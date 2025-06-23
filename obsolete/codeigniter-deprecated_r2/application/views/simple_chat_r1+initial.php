<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Test</title>
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
    <h1>WebSocket Test</h1>
    <div>
        <label for="message">Message to Send:</label>
        <input type="text" id="message" placeholder="Type a message" />
        <button onclick="sendMessage()">Send</button>
    </div>
    <h2>Log</h2>
    <div id="log"></div>

    <script>
        let log = document.getElementById('log');
        let ws;

        function logMessage(message) {
            let entry = document.createElement('div');
            entry.textContent = message;
            log.appendChild(entry);
            log.scrollTop = log.scrollHeight;
        }

        function initWebSocket() {
            // Adjust the WebSocket URL as per your setup
            ws = new WebSocket('ws://172.27.0.3:8080/ws');

            ws.onopen = () => logMessage("Connected to WebSocket server.");
            ws.onmessage = (event) => logMessage("Received: " + event.data);
            ws.onerror = (error) => logMessage("Error: " + error.message);
            ws.onclose = () => logMessage("Connection closed.");
        }

        function sendMessage() {
            const message = document.getElementById('message').value;
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(message);
                logMessage("Sent: " + message);
            } else {
                logMessage("WebSocket is not connected.");
            }
        }

        window.onload = initWebSocket;
    </script>
</body>
