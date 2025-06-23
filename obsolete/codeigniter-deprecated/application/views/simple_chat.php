<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Dynamic Chat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        #chat {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
        }
        #messages {
            height: 300px;
            border: 1px solid #ccc;
            padding: 10px;
            overflow-y: scroll;
        }
        #messageInput {
            width: calc(100% - 70px);
        }
        #sendMessage {
            width: 60px;
        }
    </style>
</head>
<body>
    <div id="chat">
        <div id="messages"></div>
        <div>
            <input type="text" id="messageInput" placeholder="Type your message">
            <button id="sendMessage">Send</button>
        </div>
    </div>
    <script>
        const ws = new WebSocket('ws://172.27.0.3:8080/ws'); // WebSocket server
        const messagesDiv = document.getElementById('messages');
        const messageInput = document.getElementById('messageInput');
        const sendMessageButton = document.getElementById('sendMessage');

        // Append message to the chat box
        function appendMessage(content) {
            const messageEl = document.createElement('div');
            messageEl.textContent = content;
            messagesDiv.appendChild(messageEl);
            messagesDiv.scrollTop = messagesDiv.scrollHeight; // Auto-scroll
        }

        // WebSocket: Receive message
        ws.onmessage = (event) => {
            appendMessage(event.data);
        };

        // WebSocket: Send message
        sendMessageButton.addEventListener('click', () => {
            const message = messageInput.value.trim();
            if (message) {
                ws.send(message);
                messageInput.value = ''; // Clear input
            }
        });

        // WebSocket: Connection open
        ws.onopen = () => {
            appendMessage('Connected to chat server.');
        };

        // WebSocket: Connection error
        ws.onerror = () => {
            appendMessage('Error connecting to chat server.');
        };

        // WebSocket: Connection closed
        ws.onclose = () => {
            appendMessage('Disconnected from chat server.');
        };
    </script>
</body>
</html>
