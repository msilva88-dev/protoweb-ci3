const CHAT_BOX = document.getElementById("chat-box-div");
const FETCH_MESSAGES = {
    isLoading: false,
    offset: 0
};
const LIMIT = 50; // Messages per page
const LOADED_MESSAGES = new Set(); // To track rendered messages
const LOAD_MORE_BUTTON = document.getElementById("load-more-button");
const SENDER_ID = document.getElementById("sender-id-input").value;
const URL = {
    api_dynjson: document.currentScript.getAttribute("data-dynjson-api-url"),
    ws: document.currentScript.getAttribute("data-ws-url")
};
const WS = {
    closeCode: {
        1000: "Normal closure",
        1001: "Going away",
        1002: "Protocol error",
        1003: "Unsupported data",
        1005: "No status rcvd", // reserved
        1006: "Abnormal closure", // reserved
        1007: "Invalid frame payload data", // Unsupported payload
        1008: "Policy violation",
        1009: "Message too big", // Too large
        1010: "Mandatory extension",
        1011: "Internal error", // Server error
        1012: "Service restart",
        1013: "Try again later",
        1014: "Bad gateway",
        1015: "TLS handshake fail", // reserved
        3000: "Unauthorized",
        3003: "Forbidden",
        3008: "Timeout"
    },
    maxRetryAttempts: 5,
    obj: new WebSocket(URL.ws),
    retryAttempts: 0
};


// Delete a message
function deleteMessage(MESSAGE_ID, messageElement) {
    consLog("chat_delmsg_attempt", MESSAGE_ID, messageElement);

    /*
    console.log('document.cookie', document.cookie);

    // Extract CSRF token from the cookie
    const CSRF_TOKEN = document.cookie.split('; ').find(
        row => row.startsWith('csrf_cookie=')
    )?.split('=')[1];

    console.log('CSRF_TOKEN', CSRF_TOKEN);

    if (!CSRF_TOKEN) {
        console.error("CSRF Token is missing.");

        showError("Failed to delete message due to missing CSRF token.");

        return;
    }
    */

    console.log('DELETE NOW!!!');

    fetch(`${URL.api_dynjson}/delete_message`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            //"X-CSRF-Token": CSRF_TOKEN
        },
        body: JSON.stringify({ message_id: MESSAGE_ID })
    })
      .then((response) => {
        if (!response.ok) {
            //throw new Error(`HTTP Error: ${response.status}`);
            consLog("chat_delmsg_fail", MESSAGE_ID, messageElement,
                    `HTTP Error: ${response.status}`);
        }

        /*
        console.log('RESPONSE');
        console.log('document.cookie', document.cookie);
        console.log('CSRF_TOKEN', CSRF_TOKEN);
        console.log('X-CSRF-Token', response.headers.get('X-CSRF-Token'));
        */

        return response.json();
    })
      .then((data) => {
        /*
        if (data.csrf_hash) {
            // Update the CSRF token for future requests
            document.cookie = `csrf_cookie_name=${data.csrf_hash}; path=/`;

            console.log('Updated CSRF Token:', data.csrf_hash);
        }

        console.log('data.csrf_hash', data.csrf_hash);
        console.log('document.cookie', document.cookie);
        */

        if (data.status === "success") {
            // Remove the message from the UI
            messageElement.remove();

            // Remove from tracking
            LOADED_MESSAGES.delete(MESSAGE_ID);

            consLog("chat_delmsg_success", MESSAGE_ID, messageElement);
        } else {
            consLog("chat_delmsg_fail", MESSAGE_ID,
                    messageElement, data.message);

            //showError(data.message || "Failed to delete the message.");
        }
    })
      .catch((error) => {
          consLog("chat_delmsg_error", MESSAGE_ID, messageElement, error);

          //showError("An error occurred while trying to delete the message.");
    });
}


// Display error
function showError(message) {
    let errorDiv = document.getElementById("error-message");

    if (!errorDiv) {
        errorDiv = document.createElement("div");
        errorDiv.id = "error-message";
        errorDiv.style.color = "red";
        errorDiv.style.margin = "10px 0";

        document.body.insertBefore(errorDiv, CHAT_BOX);
    }

    errorDiv.textContent = `Error: ${message}`;

    // Avoid multiple timers for the same errorDiv
    clearTimeout(errorDiv.timeout);

    // Remove the error message after 5 seconds
    errorDiv.timeout = setTimeout(() => errorDiv.remove(), 5000);
}


