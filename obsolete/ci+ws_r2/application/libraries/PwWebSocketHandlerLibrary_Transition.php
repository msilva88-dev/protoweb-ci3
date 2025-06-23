<?php

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
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
 * OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
 * USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * This code is optimized for PHP 7.4+ and follows PSR-12 (coding style)
 * and PSR-5 (PHPDoc) standards. It ensures high traffic handling,
 * high performance, low memory usage, and clean code.
 */

declare(strict_types=1);

defined('BASEPATH') or exit('No direct script access allowed');

namespace App\Libraries;

use App\Libraries\PwLoggerLibrary;
use Exception;
use InvalidArgumentException;
use SplObjectStorage;

class PwWebSocketHandlerLibrary implements \MessageComponentInterface
{
    protected $clients;
    protected $CI;

    private function handleDeleteMessage(
        ConnectionInterface $from,
        array $data
    ): void {
        if (!isset($data['message_id'])) {
            $from->send(json_encode(["error" => "Message ID is required"]));

            return;
        }

        // Attempt to delete the message from the database
        $deleted = $this->CI->chat_model->delete_message($data['message_id']);

        if ($deleted) {
            $response = [
                "action" => "message_deleted",
                "message_id" => $data['message_id']
            ];

            // Broadcast the deletion to all clients
            foreach ($this->clients as $client) {
                $client->send(json_encode($response));
            }
        } else {
            $from->send(json_encode(["error" => "Failed to delete message"]));
        }
    }

    private function handleFetchRecentMessages(
        ConnectionInterface $from,
        array $data
    ): void {
        $user_id = $data['user_id'] ?? 0;
        $batch_size = $data['batch_size'] ?? 10;
        $offset = $data['offset'] ?? 0;

        if ($user_id <= 0) {
            $from->send(json_encode(["error" => "Invalid User ID provided."]));

            return;
        }

        // Fetch recent messages from the database
        $messages = $this->CI->chat_model->fetch_recent_messages($user_id, $batch_size, $offset);

        // Send the fetched messages back to the requesting client
        $from->send(json_encode([
            "action" => "fetched_messages",
            "messages" => $messages
        ]));
    }

    private function handleSendMessage(
        ConnectionInterface $from,
        array $data
    ): void {
        // Validate required fields
        if (!isset($data['sender_id']) || !isset($data['content'])) {
            $from->send(json_encode(['error' => 'Invalid message format']));

            return;
        }

        // Save message to the database
        $message_id = $this->CI->chat_model->save_message(
            $data['sender_id'],
            $data['receiver_id'] ?? null,
            $data['content']
        );

        // Fetch the username for the sender
        $query = $this->CI->db->
          get_where('users', ['id' => $data['sender_id']]);
        $sender = $query->row();

        if (!$sender) {
            echo "Sender not found: {$data['sender_id']}\n";

            $from->send(json_encode(['error' => 'Sender not found']));

            return;
        }

        // Prepare response with message details
        $response = [
            'action' => 'new_message',
            'message_id' => $message_id,
            'content' => $data['content'],
            'timestamp' => date('Y-m-d H:i:s'),
            'sender' => $sender->username,
            'sender_id' => $data['sender_id'],
            'receiver' => isset($data['receiver_id'])
                          ? 'user' . $data['receiver_id'] : 'public'
        ];

        // Broadcast the message to all connected clients
        if (isset($data['receiver_id']) {
            foreach ($this->clients as $client) {
                if ($data['receiver_id'] == $client->resourceId
                    || $client == $from)
                {
                    $client->send(json_encode($response));
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, Exception $e): void
    {
        echo "An error has occurred: {$e->get_message()}\n";

        $conn->close();
    }

    public function onMessage(ConnectionInterface $from, string $msg): void
    {
        // Debug log for received message
        echo "Message received: $msg\n";

        /*
         * Decode the incoming message to add the timestamp
         *
         * true to get an associative array
         */
        $data = json_decode($msg, true);

        // Validate data
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['action']) {
            $from->send(json_encode([
                "error" => "Invalid JSON format received."
            ]));

            return;
        }


        switch ($data['action']) {
        case 'delete_message':
            $this->handleDeleteMessage($from, $data);

            break;
        case 'fetch_recent_messages':
            $this->handleFetchRecentMessages($from, $data);

            break;
        case 'send_message':
            $this->handleSendMessage($from, $data);

            break;
        default:
            echo "Unknown action received: {$data['action']}\n";

            $from->send(json_encode(["error" => "Invalid action"]));
        }
    }

    public function
    onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clients = new SplObjectStorage;

        /*
         * Include index.php
         * to ensure all constants are defined properly
         */
        require_once realpath(__DIR__ . '/../../index.php');

        // Manually set the CodeIgniter instance
        $this->CI =& get_instance();

        $this->CI->load->model('PW_Model_Chat', 'chat_model');
    }
}
