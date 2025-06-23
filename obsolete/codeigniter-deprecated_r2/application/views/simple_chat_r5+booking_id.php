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
        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 8px;
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
        <div id="error_user_id" class="error"></div>

        <label>Username:</label>
        <input type="text" id="username" value="guest">
        <div id="error_username" class="error"></div>

        <label>Booking ID:</label>
        <input type="text" id="booking_id" value="BID1234">
        <div id="error_booking_id" class="error"></div>
    </div>

    <div>
        <label for="message">Message:</label>
        <input type="text" id="message" placeholder="Type a message" />
        <button onclick="sendMessage()">Send</button>
        <div id="error_message" class="error"></div>
    </div>

    <!-- Error general (por ejemplo WebSocket) -->
    <div id="error_general" class="error" style="margin-top: 10px;"></div>

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
            // Clear previous errors
            document.getElementById("error_booking_id").textContent = "";
            document.getElementById("error_user_id").textContent = "";
            document.getElementById("error_username").textContent = "";
            document.getElementById("error_message").textContent = "";
            document.getElementById("error_general").textContent = "";

            const bookingIdStr = document.getElementById('booking_id').value.trim();
            const userIdStr = document.getElementById('user_id').value.trim();
            const userIdInt = parseInt(userIdStr, 10);
            const usernameStr = document.getElementById('username').value.trim();
            const messageStr = document.getElementById('message').value.trim();

            let hasError = false;

            if (!ws || ws.readyState !== WebSocket.OPEN) {
                document.getElementById("error_general").textContent = "WebSocket is not connected.";
                hasError = true;
            }

            if (bookingIdStr === "") {
                document.getElementById("error_booking_id").textContent = "Booking ID is required.";
                hasError = true;
            }

            if (isNaN(userIdInt) || userIdInt < 1) {
                document.getElementById("error_user_id").textContent = "User ID must be a valid number (≥ 1).";
                hasError = true;
            }

            if (usernameStr === "") {
                document.getElementById("error_username").textContent = "Username is required.";
                hasError = true;
            }

            if (messageStr === "") {
                document.getElementById("error_message").textContent = "Message cannot be empty.";
                hasError = true;
            }

            if (!hasError) {
                const payload = JSON.stringify({
                    booking_id: bookingIdStr,
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

            const storedBooking = localStorage.getItem("chat_booking_id");
            const storedId = localStorage.getItem("chat_user_id");
            const storedName = localStorage.getItem("chat_username");

            if (storedBooking) document.getElementById("booking_id").value = storedBooking;
            if (storedId) document.getElementById("user_id").value = storedId;
            if (storedName) document.getElementById("username").value = storedName;

            // Save values on change
            document.getElementById("booking_id").addEventListener("input", () => {
                localStorage.setItem("chat_booking_id", document.getElementById("booking_id").value.trim());
            });

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
