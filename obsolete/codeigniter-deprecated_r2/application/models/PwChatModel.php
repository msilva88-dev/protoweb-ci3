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

//namespace;

/**
 * PwChatModel
 *
 * This model manages all interactions with the `fc_med_message` table,
 * including message creation, updates, retrieval, and bulk operations.
 *
 * @author Márcio Delgado <marcio@libreware.info>
 * @license BSD-2-Clause
 * @since 2025
 */
class PwChatModel extends CI_Model
{
    /**
     * Validates that the given message structure is well-formed.
     *
     * @param array $msg
     *     The message to validate.
     * @param string|null $expected_booking_no
     *     Optional. Booking number to validate against.
     *
     * @return bool
     *     True if message is valid, otherwise false.
     */
    protected function isValidMessage(
        array $msg,
        ?string $expected_booking_no = null
    ): bool {
        // Basic structure
        if (
            !isset($msg['bookingNo'], $msg['senderId'], $msg['message']) ||
            trim($msg['bookingNo']) === '' ||
            trim((string)$msg['message']) === ''
        ) {
            return false;
        }

        /*
         * If a specific bookingNo is expected,
         * it must match the message's bookingNo
         */
        if ($expected_booking_no !== null) {
            if ($msg['bookingNo'] !== $expected_booking_no) {
                return false;
            }
        }

        // Validate senderId exists in fc_users
        $senderExists = $this->db
            ->select('id')
            ->from(USERS)
            ->where('id', (int)$msg['senderId'])
            ->get()
            ->num_rows() > 0;

        if (!$senderExists) {
            return false;
        }

        // Validate receiverId if provided
        if (!empty($msg['receiverId'])) {
            $receiverExists = $this->db
                ->select('id')
                ->from(USERS)
                ->where('id', (int)$msg['receiverId'])
                ->get()
                ->num_rows() > 0;

            if (!$receiverExists) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieves the user ID for a given user name.
     *
     * @param string $user_name
     *     The username to look up.
     *
     * @return int
     *     The user ID if found, or 0 if not found.
     */
    public function checkUserId(string $user_id): bool
    {
        if ($user_id < 1) {
            return false;
        }

        $query = $this->db
            ->select('user_name')
            ->from(USERS)
            ->where('id', $user_id)
            ->get();

        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Deletes all messages associated with a specific booking number.
     *
     * @param string $booking_no
     *     The booking number to target.
     *
     * @return bool
     *     True on success, false on failure.
     */
    public function deleteMessagesByBooking(string $booking_no): bool
    {
        if (empty($booking_no)) {
            return false;
        }

        $this->db->where('bookingNo', $booking_no);

        return $this->db->delete(MED_MESSAGE);
    }

    /**
     * Retrieves messages linked to a booking number,
     * with pagination options.
     *
     * @param string $booking_no
     *     The booking number.
     * @param int $limit
     *     Max number of messages to return.
     * @param int|null $before_id
     *     Optional message ID to paginate backwards.
     * @param string|null $before_date
     *     Optional dateAdded filter.
     *
     * @return array{
     *     messages: array[],
     *     total_count: int,
     *     last_id: int|null,
     *     last_dateAdded: string|null,
     *     has_more: bool
     * }
     *     Result set with metadata.
     */
    public function getMessagesByBooking(
        string $booking_no,
        int $limit = 50,
        ?int $before_id = null,
        ?string $before_date = null
    ): array {
        if (empty($booking_no)) {
            return [
                'messages' => [],
                'total_count' => 0,
                'last_id' => null,
                'last_date_added' => null,
                'has_more' => false
            ];
        }

        // Count total messages for this booking
        $total = $this->db
            ->from(MED_MESSAGE)
            ->where('bookingNo', $booking_no)
            ->count_all_results();

        // Build query with optional filters
        // Fetch one extra to detect if more exist
        $this->db->from(MED_MESSAGE)
            ->where('bookingNo', $booking_no)
            ->limit($limit + 1);

        if ($before_id !== null) {
            $this->db->where('id <', $before_id);
        }

        if ($before_date !== null) {
            $this->db->where('dateAdded <', $before_date);
        }

        $this->db->order_by(
            'dateAdded',
            ($before_id || $before_date) ? 'DESC' : 'ASC'
        );

        $query = $this->db->get();

        if (!$query) {
            return [
                'messages' => [],
                'total_count' => $total,
                'last_id' => null,
                'last_date_added' => null,
                'has_more' => false
            ];
        }

        $results = $query->result_array();
        $has_more = count($results) > $limit;

        if ($has_more) {
            array_pop($results); // Remove the extra message
        }

        $last = end($results) ?: null;

        return [
            'messages' => $results,
            'total_count' => $total,
            'last_id' => $last['id'] ?? null,
            'last_date_added' => $last['dateAdded'] ?? null,
            'has_more' => $has_more
        ];
    }

    /**
     * Retrieves the user name for a given user ID.
     *
     * @param int $user_id
     *     The user ID to look up.
     *
     * @return string
     *     The user name if found, or '' if not found.
     */
    public function getUserNameByUserId(int $user_id): string
    {
        if ($user_id < 1) {
            return "";
        }

        $query = $this->db
            ->select('user_name')
            ->from(USERS)
            ->where('id', $user_id)
            ->get();

        if ($query->num_rows() > 0) {
            return $query->row()->user_name;
        }

        return "";
    }

    /**
     * Retrieves the user ID for a given user name.
     *
     * @param string $user_name
     *     The username to look up.
     *
     * @return int
     *     The user ID if found, or 0 if not found.
     */
    public function getUserIdByUserName(string $user_name): int
    {
        $user_name = trim($user_name);

        if ($user_name === '') {
            return 0;
        }

        $query = $this->db
            ->select('id')
            ->from(USERS)
            ->where('user_name', $user_name)
            ->get();

        if ($query->num_rows() > 0) {
            return (int)$query->row()->id;
        }

        return 0;
    }

    /**
     * Insert a single message into fc_med_message.
     *
     * @param array $msg {
     *     @type int $product_id
     *     @type string $booking_no
     *     @type int $sender_id
     *     @type int $receiver_id
     *     @type string $subject
     *     @type string $message
     *     @type string $status
     *         (Pending, Accept, Decline)
     *     @type string $currency_code
     * }
     *     The message details.
     *
     * @return bool
     *     True on success, false on failure.
     */
    public function insertMessage(array $msg): bool
    {
        // Basic field validation
        if (!$this->isValidMessage($msg)) {
            return false;
        }

        $insert = [
            'productId' => $msg['product_id'] ?? 0,
            'bookingNo' => $msg['booking_no'], // booking_id
            'senderId' => $msg['sender_id'], // user_id
            'receiverId' => $msg['receiver_id'] ?? 0,
            'admin_id' => $msg['admin_id'] ?? 0,
            'subject' => $msg['subject'] ?? '',
            'message' => $msg['message'], // message
            //'dateAdded' => NOW, // Default // date + time
            //'msg_read' => 'No', // Default
            'status' => $msg['status'] ?? 'Pending',
            //'point' => 0, // Default
            //'user_archive_status' => 'No', // Default
            //'host_archive_status' => 'No', // Default
            //'user_msgread_status' => 'No', // Default
            //'host_msgread_status' => 'No', // Default
            'msg_status' => 1,
            'currencycode' => $msg['currency_code'] ?? 'USD' //,
            //'msg_star_status' => 'No' // Default
        ];

        return $this->db->insert(MED_MESSAGE, $insert);
    }

    /**
     * Mark a specific message as read by the receiver.
     *
     * @param int $message_id
     *     The message ID to mark as read.
     * @param int $receiver_id Optional.
     *     Optional receiver ID for security check.
     *
     * @return bool
     *     True if marked successfully, otherwise false.
     */
    public function markMessageAsRead(
        int $message_id,
        int $receiver_id = 0
    ): bool {
        if ($message_id < 1) {
            return false;
        }

        $this->db->where('id', $message_id);

        if ($receiver_id > 0) {
            $this->db->where('receiverId', $receiver_id);
        }

        return $this->db->update(MED_MESSAGE, [
            'msg_read' => 'Yes',
            'user_msgread_status' => 'Yes',
            'host_msgread_status' => 'Yes'
        ]);
    }

    /**
     * Updates specific fields in a single message.
     *
     * @param int $message_id
     *     The message ID to modify.
     * @param array $msg
     *     Data to update.
     *
     * @return bool
     *     True if successful, false otherwise.
     */
    public function modifyMessage(int $message_id, array $msg): bool
    {
        if ($message_id < 1 || empty($msg)) {
            return false;
        }

        $this->db->where('id', $message_id);

        return $this->db->update(MED_MESSAGE, $msg);
    }

    /**
     * Replaces all rows in the message table.
     *
     * @param array[] $messages
     *     New set of messages to insert.
     *
     * @return bool
     *     True if all insertions succeed, otherwise false.
     */
    public function postAllMessages(array $messages): bool
    {
        if (empty($messages)) {
            return false;
        }

        $this->db->trans_start();
        $this->db->truncate(MED_MESSAGE);

        foreach ($messages as $msg) {
            if (!$this->isValidMessage($msg)) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->insert(MED_MESSAGE, $msg);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Replaces all messages related to a booking number.
     *
     * @param string $booking_no
     *     Target booking number.
     * @param array[] $messages
     *     Messages to insert.
     *
     * @return bool
     *     True if successful, otherwise false.
     */
    public function postMessagesByBooking(
        string $booking_no,
        array $messages
    ): bool {
        if (empty($booking_no) || empty($messages)) {
            return false;
        }

        $this->db->trans_start();
        $this->db->where('bookingNo', $booking_no)->delete(MED_MESSAGE);

        foreach ($messages as $msg) {
            if (!$this->isValidMessage($msg, $booking_no)) {
                $this->db->trans_rollback();

                return false;
            }

            $this->db->insert(MED_MESSAGE, $msg);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Updates every record in the table with the given fields.
     *
     * WARNING: This updates ALL records. Use with caution.
     *
     * @param array $msg
     *     Fields to update.
     *
     * @return bool
     *     True if successful, false otherwise.
     */
    public function putAllMessages(array $messages): bool
    {
        if (empty($messages)) {
            return false;
        }

        return $this->db->update(MED_MESSAGE, $messages);
    }

    /**
     * Updates all messages related to a specific booking number.
     *
     * @param string $booking_no
     *     Booking number to filter messages.
     * @param array $msg
     *     Fields to update.
     *
     * @return bool
     *     True if successful, otherwise false.
     */
    public function putMessagesByBooking(
        string $booking_no,
        array $messages
    ): bool {
        if (empty($booking_no) || empty($messages)) {
            return false;
        }

        $this->db->where('bookingNo', $booking_no);

        return $this->db->update(MED_MESSAGE, $messages);
    }

    /**
     * Permanently deletes a specific message.
     *
     * @param int $message_id
     *     The message ID to remove.
     *
     * @return bool
     *     True if successful, otherwise false.
     */
    public function removeMessage(int $message_id): bool
    {
        if ($message_id < 1) {
            return false;
        }

        $this->db->where('id', $message_id);

        return $this->db->delete(MED_MESSAGE);
    }
}