// Render messages
function renderMessage(message, SENDER_ID, prepend = false) {
    consLog("chat_rendmsg_attempt", message, SENDER_ID);

    if (!message.sender_id) {
        console.error("Message Sender ID is missing:", message);

        return;
    }

    // Avoid duplicate rendering
    if (LOADED_MESSAGES.has(message.message_id)) {
        return;
    }

    // Mark as rendered
    LOADED_MESSAGES.add(message.message_id);

    const MESSAGE_DIV = document.createElement("div");

    // Graceful fallback for missing fields
    const SENDER = message.sender || "Unknown user";
    const RECEIVER = message.receiver || "public";
    const CONTENT = message.content || "No content";
    const TIMESTAMP = message.timestamp || "No timestamp";

    MESSAGE_DIV.className = "message";
    MESSAGE_DIV.textContent =
      `${SENDER} -> ${RECEIVER}: ${CONTENT} (${TIMESTAMP})`;

    // Add delete button if the sender is the logged-in user
    //if (message.sender_id == SENDER_ID) {
    if (parseInt(message.sender_id) === parseInt(SENDER_ID)) {
        const DELETE_BUTTON = document.createElement("button");

        DELETE_BUTTON.textContent = "Delete";
        DELETE_BUTTON.style.marginLeft = "10px";

        // Add delete functionality
        DELETE_BUTTON.onclick = () => {
            deleteMessage(message.message_id, MESSAGE_DIV);
        };

        MESSAGE_DIV.appendChild(DELETE_BUTTON);
    }

    // Prepend for older messages, append for real-time updates
    prepend
      ? CHAT_BOX.prepend(MESSAGE_DIV)
      : CHAT_BOX.appendChild(MESSAGE_DIV);
}


// Fetch messages with pagination
function fetchMessages() {
    // Avoid multiple simultaneous fetch calls
    if (FETCH_MESSAGES.isLoading) {
        return;
    }

    FETCH_MESSAGES.isLoading = true;
    LOAD_MORE_BUTTON.disabled = true;

    fetch(`${URL.api_dynjson}/get_recent_messages`
          + `?user_id=${SENDER_ID}`
          + `&limit=${LIMIT}`
          + `&offset=${FETCH_MESSAGES.offset}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success" && data.data.length > 0) {
            data.data.forEach(
                (message) => renderMessage(message, SENDER_ID, true)
            );

            // Increment global offset
            FETCH_MESSAGES.offset += data.data.length;
        } else {
            console.log("No messages found.");

            LOAD_MORE_BUTTON.style.display = "none";
        }
    })
      .catch((error) => console.error("Error fetching messages:", error))
      .finally(() => {
        FETCH_MESSAGES.isLoading = false;
        LOAD_MORE_BUTTON.disabled = false;
    });
}


// Attach event listener to "Load More" button
document.getElementById("load-more-button")
  .addEventListener("click", () => {
    try {
        fetchMessages();
    } catch (error) {
        showError("Failed to load more messages.");
    }
});

// Handle incoming WebSocket messages
WS.obj.onmessage = (event) => {
    try {
        const DATA = JSON.parse(event.data);

        // Validate message structure
        if (!DATA.message_id || !DATA.sender
            || !DATA.content || !DATA.timestamp)
        {
            console.error("Malformed message received:", DATA);

            return;
        }

        // Render the new message
        renderMessage(DATA, SENDER_ID);
    } catch (error) {
        console.error("Error processing WebSocket message:", error);
    }
};

WS.obj.onclose = (event) => {
    const REASON =
      WS.closeCode[event.code] || event.reason || "Unknown reason";

    console.warn(`WebSocket closed: ${event.code} - ${REASON}`);

    if (WS.retryAttempts < WS.maxRetryAttempts) {
        console.log("Reconnecting WebSocket...");

        WS.retryAttempts++;

        setTimeout(() => {
            WS.obj = new WebSocket(URL.ws);

            // Reassign event handlers to the new WebSocket instance
            WS.obj.onopen = () => {
                console.log("WebSocket reconnected successfully.");

                WS.retryAttempts = 0; // Reset retry attempts
            };

            WS.obj.onmessage = WS.obj.onmessage;
            WS.obj.onclose = WS.obj.onclose;
            WS.obj.onerror = WS.obj.onerror;
        }, 2000); // Retry after 2 seconds
    } else {
        showError("WebSocket connection failed. Falling back to HTTP.");
    }
};

// WebSocket connection handlers
WS.obj.onopen = () => console.log("WebSocket connection established.");
WS.obj.onerror = (error) => console.error("WebSocket error:", error);

// Handle chat form submission
document.getElementById("chat-form").onsubmit = (event) => {
    // Prevent form from refreshing the page
    event.preventDefault();

    const CONTENT = document.getElementById("message-input").value.trim();

    if (!CONTENT) {
        showError("Message cannot be empty.");

        return;
    }

    const MESSAGE_DATA = JSON.stringify({
        sender_id: SENDER_ID,
        receiver_id: document.getElementById("receiver-id-input").value
                     || null,
        content: CONTENT
    });

    // Send the message through WebSocket
    if (WS.obj.readyState === WebSocket.OPEN) {
        // WebSocket will broadcast and update the UI
        WS.obj.send(MESSAGE_DATA);
    } else {
        console.error("WebSocket is not open. Falling back to HTTP POST.");

        // Fallback to HTTP POST if WebSocket is not available
        fetch(`${URL.api_dynjson}/send_message`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: MESSAGE_DATA // This is your JSON message
        })
          .then((response) => response.json())
          .then(data => {
            if (data.status === "success") {
                renderMessage(data.message, SENDER_ID);
            } else {
                console.error("HTTP POST failed:", data.message);
            }
        })
          .catch((error) => {
            console.error("Error sending message via HTTP POST:", error);
        });
    }

    // Clear the input field after sending the message
    document.getElementById("message-input").value = "";
};

// Initial fetch
fetchMessages();
