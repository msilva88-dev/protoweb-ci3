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


class PwChatModel extends CI_Model
{
    // Save a message
    public function saveMessage(array $data): int
    {
        // Insert the message
        $this->db->insert('messages', [
            'content' => $data['content'],
            'message_type_id' => $data['message_type_id']
        ]);

        $messageId = $this->db->insert_id();

        // Insert participants
        $this->db->insert('message_participants', [
            'message_id' => $messageId,
            'user_id' => $data['sender_id'],
            'is_sender' => true
        ]);
        $this->db->insert('message_participants', [
            'message_id' => $messageId,
            'user_id' => $data['receiver_id'],
            'is_recipient' => true
        ]);

        return is_int($messageId) ? $messageId : 0;
    }

    // Retrieve messages between two users
    public function getMessages(int $senderId, int $receiverId): array
    {
        $this->db->select(
            'messages.id, messages.content, messages.timestamp,'
            . ' users.username AS sender'
        );
        $this->db->from('messages');
        $this->db->join(
            'message_participants AS sender_part',
            'messages.id = sender_part.message_id'
            . ' AND sender_part.is_sender = 1',
            'inner'
        );
        $this->db->join('users', 'sender_part.user_id = users.id', 'inner');
        $this->db->join(
            'message_participants AS receiver_part',
            'messages.id = receiver_part.message_id'
            . ' AND receiver_part.is_recipient = 1',
            'inner'
        );
        $this->db->where('sender_part.user_id', $senderId);
        $this->db->where('receiver_part.user_id', $receiverId);
        $this->db->or_where('sender_part.user_id', $receiverId);
        $this->db->where('receiver_part.user_id', $senderId);
        $this->db->order_by('messages.timestamp', 'ASC');

        $query = $this->db->get();

        return $query->result_array();
    }

    public function deleteMessage(int $messageId, int $userId): bool
    {
        // Check if the user is the sender of the message
        $this->db->select('id');
        $this->db->from('message_participants');
        $this->db->where('message_id', $messageId);
        $this->db->where('user_id', $userId);
        $this->db->where('is_sender', true);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            // User is the sender; delete the message
            $this->db->delete('messages', ['id' => $messageId]);
            return true;
        }

        return false;
    }

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
}
