<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/autobahn/22.10.1/autobahn.min.js"></script>
</head>
<body>
    <h1>Chat Application</h1>
    <div id="chat"></div>
    <input type="text" id="message" placeholder="Type your message">
    <button id="send">Send</button>

    <script>
        const wsUrl = 'ws://172.27.0.3/ws/';
        const realm = 'realm1';
        const baseUrl = "<?= site_url('chat') ?>";

        const connection = new autobahn.Connection({ url: wsUrl, realm: realm });

console.log("Connecting to WAMP server at", wsUrl);
console.log("Base URL for API:", baseUrl);

        function deleteMessage(messageId) {
            const userId = 1; // Replace with logged-in user ID

            fetch(`${baseUrl}/delete_message`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message_id: messageId, user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Remove the message from the UI
                    document.getElementById(`message-${messageId}`).remove();
                } else {
                    alert(data.message || 'Failed to delete message');
                }
            });
        }

connection.onclose = function (reason, details) {
    console.error("Connection closed", reason, details);
};

        connection.onopen = function (session) {
            console.log("Connected to WAMP server");

            // Subscribe to new message events
            session.subscribe('com.chat.messages', function (args) {
                console.log("Received message payload:", args);

    if (!args || !args[0]) {
        console.error("Invalid message payload:", args);
        return;
    }

                const message = args[0];
    console.log("Parsed message:", message);
                const userId = 1; // Replace with logged-in user ID

                const isSender = message.sender_id === userId;
                const deleteButton = isSender
                    ? `<button onclick="deleteMessage(${message.id})">Delete</button>`
                    : '';

                document.getElementById('chat').innerHTML += `
                    <div id="message-${message.id}">
                        <b>${message.sender}:</b> ${message.content} ${deleteButton}
                    </div>`;

                /*
                document.getElementById('chat').innerHTML += `
                    <div>
                        <b>${message.sender}:</b> ${message.content}
                    </div>`;
                */
            }).then(
                function () {
                    console.log("Subscribed to 'com.chat.messages'");
                },
                function (err) {
                    console.error("Failed to subscribe to 'com.chat.messages':", err);
                }
            );

            // Subscribe to message deletion events
            session.subscribe('com.chat.message_deleted', function (args) {
                console.log("Message deleted:", args);
                const messageId = args[0];
                const messageElement =
                  document.getElementById(`message-${messageId}`);
                if (messageElement) {
                    messageElement.remove();
                }
            }).then(
                function () {
                    console.log("Subscribed to 'com.chat.message_deleted'");
                },
                function (err) {
                    console.error("Failed to subscribe to 'com.chat.message_deleted':", err);
                }
            );

            // Send message logic
            document.getElementById('send').addEventListener('click', function () {
                console.log("Sending message ...");
                const messageContent = document.getElementById('message').value;

                const data = {
                    sender_id: 1,  // Replace with logged-in user ID
                    receiver_id: 2,  // Replace with recipient's user ID
                    message_type_id: 1,  // Assume "text" type
                    content: messageContent
                };

                fetch(`${baseUrl}/send_message`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                }).then(response => response.json())
                .then(result => {
                    console.log("Message send result:", result);
                }).catch(err => {
                    console.error("Error sending message:", err);
                });

                document.getElementById('message').value = '';
            });
        };

        connection.open();
    </script>
</body>
</html>
