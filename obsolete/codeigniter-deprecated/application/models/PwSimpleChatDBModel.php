<?php

/*
 * Copyright (c) 2025, Márcio Delgado <marcio@libreware.info>
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


class PwSimpleChatDBModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Handle WebSocket requests.
     *
     * @param string $message - JSON string from WebSocket.
     * @return string - JSON encoded response.
     */
    public function request($message) {
        $data = json_decode($message, true);

        if ($data === null) {
            return json_encode(['error' => 'Invalid JSON']);
        }

        if (!isset($data['action'])) {
            return json_encode(['error' => 'Invalid message format']);
        }

        switch ($data['action']) {
            case 'delete_message':
                return $this->deleteMessage($data);
            case 'get_messages':
                return $this->getMessages($data);
            case 'patch_message':
                return $this->patchMessage($data);
            case 'post_message':
                return $this->postMessage($data);
            default:
                return json_encode(['error' => 'Unknown action']);
        }
    }

    /**
     * Insert a new message into the database.
     *
     * @param array $data - Associative array containing 'content', 'sender_id', 'receiver_id'.
     * @return string - JSON encoded response.
     */
    private function postMessage($data) {
        if (isset($data['content'], $data['sender_id'], $data['receiver_id'])) {
            $insert_data = [
                'content' => $data['content'],
                'sender_id' => $data['sender_id'],
                'receiver_id' => $data['receiver_id'],
                'timestamp' => date('Y-m-d H:i:s')
            ];

            if ($this->db->insert('messages', $insert_data)) {
                return json_encode(['success' => true, 'message_id' => $this->db->insert_id()]);
            }
        }
        return json_encode(['success' => false, 'error' => 'Failed to send message']);
    }

    /**
     * Fetch recent messages between two users.
     *
     * @param array $data - Associative array containing 'user1_id', 'user2_id', and optional 'limit'.
     * @return string - JSON encoded response.
     */
    private function getMessages($data) {
        if (isset($data['user1_id'], $data['user2_id'])) {
            $this->db->select('id, content, timestamp, sender_id, receiver_id');
            $this->db->from('messages');
            $this->db->group_start()
                     ->where('sender_id', $data['user1_id'])
                     ->where('receiver_id', $data['user2_id'])
                     ->group_end()
                     ->or_group_start()
                     ->where('sender_id', $data['user2_id'])
                     ->where('receiver_id', $data['user1_id'])
                     ->group_end();
            $this->db->order_by('timestamp', 'DESC');
            $this->db->limit($data['limit'] ?? 10);

            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                return json_encode(['success' => true, 'messages' => $query->result_array()]);
            }
        }
        return json_encode(['success' => false, 'error' => 'Failed to fetch messages']);
    }

    /**
     * Delete messages older than a specific timestamp.
     *
     * @param string $timestamp - Timestamp in 'Y-m-d H:i:s' format.
     * @return bool - True on success, false on failure.
     */
    public function delete_old_messages($timestamp) {
        $this->db->where('timestamp <', $timestamp);
        return $this->db->delete('messages');
    }
}
