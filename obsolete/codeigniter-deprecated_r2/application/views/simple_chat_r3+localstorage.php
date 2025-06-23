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
        <input type="text" id="user_id" value="1" min="1">
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
            const messageStr = document.getElementById('message').value.trim();
            const userIdStr = document.getElementById('user_id').value.trim();
            const usernameStr = document.getElementById('username').value.trim();
            const userIdInt = parseInt(userIdStr, 10);

            if (!ws || ws.readyState !== WebSocket.OPEN) {
                logMessage("WebSocket is not connected.");
            } else if (isNaN(userIdInt) || userIdInt < 1) {
                logMessage("User ID must be a valid number (≥ 1).");
            } else if (usernameStr === "") {
                logMessage("Username is empty.");
            } else if (messageStr === "") {
                logMessage("Message is empty.");
            } else {
                const payload = JSON.stringify({
                    user_id: userIdInt,
                    username: usernameStr,
                    message: messageStr
                });

                ws.send(payload);
                document.getElementById('message').value = ""; // Clear input
            }
        }

        // Restore user ID and username from localStorage on load
        window.onload = function () {
            initWebSocket();

            const storedId = localStorage.getItem("chat_user_id");
            const storedName = localStorage.getItem("chat_username");

            if (storedId) document.getElementById("user_id").value = storedId;
            if (storedName) document.getElementById("username").value = storedName;

            // Save values on change
            document.getElementById("user_id").addEventListener("input", () => {
                localStorage.setItem("chat_user_id", document.getElementById("user_id").value.trim());
            });

            document.getElementById("username").addEventListener("input", () => {
                localStorage.setItem("chat_username", document.getElementById("username").value.trim());
            });
        };
        //window.onload = initWebSocket;
    </script>
</body>
