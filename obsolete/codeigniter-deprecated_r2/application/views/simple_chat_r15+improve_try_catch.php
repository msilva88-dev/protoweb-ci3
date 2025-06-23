<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
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
            width: 100%;
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
            <input id="user_id" min="1" type="text" value="1">
            <div class="error" id="error_user_id"></div>

            <label>Username:</label>
            <input id="username" type="text" value="guest">
            <div class="error" id="error_username"></div>

            <label>Booking ID:</label>
            <input id="booking_id" type="text" value="ID1234567">
            <div class="error" id="error_booking_id"></div>

            <textarea
             cols="40"
             id="message_content"
             maxlength="250"
             placeholder="Add a Personal message here..."
             rows="6"
             ></textarea>
            <div class="char-counter" id="char_counter"></div>
            <button
             class="submitBtn1"
             id="send_button"
             onclick="sendMessage()"
             >Send Message</button>
        </div>
    </div>

    <script>
        let ws;

        console.log("Environment: <?= ENVIRONMENT ?>");

        const IS_DEBUG_ENV = "<?= ENVIRONMENT ?>" === "development";
        const IS_TEST_ENV = "<?= ENVIRONMENT ?>" !== "production";
        const chatCounter = " / 250";
        const defaultChatCounter = 0 + chatCounter;
        const panelBody = document.querySelector(".panel-body");

        function hasChatMessages() {
            return Array
                .from(panelBody.children)
                .some(el => el.classList.contains("chat"));
        }

        function logMessage(rawMessage) {
            let data;

            if (IS_DEBUG_ENV) console.log("Received message:", rawMessage);

            try {
                data = JSON.parse(rawMessage);
            } catch (e) {
                if (IS_TEST_ENV) {
                    console.warn("Invalid JSON message:", rawMessage);
                }

                return;
            }

            const requiredFields = [
                "date",
                "message",
                "time",
                "user_id",
                "username"
            ];
            const missing = requiredFields.filter(field => !data[field]);

            // Validate required fields
            if (missing.length > 0) {
                if (IS_TEST_ENV) {
                    console.warn(
                        "Incomplete message received. Missing:",
                        missing.join(", "),
                        "→ Data:",
                        data
                    );
                }

                return;
            }

            const avatar = document.createElement("img");
            const chatWrapper = document.createElement("div");
            const contentDiv = document.createElement("div");
            const currentUserId = parseInt(
                document.getElementById("user_id").value.trim(),
                10
            );
            const existingStatus =
                panelBody.querySelector(".ws-status, .ws-error");
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
            info.textContent =
                `${data.username} - ${data.date} - ${data.time}`;
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
            const bookingId =
                document.getElementById("booking_id").value.trim();

            document.getElementById("conversation_title").textContent =
                "Conversation: " + bookingId;
        }

        function validateInputsAndToggleButton() {
            let hasError = false;

            const errors = {
                booking_id: "Booking ID is required.",
                user_id: "User ID must be a valid number (≥ 1).",
                username: "Username is required."
            };
            const message =
                document.getElementById("message_content").value.trim();
            const sendButton = document.getElementById("send_button");

            if (!ws || ws.readyState !== WebSocket.OPEN) hasError = true;
            if (message === "") hasError = true;

            Object.entries(errors).forEach(([id, message]) => {
                const input = document.getElementById(id);
                const errorDiv = document.getElementById(`error_${id}`);

                errorDiv.textContent = "";

                if (
                    (
                        id === "user_id" &&
                        (
                            isNaN(parseInt(input.value, 10)) ||
                            parseInt(input.value, 10) < 1
                        )
                    ) ||
                    (id !== "user_id" && input.value.trim() === "")
                ) {
                    errorDiv.textContent = message;
                    hasError = true;
                }
            });

            sendButton.disabled = hasError;
        }

        function initWebSocket() {
            ws = new WebSocket("ws://172.27.0.3:8080/ws");

            ws.onopen = () => {
                validateInputsAndToggleButton();
                showStatusMessage("Connected to WebSocket server.");
            }
            ws.onmessage = event => {
                try {
                    logMessage(event.data);
                } catch (e) {
                    if (IS_TEST_ENV) {
                        console.error("Unhandled error in logMessage:", e);
                    }
                }
            };
            ws.onerror = error => {
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

            const bookingId =
                document.getElementById("booking_id").value.trim();
            const userId = parseInt(
                document.getElementById("user_id").value.trim(),
                10
            );
            const username = document.getElementById("username").value.trim();
            const message =
                document.getElementById("message_content").value.trim();
            const sendButton = document.getElementById("send_button");
            const payload = JSON.stringify({
                booking_id: bookingId,
                user_id: userId,
                username: username,
                message: message
            });

            try {
                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(payload);

                    if (IS_DEBUG_ENV) {
                        console.log("Sending message:", payload);
                    }
                } else {
                    if (IS_TEST_ENV) {
                        console.warn(
                            "WebSocket not open. Message not sent:",
                            payload
                        );
                    }
                }
            } catch (e) {
                if (IS_TEST_ENV) {
                    console.error("WebSocket send failed:", e, payload);
                }
            }

            // Clear input and reset the chat counter
            document.getElementById("message_content").value = "";
            document.getElementById("char_counter").textContent =
                defaultChatCounter;

            // Revalidate after clearing the message
            validateInputsAndToggleButton();
        }

        const observer = new MutationObserver(() => {
            const hasMessages = hasChatMessages();
            const hasStatus =
                panelBody.querySelector(".ws-status, .ws-error");

            if (!hasMessages && !hasStatus) {
                if (ws && ws.readyState === WebSocket.OPEN) {
                    showStatusMessage("Connected to WebSocket server.");
                } else {
                    showStatusMessage("WebSocket is not connected.", true);
                }
            }
        });

        observer.observe(panelBody, {childList: true, subtree: false});

        document.getElementById("char_counter").textContent =
            defaultChatCounter;

        // Restore user ID and username from localStorage on load
        window.onload = () => {
            initWebSocket();

            const storedBooking = localStorage.getItem("chat_booking_id");
            const storedId = localStorage.getItem("chat_user_id");
            const storedName = localStorage.getItem("chat_username");

            if (storedBooking) {
                document.getElementById("booking_id").value = storedBooking;
            }
            if (storedId) document.getElementById("user_id").value = storedId;
            if (storedName) {
                document.getElementById("username").value = storedName;
            }

            // List of input fields to watch
            ["booking_id", "user_id", "username"].forEach(id => {
                document.getElementById(id).addEventListener("input", () => {
                    localStorage.setItem(
                        "chat_" + id,
                        document.getElementById(id).value.trim()
                    );

                    if (id === "booking_id") updateConversationTitle();

                    validateInputsAndToggleButton();
                });
            });

            // Also validate when the user types a message
            document.getElementById("message_content").addEventListener(
                "input",
                () => {
                    const msg =
                        document.getElementById("message_content").value;
                    const counter = document.getElementById("char_counter");

                    counter.textContent = msg.length + chatCounter;

                    validateInputsAndToggleButton();
                }
            );

            updateConversationTitle(); // Call at start
            validateInputsAndToggleButton();
        };
    </script>
</body>
</html>
