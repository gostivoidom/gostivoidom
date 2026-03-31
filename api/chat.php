<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    http_response_code(405);
    exit;
}

include __DIR__ . '/../db/connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$history = $input['history'] ?? [];

if ($message === '' || mb_strlen($message) > 500) {
    echo json_encode(['error' => 'Invalid message']);
    http_response_code(400);
    exit;
}

// --- Загрузить каталог номеров для контекста ---
$catalogContext = '';
try {
    $result = $conn->query("SELECT id, name, price, capacity, description FROM rooms ORDER BY name");
    $lines = [];
    if ($result) {
        while ($r = $result->fetch_assoc()) {
            $desc = $r['description'] ? ' ' . mb_substr($r['description'], 0, 150) : '';
            $lines[] = "- {$r['name']}: {$r['price']} руб./ночь, вместимость {$r['capacity']} чел.{$desc}";
        }
    }
    $catalogContext = implode("\n", $lines);
} catch (Exception $e) {
    $catalogContext = '(каталог временно недоступен)';
}

// --- Системный промпт ---
$systemPrompt = "Ты — ИИ-консультант гостевого дома «Уют». "
    . "Отвечай на русском языке. Будь вежлив, краток и полезен. "
    . "Помогай гостям выбрать номер, отвечай на вопросы о ценах, вместимости и удобствах. "
    . "Если гость спрашивает о чём-то, чего нет в каталоге, вежливо сообщи об этом. "
    . "Не выдумывай номера, которых нет в каталоге. "
    . "Для бронирования направляй гостей на страницу «Номера» на сайте. "
    . "Телефон: +7 (999) 123-45-67, email: info@gostivoldom.ru, адрес: г. Курск, ул. Примерная, д. 1.\n\n"
    . "Каталог номеров:\n" . $catalogContext;

// --- Формируем сообщения для HF API ---
$messages = [];
$messages[] = ['role' => 'system', 'content' => $systemPrompt];

$history = array_slice($history, -10);
foreach ($history as $msg) {
    $role = ($msg['role'] === 'user') ? 'user' : 'assistant';
    $messages[] = ['role' => $role, 'content' => $msg['content']];
}

$messages[] = ['role' => 'user', 'content' => $message];

// --- Запрос к Hugging Face Inference API ---
$hfToken = getenv('HF_API_TOKEN');
if (!$hfToken) {
    echo json_encode(['error' => 'HF_API_TOKEN not configured', 'reply' => 'ИИ-консультант временно недоступен. Пожалуйста, свяжитесь с нами по телефону.']);
    http_response_code(500);
    exit;
}

$model = 'meta-llama/Llama-3.1-8B-Instruct';
$url = 'https://router.huggingface.co/v1/chat/completions';

$payload = json_encode([
    'model' => $model,
    'messages' => $messages,
    'max_tokens' => 512,
    'temperature' => 0.7,
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $hfToken,
    ],
    CURLOPT_TIMEOUT => 60,
    CURLOPT_CONNECTTIMEOUT => 10,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['reply' => 'Ошибка соединения с ИИ. Попробуйте позже.']);
    http_response_code(502);
    exit;
}

$data = json_decode($response, true);

if ($httpCode !== 200 || !isset($data['choices'][0]['message']['content'])) {
    echo json_encode(['reply' => 'ИИ-консультант временно недоступен. Попробуйте позже.']);
    http_response_code(502);
    exit;
}

$reply = trim($data['choices'][0]['message']['content']);
echo json_encode(['reply' => $reply]);

$conn->close();
