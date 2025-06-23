<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Chat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        #log {
            border: 1px solid #ccc;
            padding: 10px;
            height: 150px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <h1>WebSocket Chat</h1>

    <div>
        <label>User ID:</label>
        <input type="text" id="user_id" value="1">
        <label>Username:</label>
        <input type="text" id="username" value="guest">
    </div>

    <div>
        <label for="message">Message:</label>
        <input type="text" id="message" placeholder="Type a message" />
        <button onclick="sendMessage()">Send</button>
    </div>

    <h2>Log</h2>
    <div id="log"></div>

    <script>
        let ws;
        const log = document.getElementById('log');

        function logMessage(message) {
            const entry = document.createElement('div');
            entry.textContent = message;
            log.appendChild(entry);
            log.scrollTop = log.scrollHeight;
        }

        function initWebSocket() {
            ws = new WebSocket('ws://172.27.0.3:8080/ws');

            ws.onopen = () => logMessage("Connected to WebSocket server.");
            ws.onmessage = (event) => logMessage(event.data);
            ws.onerror = (error) => logMessage("Error: " + error.message);
            ws.onclose = () => logMessage("Connection closed.");
        }

        function sendMessage() {
            const message = document.getElementById('message').value.trim();
            const userId = document.getElementById('user_id').value.trim();
            const username = document.getElementById('username').value.trim();
            if (
                ws &&
                ws.readyState === WebSocket.OPEN &&
                message !== ""
            ) {
                const payload = JSON.stringify({
                    user_id: userId,
                    username: username,
                    message: message
                });
                ws.send(payload);
                document.getElementById('message').value = ""; // Clear input
            } else if (message === "") {
                logMessage("Message is empty.");
            } else {
                logMessage("WebSocket is not connected.");
            }
        }

        window.onload = initWebSocket;
    </script>
</body>
