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
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .char-counter {
            color: #666;
            font-size: 0.9em;
            margin: 4px 0 10px;
        }
        .chat p::after {
            content: "💬";
            font-size: 0.9em;
            margin-left: 4px;
        }
        .chat small::after {
            content: " 🕒";
            font-size: 0.85em;
            margin-left: 6px;
        }
        .clear::after {
        }
        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 8px;
        }
        .left, .right {
            display: grid;
            gap: 10px;
            margin: 10px 0;
        }
        .left {
            grid-template-areas: "avatar msg" "avatar meta";
            grid-template-columns: auto 1fr;
            justify-content: start;
        }
        .right {
            grid-template-areas: "msg avatar" "meta avatar";
            grid-template-columns: 1fr auto;
            justify-content: end;
        }
        .left img, .right img {
            background-color: #ccc;
            border-radius: 50%;
            grid-area: avatar;
            height: 40px;
            width: 40px;
        }
        .left img {
            margin-right: 8px;
        }
        .right img {
            margin-left: 8px;
        }
        .left p, .right p {
            border-radius: 12px;
            font-size: 14px;
            grid-area: msg;
            margin: 0;
            padding: 8px 12px;
            width: fit-content;
        }
        .left p {
            background-color: #f1f1f1;
        }
        .right p {
            background-color: #dbeafe;
            justify-self: end;
        }
        .left small, .right small {
            color: #666;
            grid-area: meta;
            grid-column: 1 / span 2;
            margin-top: 4px;
        }
        .right small {
            justify-self: end;
        }
        .panel {
            border: 1px solid #ccc;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .panel-body {
            background-color: #fafafa;
            font-family: monospace;
            height: 200px;
            overflow-y: auto;
            padding: 10px;
        }
        .panel-heading {
            background-color: #f5f5f5;
            border-bottom: 1px solid #ddd;
            font-size: 1.1em;
            font-weight: bold;
            padding: 10px;
        }
        .panel-tailing {
            background-color: #f9f9f9;
            border-top: 1px solid #ddd;
            display: grid;
            padding: 10px;
        }
        .panel-tailing input[type="text"] {
            box-sizing: border-box;
            padding: 6px;
            min-width: 10px;
        }
        .submitBtn1 {
            background-color: #08f;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            padding: 8px 16px;
            transition: background-color 0.2s ease;
            width: fit-content;
        }
        .submitBtn1:hover:not(:disabled) {
            background-color: #05b;
        }
        .submitBtn1:disabled {
            background-color: #999;
            cursor: not-allowed;
            opacity: 0.7;
        }
        .ws-error, .ws-status {
            font-style: italic;
            padding: 6px;
            text-align: center;
        }
        .ws-error {
            color: red;
        }
        .ws-status {
            color: green;
        }
        @media (max-width: 194px) {
            .left, .right {
                grid-template-areas: "avatar" "msg" "meta"
            }
        }
    </style>
</head>
<body>
    <h1>WebSocket Chat</h1>

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

        const chatCounter = " / 250";
        const defaultChatCounter = 0 + chatCounter;
        const panelBody = document.querySelector(".panel-body");

        function hasChatMessages() {
            return Array.from(panelBody.children).some(el => el.classList.contains("chat"));
        }

        function logMessage(rawMessage) {
            let data;

            try {
                data = JSON.parse(rawMessage);
            } catch (e) {
                console.warn("Invalid JSON message:", rawMessage);

                return;
            }

            // Validate required fields
            if (!data.message || !data.username || !data.date || !data.time || typeof data.user_id === "undefined") {
                console.warn("Incomplete message received:", data);

                return;
            }

            const avatar = document.createElement("img");
            const chatWrapper = document.createElement("div");
            const contentDiv = document.createElement("div");
            const currentUserId = parseInt(document.getElementById("user_id").value.trim(), 10);
            const existingStatus = panelBody.querySelector(".ws-status, .ws-error");
            const info = document.createElement("small");
            const isMine = data.user_id === currentUserId;
            const messageParagraph = document.createElement("p");

            if (existingStatus) existingStatus.remove();

            chatWrapper.className = "chat";
            contentDiv.className = isMine ? "left" : "right";
            avatar.alt = "avatar";
            avatar.loading = "lazy";
            avatar.src = "";
            messageParagraph.textContent = data.message; // this is bubble
            info.textContent = `${data.username} - ${data.date} - ${data.time}`;
            panelBody.scrollTop = panelBody.scrollHeight;

            contentDiv.appendChild(avatar);
            contentDiv.appendChild(messageParagraph);
            contentDiv.appendChild(info);
            chatWrapper.appendChild(contentDiv);
            panelBody.appendChild(chatWrapper);
        }

        function showStatusMessage(text, isError = false) {
            const existing = panelBody.querySelector(".ws-status, .ws-error");

            // This avoid duplicated messages
            if (existing) existing.remove();

            // Check if not have previous messages
            if (!hasChatMessages()) {
                const status = document.createElement("div");

                status.className = isError ? "ws-error" : "ws-status";
                status.textContent = text;

                panelBody.appendChild(status);
            }

            if (!isError) {
                console.log(text);
            } else {
                console.error(text);
            }
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

            if (!ws || ws.readyState !== WebSocket.OPEN) hasError = true;

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

            if (message === "") hasError = true;

            sendButton.disabled = hasError;
        }

        function initWebSocket() {
            ws = new WebSocket('ws://172.27.0.3:8080/ws');

            ws.onopen = () => {
                validateInputsAndToggleButton();
                showStatusMessage("Connected to WebSocket server.");
            }
            ws.onmessage = (event) => logMessage(event.data);
            ws.onerror = (error) => {
                validateInputsAndToggleButton();
                console.error("WebSocket error:", error);
            }
            ws.onclose = () => {
                validateInputsAndToggleButton();
                showStatusMessage("WebSocket is not connected.", true);
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

        const observer = new MutationObserver(() => {
            const hasMessages = hasChatMessages();
            const hasStatus = panelBody.querySelector(".ws-status, .ws-error");

            if (!hasMessages && !hasStatus) {
                if (ws && ws.readyState === WebSocket.OPEN) {
                    showStatusMessage("Connected to WebSocket server.");
                } else {
                    showStatusMessage("WebSocket is not connected.", true);
                }
            }
        });

        observer.observe(panelBody, {childList: true, subtree: false});

        document.getElementById("char_counter").textContent = defaultChatCounter;

        // Restore user ID and username from localStorage on load
        window.onload = () => {
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

                counter.textContent = msg.length + chatCounter;

                validateInputsAndToggleButton();
            });

            updateConversationTitle(); // Call at start
            validateInputsAndToggleButton();
        };
    </script>
</body>
