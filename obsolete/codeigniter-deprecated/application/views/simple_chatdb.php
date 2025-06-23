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

        // Use PHP to inject the base URL dynamically
        const baseUrl = "<?= site_url() ?>";

        // Append message to the chat box
        function appendMessage(content) {
            const messageEl = document.createElement('div');
            messageEl.textContent = content;
            messagesDiv.appendChild(messageEl);
            messagesDiv.scrollTop = messagesDiv.scrollHeight; // Auto-scroll
        }

        // WebSocket: Receive message
        ws.onmessage = (event) => {
            const message = JSON.parse(event.data);
            if (message.action === 'new_message') {
                appendMessage(`${message.sender}: ${message.content}`);
            }
        };

        // WebSocket: Send message
        sendMessageButton.addEventListener('click', () => {
            const message = messageInput.value.trim();
            if (message) {
                ws.send(JSON.stringify({
                    action: 'send_message',
                    content: message,
                    sender_id: 1 // Replace with dynamic user ID
                }));
                messageInput.value = ''; // Clear input
            }
        });

        // Fetch and display users (via HTTP)
        async function fetchUsers() {
            try {
                const response = await fetch(`${baseUrl}/PwSimpleChatDBController/get_user/1`); // Replace with dynamic user ID
                const data = await response.json();
                if (data.success) {
                    console.log('User fetched:', data.user);
                } else {
                    console.error(data.error);
                }
            } catch (error) {
                console.error('Failed to fetch user:', error);
            }
        }

        // WebSocket: Connection open
        ws.onopen = () => {
            appendMessage('Connected to chat server.');
            fetchUsers(); // Fetch user details on connection
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
