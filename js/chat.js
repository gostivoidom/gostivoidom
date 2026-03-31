document.addEventListener('DOMContentLoaded', function () {
    // Create chat widget HTML
    var html = '<button id="chat-widget-btn" title="ИИ-консультант">&#128172;</button>'
        + '<div id="chat-widget">'
        + '  <div id="chat-widget-header">'
        + '    <span>ИИ-консультант</span>'
        + '    <button id="chat-close">&times;</button>'
        + '  </div>'
        + '  <div id="chat-messages">'
        + '    <div class="chat-msg bot">Здравствуйте! Я ИИ-консультант гостевого дома «Уют». Чем могу помочь?</div>'
        + '  </div>'
        + '  <div id="chat-input-area">'
        + '    <input type="text" id="chat-input" placeholder="Введите сообщение..." maxlength="500">'
        + '    <button id="chat-send">&#10148;</button>'
        + '  </div>'
        + '</div>';

    var container = document.createElement('div');
    container.innerHTML = html;
    document.body.appendChild(container);

    var btn = document.getElementById('chat-widget-btn');
    var widget = document.getElementById('chat-widget');
    var closeBtn = document.getElementById('chat-close');
    var input = document.getElementById('chat-input');
    var sendBtn = document.getElementById('chat-send');
    var messagesDiv = document.getElementById('chat-messages');
    var history = [];

    btn.addEventListener('click', function () {
        widget.classList.toggle('open');
        if (widget.classList.contains('open')) {
            input.focus();
        }
    });

    closeBtn.addEventListener('click', function () {
        widget.classList.remove('open');
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    sendBtn.addEventListener('click', sendMessage);

    function addMessage(text, role) {
        var div = document.createElement('div');
        div.className = 'chat-msg ' + role;
        div.textContent = text;
        messagesDiv.appendChild(div);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        return div;
    }

    function sendMessage() {
        var text = input.value.trim();
        if (!text) return;

        addMessage(text, 'user');
        history.push({ role: 'user', content: text });
        input.value = '';
        sendBtn.disabled = true;

        var typing = addMessage('Печатает...', 'bot typing');

        fetch('api/chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text, history: history })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            messagesDiv.removeChild(typing);
            var reply = data.reply || 'Не удалось получить ответ.';
            addMessage(reply, 'bot');
            history.push({ role: 'assistant', content: reply });
        })
        .catch(function () {
            messagesDiv.removeChild(typing);
            addMessage('Ошибка соединения. Попробуйте позже.', 'bot');
        })
        .finally(function () {
            sendBtn.disabled = false;
            input.focus();
        });
    }
});
