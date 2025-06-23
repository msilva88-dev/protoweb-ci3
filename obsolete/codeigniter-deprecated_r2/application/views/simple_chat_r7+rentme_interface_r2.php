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
        .char-counter {
            font-size: 0.9em;
            color: #666;
            margin-top: 4px;
            margin-bottom: 10px;
        }
        .chat {
            margin-bottom: 12px;
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }
        .chat img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ccc;
        }
        .chat p::after {
            content: " 💬";
            color: #888;
            margin-left: 4px;
            font-size: 0.9em;
        }
        .chat small::after {
            content: " 🕒";
            color: #bbb;
            margin-left: 6px;
            font-size: 0.85em;
        }
        .chat-content {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }
        .chat-content p {
            margin: 0;
            flex-grow: 1;
            font-size: 14px;
        }
        .chat-content small {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: #777;
        }
        .clear::after {
            content: "";
            display: block;
            height: 1px;
            margin: 12px 0;
            background-color: #ddd;
        }
        .clear input[type="text"] {
            width: 70%;
            padding: 6px;
            margin-right: 10px;
        }
        .clear button {
            padding: 6px 12px;
        }
        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 8px;
        }
        .msgConversation {
            font-family: monospace;
            white-space: pre-wrap;
        }
        .panel {
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .panel-body {
            padding: 10px;
            height: 200px;
            overflow-y: auto;
            background-color: #fafafa;
        }
        .panel-heading {
            background-color: #f5f5f5;
            border-bottom: 1px solid #ddd;
            padding: 10px;
            font-weight: bold;
            font-size: 1.1em;
        }
        .panel-tailing {
            padding: 10px;
            border-top: 1px solid #ccc;
            background-color: #f9f9f9;
        }
        .submitBtn1 {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .submitBtn1:hover:not(:disabled) {
            background-color: #0056b3;
        }
        .submitBtn1:disabled {
            background-color: #999;
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <h1>WebSocket Chat</h1>

    <!-- General error (as example: WebSocket) -->
    <div id="error_general" class="error" style="margin-top: 10px;"></div>

    <div class="panel panel-default">
        <div class="panel-heading" id="conversation_title"></div>
        <div class="panel-body msgConversation"></div>
        <div class="panel-tailing clear">
            <label>User ID:</label>
            <input type="text" id="user_id" value="1" min="1">
            <div id="error_user_id" class="error"></div>

            <label>Username:</label>
            <input type="text" id="username" value="guest">
            <div id="error_username" class="error"></div>

            <label>Booking ID:</label>
            <input type="text" id="booking_id" value="ID1234567">
            <div id="error_booking_id" class="error"></div>

            <textarea id="message_content" placeholder="Add a Personal message here..." maxlength="250" cols="40" rows="6"></textarea>
            <div id="char_counter" class="char-counter"></div>
            <button id="send_button" class="submitBtn1" onclick="sendMessage()">Send Message</button>
        </div>
    </div>

    <script>
        let ws;

        const ChatCounter = " / 250";
        const defaultChatCounter = 0 + ChatCounter;

        function logMessage(rawMessage) {
            const logContainer = document.querySelector(".msgConversation.panel-body");
            const chatWrapper = document.createElement("div");
            const contentDiv = document.createElement("div");
            const avatar = document.createElement("img");
            const messageParagraph = document.createElement("p");
            const info = document.createElement("small");

            chatWrapper.className = "chat";
            contentDiv.className = "chat-content";
            avatar.alt = "avatar";
            avatar.loading = "lazy";
            avatar.src = "";
            messageParagraph.textContent = rawMessage;
            info.textContent = "guest - 2025-04-10 - 14:00";
            logContainer.scrollTop = logContainer.scrollHeight;

            contentDiv.appendChild(avatar);
            contentDiv.appendChild(messageParagraph);
            contentDiv.appendChild(info);
            chatWrapper.appendChild(contentDiv);
            logContainer.appendChild(chatWrapper);
        }

        function initWebSocket() {
            ws = new WebSocket('ws://172.27.0.3:8080/ws');

            ws.onopen = () => {
                logMessage("Connected to WebSocket server.");
                validateInputsAndToggleButton();
            }
            ws.onmessage = (event) => logMessage(event.data);
            ws.onerror = (error) => {
                logMessage("Error: " + error.message);
                validateInputsAndToggleButton();
            }
            ws.onclose = () => {
                logMessage("Connection closed.");
                validateInputsAndToggleButton();
            }
        }

        function sendMessage() {
            if (document.getElementById("send_button").disabled) return;

            const bookingId = document.getElementById('booking_id').value.trim();
            const userId = parseInt(document.getElementById('user_id').value.trim(), 10);
            const username = document.getElementById('username').value.trim();
            const message = document.getElementById('message_content').value.trim();
            const sendButton = document.getElementById('send_button');
            const payload = JSON.stringify({
                booking_id: bookingId,
                user_id: userId,
                username: username,
                message: message
            });

            ws.send(payload);
            document.getElementById('message_content').value = ""; // Clear input
            document.getElementById("char_counter").textContent = defaultChatCounter;

            validateInputsAndToggleButton(); // Revalidate after clearing the message
        }

        function updateConversationTitle() {
            const bookingId = document.getElementById("booking_id").value.trim();

            document.getElementById("conversation_title").textContent = "Conversation: " + bookingId;
        }

        function validateInputsAndToggleButton() {
            let hasError = false;

            const bookingId = document.getElementById('booking_id').value.trim();
            const userId = parseInt(document.getElementById('user_id').value.trim(), 10);
            const username = document.getElementById('username').value.trim();
            const message = document.getElementById('message_content').value.trim();
            const sendButton = document.getElementById('send_button');

            // Clear previous errors
            document.getElementById("error_booking_id").textContent = "";
            document.getElementById("error_user_id").textContent = "";
            document.getElementById("error_username").textContent = "";
            document.getElementById("error_general").textContent = "";


            if (!ws || ws.readyState !== WebSocket.OPEN) {
                document.getElementById("error_general").textContent = "WebSocket is not connected.";

                hasError = true;
            }

            if (bookingId === "") {
                document.getElementById("error_booking_id").textContent = "Booking ID is required.";

                hasError = true;
            }

            if (isNaN(userId) || userId < 1) {
                document.getElementById("error_user_id").textContent = "User ID must be a valid number (≥ 1).";

                hasError = true;
            }

            if (username === "") {
                document.getElementById("error_username").textContent = "Username is required.";

                hasError = true;
            }

            if (message === "") {
                hasError = true;
            }

            sendButton.disabled = hasError;
        }

        document.getElementById("char_counter").textContent = defaultChatCounter;

        // Restore user ID and username from localStorage on load
        window.onload = function () {
            initWebSocket();

            const storedBooking = localStorage.getItem("chat_booking_id");
            const storedId = localStorage.getItem("chat_user_id");
            const storedName = localStorage.getItem("chat_username");

            if (storedBooking) document.getElementById("booking_id").value = storedBooking;
            if (storedId) document.getElementById("user_id").value = storedId;
            if (storedName) document.getElementById("username").value = storedName;

            // List of input fields to watch
            ["booking_id", "user_id", "username"].forEach((id) => {
                document.getElementById(id).addEventListener("input", () => {
                    localStorage.setItem("chat_" + id, document.getElementById(id).value.trim());

                    if (id === "booking_id") updateConversationTitle();

                    validateInputsAndToggleButton();
                });
            });

            // Also validate when the user types a message
            document.getElementById("message_content").addEventListener("input", () => {
                const msg = document.getElementById("message_content").value;
                const counter = document.getElementById("char_counter");

                counter.textContent = msg.length + ChatCounter;

                validateInputsAndToggleButton();
            });

            updateConversationTitle(); // Call at start
            validateInputsAndToggleButton();
        };
    </script>
</body>
