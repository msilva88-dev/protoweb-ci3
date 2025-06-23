/*
 * Copyright (c) 2024-2025, M  rcio Delgado <marcio@libreware.info>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in
 *    the documentation and/or other materials provided with the
 *    distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * This code is written in accordance with the ES2023 (ES14) standard.
 * It ensures high traffic, high performance, low memory usage,
 * and clean code.
 * It adheres to coding standards inspired by PEP7, PEP8,
 * and C-style guidelines.
 */

const ENV = "development";
const HOST = "protoWeb";


/**
 * ...
 */
class Chat {
    #data = {
        urlApiDynJson: "",
        urlWebSocket: ""
    };
    #env = "";
    #host = "";
    #elem = {
        buttonLoadMore: undefined,
        divChatBox: undefined,
        formChat: undefined,
        inputMessage: undefined,
        inputReceiverId: undefined,
        inputSenderId: undefined
    };
    #setLoadedMessages = new Set();
    #state = {
        get: {
            isLoading: false,
            offset: 0
        },
        handleWebSocketClose: {
            retryAttempts: 0
        }
    };
    #webSocket;

    #isWebSocketOpen() {
        return this.#webSocket
               && this.#webSocket.readyState === WebSocket.OPEN;
    }

    #render(message, prepend = false) {
        if (this.#setLoadedMessages.has(message.message_id)) {
            return;
        }

        this.#setLoadedMessages.add(message.message_id);

        const ELEM_DIV_MESSAGE = document.createElement("div");

        ELEM_DIV_MESSAGE.className = "message";
        ELEM_DIV_MESSAGE.textContent =
          `${message.sender} -> ${message.receiver}:`
          + ` ${message.content} (${message.timestamp})`;

        if (parseInt(message.sender_id)
            === parseInt(this.#elem.inputSenderId.value))
        {
            const ELEM_DELETE_BUTTON = document.createElement("button");

            ELEM_DELETE_BUTTON.textContent = "Delete";
            ELEM_DELETE_BUTTON.onclick = () => {
                this.request(
                    "delete",
                    { message_id: message.message_id },
                    ELEM_DIV_MESSAGE
                );
            };

            ELEM_DIV_MESSAGE.appendChild(ELEM_DELETE_BUTTON);
        }

        prepend
          ? this.#elem.divChatBox.prepend(ELEM_DIV_MESSAGE)
          : this.#elem.divChatBox.appendChild(ELEM_DIV_MESSAGE);
    }

    #handleWebSocketClose(event, maxRetryAttempts = 50, timeoutDelay = 2000) {
        if (this.#state.handleWebSocketClose.retryAttempts >= maxRetryAttempts)
        {
            console.error("Max WebSocket reconnection attempts reached.");
            //alert("WebSocket connection lost. Please refresh the page.");

            return;
        }

        setTimeout(() => {
            this.#initializeWebSocket();

            this.#state.handleWebSocketClose.retryAttempts++;
        }, timeoutDelay);
    }

    #handleWebSocketMessage(event) {
        try {
            const DATA = JSON.parse(event.data);

            console.log("WebSocket received:", DATA);

            // Ensure the message format is consistent
            if (DATA.status === "success" && Array.isArray(DATA.data)) {
                DATA.data.forEach(
                    message => this.#render(message, true)
                );

                this.#state.get.offset += DATA.data.length;
            } else if (DATA.message_id) {
                this.#render(DATA);
            } else {
                console.warn(
                    "WebSocket received unexpected message format:",
                    DATA
                );
            }
        } catch (error) {
            console.error("Error processing WebSocket message:", error);
            //alert("Error processing WebSocket message: " + error.message);
        }
    }

    #initializeWebSocket() {
        this.#webSocket = new WebSocket(this.#data.urlWebSocket);
        this.#webSocket.onclose = event => {
            this.#handleWebSocketClose(event);

            console.warn("WebSocket closed. Attempting reconnect...");
        };
        this.#webSocket.onerror =
          error => console.error("WebSocket error:", error);
        this.#webSocket.onmessage =
          event => this.#handleWebSocketMessage(event);
        this.#webSocket.onopen = () => {
            this.#state.get.isLoading = false;

            console.log("WebSocket connection established.");
        };
    }

    async #httpRequest(url, method, params = {}, body = null) {
        try {
            const CSRF_TOKEN =
              document.querySelector('meta[name="csrf-token"]').content;
            const OPTIONS = {
                method,
                headers: { "X-CSRF-Token": CSRF_TOKEN }
            };

            if (body) {
                OPTIONS.body = JSON.stringify(body);
                OPTIONS.headers["Content-Type"] = "application/json";
            }

            const QUERY_STRING =
              method === "GET" || method === "DELETE" ?
              `?${new URLSearchParams(params).toString()}` : "";
            const RESPONSE = await fetch(`${url}${QUERY_STRING}`, OPTIONS);

            if (!RESPONSE.ok) {
                console.error("Error: ", RESPONSE.statusText);
                //alert(`Error: ${RESPONSE.statusText}`);

                throw new Error(`HTTP Error: ${RESPONSE.status}`);
            }

            return await RESPONSE.json();
        } catch (error) {
            console.error(`HTTP ${method} Request Error:`, error);
            //alert(`Error: ${error.message}`);
            //alert("Error connecting to the server. Please try again.");

            return { status: "error", message: error.message };
        }
    }

    async request(action = "get", data = {}, element = null, event = null) {
        switch (action) {
            case "delete":
                // The Data ID is required to delete, ignored
                //data["message_id"] = data["message_id"];

                break;
            case "post":
                if (event !== null) {
                    event.preventDefault();
                }

                const CONTENT = this.#elem.inputMessage.value.trim();

                if (!CONTENT) {
                    console.warn("Message cannot be empty!");
                    //alert("Message cannot be empty!");

                    return;
                }

                data["sender_id"] ??= this.#elem.inputSenderId.value;
                data["receiver_id"] ??= this.#elem.inputReceiverId.value;
                data["content"] = data["content"].trim() ?? CONTENT;

                break;
            case "get":
                if (this.#state.get.isLoading) {
                    return;
                }

                this.#state.get.isLoading = true;
                this.#elem.buttonLoadMore.disabled = true;

                // WebSocket Fallback Delay with Retry Logic
                const WEBSOCKET_WAIT = async (
                    retries = 5,
                    delay = 1000
                ) => {
                    for (let i = 0; i < retries; i++) {
                        if (this.#isWebSocketOpen()) {
                            return true;
                        }

                        console.warn(
                            "Waiting for WebSocket to reconnect..." +
                            ` (${i + 1}/${retries})`
                        );
                        await new Promise(res => setTimeout(res, delay));
                    }

                    return false;
                };

                data["user_id"] ??= this.#elem.inputSenderId.value;
                data["limit"] ??= 50;
                data["offset"] ??= this.#state.get.offset;
        }

        if (
            action === "get" ? await WEBSOCKET_WAIT() : this.#isWebSocketOpen()
        ) {
            wsData = { action, env: this.#env, host: this.#host };

            Object.assign(wsData, data);

            //console.log("WebSocket reconnected, sending data...");
            console.log("Sending WebSocket message:", wsData);

            this.#webSocket.send(JSON.stringify(wsData));
        } else {
            console.warn(
                "WebSocket is not open. " +
                `Falling back to HTTP ${action.toUpperCase()}.`
            );

            try {
                const HTTP_DATA = await this.#httpRequest(
                    `${this.#data.urlApiDynJson}/http_$(action)`,
                    action,
                    action === "post" ? {} : data,
                    action === "post" ? JSON.stringify(data) : null
                );

                if (HTTP_DATA.status === "success") {
                    switch (action) {
                        case "delete":
                            element.remove();

                            this.#setLoadedMessages.delete(data["message_id"]);
                            break;
                        case "get":
                            if (HTTP_DATA.data.length > 0) {
                                HTTP_DATA.data.forEach(
                                    message => this.#render(message, true)
                                );

                                this.#state.get.offset +=
                                    HTTP_DATA.data.length;
                            }

                            break;
                        case "post":
                            this.#render(HTTP_DATA.message);
                    }
                } else {
                    switch (action) {
                        case "delete":
                            console.error("Failed to delete message:", HTTP_DATA.message);
                            //alert("Failed to delete message:", HTTP_DATA.message);

                            break;
                        case "get":
                            this.#elem.buttonLoadMore.style.display = "none";

                        case "post":
                            console.error("Failed to send message:", HTTP_DATA.message);
                            //alert("Failed to send message:", HTTP_DATA.message);
                    }
                }
            } catch (error) {
                console.error(`Error ${action} request:`, error);
                //alert(`Error ${action} request: ` + error.message);
            } finally {
                switch (action) {
                    case "get":
                        this.#state.get.isLoading = false;
                        this.#elem.buttonLoadMore.disabled = false;
                }
            }
        }
    }

    constructor(id, data, env = "development", host = "protoWeb") {
        this.#data = {
            urlApiDynJson: document.currentScript.getAttribute(
                data.urlApiDynJson
            ),
            urlWebSocket: document.currentScript.getAttribute(
                data.urlWebSocket
            )
        };
        this.#elem = {
            buttonLoadMore: document.getElementById(id.buttonLoadMore),
            divChatBox: document.getElementById(id.divChatBox),
            formChat: document.getElementById(id.formChat),
            inputMessage: document.getElementById(id.inputMessage),
            inputReceiverId: document.getElementById(id.inputReceiverId),
            inputSenderId: document.getElementById(id.inputSenderId)
        };
        this.#env = env;
        this.#host = host;

        this.#initializeWebSocket();
        this.#elem.buttonLoadMore.addEventListener("click",() => this.get());

        this.#elem.formChat.onsubmit = event => {
            this.request("post", {}, null, event)
        };
    }
}


const OBJ_CHAT = new Chat(
    {
       buttonLoadMore: "load-more-button",
       divChatBox: "chat-box-div",
       formChat: "chat-form",
       inputMessage: "message-input",
       inputReceiverId: "receiver-id-input",
       inputSenderId: "sender-id-input"
    }, {
       urlApiDynJson: "data-dynjson-api-url",
       urlWebSocket: "data-ws-url"
    }
);

OBJ_CHAT.request();
