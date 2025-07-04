<!--
Copyright (c) 2024–2025, Marcio Delgado <marcio@libreware.info>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice,
   this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright notice,
   this list of conditions and the following disclaimer in the documentation
   and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS “AS IS”
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.

This file follows W3C HTML5 and CSS3 specifications and
JavaScript best practices.
Code style is inspired by the Airbnb JavaScript Style Guide,
W3C HTML/CSS Guidelines, and adapted to PSR-style documentation
conventions for readability.
-->

<!--
  WebSocket Chat UI
  Copyright (c) 2024-2025, Marcio Delgado
  License: BSD-2-Clause

  This HTML, CSS, and JS source follows the Google HTML/CSS Style Guide,
  Airbnb JavaScript Style Guide, and general industry best practices.
-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>WebSocket Chat</title>
    <style>
        /*
         * CSS styles for WebSocket Chat UI.
         * Designed for responsive layout and semantic structure.
         * Uses grid-based message layout with visual roles.
         */

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

        .clear::after {}

        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 8px;
        }

        .input-error {
            border: 2px solid red;
            outline: none;
        }

        .left,
        .right {
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

        .left img,
        .right img {
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

        .left p,
        .right p {
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

        .left small,
        .right small {
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

        .ws-error,
        .ws-status {
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
            .left,
            .right {
                grid-template-areas: "avatar" "msg" "meta";
            }
        }
    </style>
</head>
<body>
    <noscript>
        <style>
            body > *:not(noscript) {
                display: none !important;
            }

            noscript div {
                color: red;
                font-family: sans-serif;
                margin-top: 20%;
                text-align: center;
            }
        </style>
        <div>
            <h1>This website requires JavaScript to function properly.</h1>
            <p>Please enable JavaScript in your browser settings<br
              >or use a browser that supports JavaScript.</p>
        </div>
    </noscript>

    <h1>WebSocket Chat</h1>

    <div class="panel panel-default">
        <div class="panel-heading" id="conversation_title"></div>
        <div class="panel-body msgConversation"></div>
        <div class="panel-tailing clear">
            <label>Your ID:</label>
            <input
             id="sender_id"
             min="1"
             placeholder="Example: 1"
             type="text"
             >
            <div class="error" id="error_sender_id"></div>

            <label>Your name:</label>
            <input id="sender_name" placeholder="Example: John" type="text">
            <div class="error" id="error_sender_name"></div>

            <label>Recipient's ID (optional):</label>
            <input
             id="receiver_id"
             min="1"
             placeholder="Example: 2"
             type="text"
             >
            <div class="error" id="error_receiver_id"></div>

            <label>Recipient's name (optional):</label>
            <input
             id="receiver_name"
             placeholder="Example: Alice"
             type="text"
             >
            <div class="error" id="error_receiver_name"></div>

            <label>Booking reference:</label>
            <input
             id="booking_no"
             placeholder="Example: ID1234567"
             type="text"
             >
            <div class="error" id="error_booking_no"></div>

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

    <script type="text/javascript">
        (function () {
            var browserBlockMessage =
                "<h1 style="
                + "'color: red;"
                + " text-align: center;"
                + " margin-top: 20%;"
                + " font-family: sans-serif;'"
                + ">"
                + "This website is not compatible with your browser.<br>"
                + "Please update to a modern version"
                + " of Chrome, Firefox, Safari, or Edge."
                + "</h1>";

            /*
             *
             * Compatibility check for JavaScript ES1 and ES2.
             * Blocks execution in very old engines
             * lacking basic support.
             *
             */

            var canUseDocumentWrite =
                typeof document !== "undefined"
                && typeof document.write === "function";

            var canUseAlert =
                typeof window !== "undefined"
                && typeof window.alert === "function";

            /*
             * Extreme compatibility lock:
             * both alert() and document.write() are unavailable.
             */
            if (!canUseDocumentWrite || !canUseAlert) {
                if (canUseDocumentWrite) {
                    document.write(browserBlockMessage);
                }

                return; // Exit the script
            }

            /*
             * Compatibility check for JavaScript ES3 and later.
             * Blocks execution in outdated engines
             * missing modern features.
             */

            var unsupported = false;

            try {
                if (
                    /*
                     * ES2020 (ES11, 2020)
                     *
                     * Requires BigInt support
                     * to handle large integer values,
                     * such as SQL BIGINT types,
                     * without loss of precision.
                     */
                    typeof BigInt === "undefined"
                    // ES2015 (ES6, 2015)
                    || typeof Promise === "undefined"
                    // DOM (~2012)
                    || typeof MutationObserver === "undefined"
                    // ES2009 (ES5, 2009)
                    || typeof JSON === "undefined"
                    // HTML5 (2009)
                    || typeof localStorage === "undefined"
                    // HTML5 (~2009)
                    || typeof WebSocket === "undefined"
                    // ES1997 (ES1, 1997)
                    || typeof Array === "undefined"
                    // ES1997 (ES1, 1997)
                    || typeof Object === "undefined"
                ) {
                    unsupported = true;
                }

                if (!unsupported) {
                    if (
                        // ES2019 (ES10, 2019)
                        typeof Object.fromEntries !== "function"
                        // ES2015 (ES6, 2015)
                        || typeof Array.from !== "function"
                        // ES2009 (ES5, 2009)
                        || typeof JSON.parse !== "function"
                    ) {
                        unsupported = true;
                    }
                }
            } catch (e) {
                unsupported = true;
            }

            if (unsupported) {
                alert(
                    "This website requires support"
                    + " for modern JavaScript features.\n"
                    + "Please update your browser"
                    + " or enable full JavaScript support."
                );

                if ( // DOM level 0/1 (~1996)
                    typeof document !== "undefined"
                    && typeof document.body !== "undefined"
                    && typeof document.body.innerHTML !== "undefined"
                ) {
                    document.body.innerHTML = browserBlockMessage;
                } else if (canUseDocumentWrite) {
                    document.write(browserBlockMessage);
                }

                throw new Error(
                    "Unsupported browser: missing ES2020+ features."
                );
            }
        })();
    </script>

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

        function parseWithBigInt(rawMessage) {
            // 2^53 - 1 = 9007199254740991n
            const MAX_SAFE_INTEGER = BigInt(Number.MAX_SAFE_INTEGER);

            return JSON.parse(rawMessage, (key, value) => {
                // Only parse keys that ends on '_id' or are 'id'
                const isIdKey = key === "id" || key.endsWith("_id");

                if (!isIdKey) return value;

                // If the value is a Number and safer
                if (typeof value === "number") {
                    if (
                        Number.isInteger(value)
                        && value >= 0
                        && value <= Number.MAX_SAFE_INTEGER
                    ) {
                        return BigInt(value);
                    } else {
                        console.warn(
                            "Number too large for safe conversion to BigInt:",
                            key,
                            value
                        );

                        return null;
                    }
                }

                // If the value is a String
                if (typeof value === "string" && /^\d+$/.test(value)) {
                    try {
                        return BigInt(value);
                    } catch (e) {
                        console.warn("Invalid BigInt string for", key, value);

                        return null;
                    }
                }

                return null;
            });
        }

        function logMessage(rawMessage) {
            let data;

            if (IS_DEBUG_ENV) console.log("Received message:", rawMessage);

            try {
                data = parseWithBigInt(rawMessage);
            } catch (e) {
                if (IS_TEST_ENV) {
                    console.warn("Invalid JSON message:", rawMessage);
                }

                return;
            }

            const requiredFields = [
                "created_at",
                "message",
                "sender_id",
                "sender_name"
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
            const currentSenderId = BigInt(
                document.getElementById("sender_id").value.trim()
            );
            const existingStatus =
                panelBody.querySelector(".ws-status, .ws-error");
            const info = document.createElement("small");
            const isMine = data["sender_id"] === currentSenderId;
            const messageParagraph = document.createElement("p");
            const [date, time] = data["created_at"].split(' ');

            if (existingStatus) existingStatus.remove();

            chatWrapper.className = "chat";
            contentDiv.className = isMine ? "left" : "right";
            avatar.alt = "avatar";
            avatar.loading = "lazy";
            avatar.src = "";
            messageParagraph.textContent = data["message"]; // this is bubble
            if (
                !data["receiver_name"]
                || data["receiver_name"].trim() === ""
                || data["receiver_name"].trim() === data["sender_name"].trim()
            ) {
                info.textContent =
                    `${data["sender_name"]} - ${date} - ${time}`;
            } else {
                info.textContent =
                    `${data["sender_name"]} → ${data["receiver_name"]}`
                    + ` - ${date} - ${time}`;
            }
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
            const bookingNo =
                document.getElementById("booking_no").value.trim();

            document.getElementById("conversation_title").textContent =
                "Conversation: " + bookingNo;
        }

        function validateInputsAndToggleButton() {
            let firstErrorElement = null;
            let hasError = false;

            const errors = {
                booking_no: "Booking No. is required.",
                receiver_id: "Receiver ID must be a valid number (≥ 1).",
                sender_id: "Sender ID must be a valid number (≥ 1).",
                sender_name: "Sender name is required."
            };
            const messageContent =
                document.getElementById("message_content").value.trim();
            const sendButton = document.getElementById("send_button");

            if (!ws || ws.readyState !== WebSocket.OPEN) hasError = true;
            if (messageContent === "") hasError = true;

            Object.entries(errors).forEach(([attrId, messageContent]) => {
                const errorDiv = document.getElementById(`error_${attrId}`);
                const input = document.getElementById(attrId);
                const value = input.value.trim();
                const isNumeric =
                    !isNaN(parseInt(value, 10)) && parseInt(value, 10) >= 1;
                const isOptionalNumber = (
                    attrId === "receiver_id" && value !== "" && !isNumeric
                );
                const isRequiredNumber = (
                    attrId === "sender_id" && !isNumeric
                );
                const isRequiredText = (
                    attrId === "booking_no"
                    || attrId === "sender_name"
                ) && value === "";

                // Clear previous error state
                errorDiv.textContent = "";
                input.classList.remove("input-error");

                if (isOptionalNumber || isRequiredNumber || isRequiredText) {
                    errorDiv.textContent = messageContent;
                    hasError = true;

                    input.classList.add("input-error");

                    // Save first error element to focus later
                    if (!firstErrorElement) {
                        firstErrorElement = input;
                    }
                }
            });

            sendButton.disabled = hasError;

            // Scroll and focus to the first error field
            if (firstErrorElement) {
                firstErrorElement.scrollIntoView(
                    { behavior: "smooth", block: "center" }
                );
                firstErrorElement.focus();
            }
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

            const bookingNo =
                document.getElementById("booking_no").value.trim();
            const messageContent =
                document.getElementById("message_content").value.trim();
            const strReceiverId =
                document.getElementById("receiver_id").value.trim();
            const receiverId =
                strReceiverId === "" ? 0n : BigInt(strReceiverId);
            const receiverName =
                document.getElementById("receiver_name").value.trim();
            const sendButton = document.getElementById("send_button");
            const senderId = BigInt(
                document.getElementById("sender_id").value.trim()
            );
            const senderName =
                document.getElementById("sender_name").value.trim();
            const payloadData = {
                action: "insert",
                booking_no: bookingNo,
                receiver_id: receiverId.toString(),
                sender_id: senderId.toString(),
                sender_name: senderName,
                message: messageContent
            };

            if (receiverName !== "") {
                payloadData["receiver_name"] = receiverName;
            }

            const payload = JSON.stringify(payloadData);

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

        observer.observe(panelBody, { childList: true, subtree: false });

        document.getElementById("char_counter").textContent =
            defaultChatCounter;

        // Restore sender ID and username from localStorage on load
        window.onload = () => {
            initWebSocket();

            const storedBooking = localStorage.getItem("chat_booking_no");
            const storedReceiverId = localStorage.getItem("chat_receiver_id");
            const storedReceiverName =
                localStorage.getItem("chat_receiver_name");
            const storedSenderId = localStorage.getItem("chat_sender_id");
            const storedSenderName = localStorage.getItem("chat_sender_name");

            document.getElementById("booking_no").value =
                storedBooking || "ID1234567";
            document.getElementById("receiver_id").value =
                storedReceiverId || "";
            document.getElementById("receiver_name").value =
                storedReceiverName || "";
            document.getElementById("sender_id").value =
                storedSenderId || "1";
            document.getElementById("sender_name").value =
                storedSenderName || "guest";

            // List of input fields to watch
            [
                "booking_no",
                "receiver_id",
                "receiver_name",
                "sender_id",
                "sender_name"
            ].forEach(attrId => {
                document.getElementById(attrId).addEventListener(
                    "input",
                    () => {
                        localStorage.setItem(
                            "chat_" + attrId,
                            document.getElementById(attrId).value.trim()
                        );

                        if (attrId === "booking_no") {
                            updateConversationTitle();
                        }

                        validateInputsAndToggleButton();
                    });
                }
            );

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
