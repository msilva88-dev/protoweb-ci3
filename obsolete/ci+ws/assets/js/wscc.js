/*
 * Copyright (c) 2024-2025, Márcio Delgado <marcio@libreware.info>
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
 * Wrote as ES2023/ES14 standard.
 */


/**
 * Returns the meaning of a WebSocket close code
 * based on the provided code value.
 *
 * @param {number} code - The WebSocket close code to interpret.
 * @returns {string} A human-readable description
 *   of the WebSocket close code.
 * @throws Will print errors to the console if the code is invalid.
 */
function getWebSocketCodeMeaning(code) {
    switch (code) {
    case 1000:
        return "Normal closure";
    case 1001:
        return "Going away";
    case 1002:
        return "Protocol error";
    case 1003:
        return "Unsupported data";
    case 1005:
        return "No status received (reserved code)";
    case 1006:
        return "Abnormal closure (reserved code)";
    case 1007:
        return "Invalid frame payload data (unsupported payload)";
    case 1008:
        return "Policy violation";
    case 1009:
        return "Message too big";
    case 1010:
        return "Mandatory extension required";
    case 1011:
        return "Internal server error";
    case 1012:
        return "Service restart";
    case 1013:
        return "Try again later";
    case 1014:
        return "Bad gateway";
    case 1015:
        return "TLS handshake failure (reserved code)";
    case 3000:
        return "Unauthorized (non-standard code)";
    case 3003:
        return "Forbidden (non-standard code)";
    case 3008:
        return "Timeout (non-standard code)";
    default:
        if (typeof code === "number") {
            if (!Number.isInteger(code)) {
                console.error(
                    "The WebSocket connection close code is not an integer."
                );

                return "Closure code is not an integer";
            } else if (code < 0 || code > 4999) {
                console.error(
                    "The WebSocket connection close code is out of range."
                );

                return "Out of range closure code";
            } else {
                console.warn(
                    "The WebSocket connection was closed"
                    + " for an unknown reason."
                );

                return "Unknown closure";
            }
        } else {
            console.error(
                "The WebSocket connection close code is not a number."
            );

            return "Closure code is not a number";
        }
    }
}
