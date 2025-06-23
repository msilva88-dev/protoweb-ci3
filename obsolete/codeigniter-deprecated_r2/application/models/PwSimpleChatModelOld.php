<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// Global constants
define('TBL_PREF', 'fc_');
define('MED_MESSAGE', TBL_PREF . 'med_message');
define('USERS', TBL_PREF . 'users');

/**
 * PwChatModel
 *
 * Handles chat messages stored in fc_med_message.
 *
 * @author Marcio Delgado <marcio@libreware.info>
 * @license BSD-2-Clause
 * @since 2025
 */
class PwChatModel extends CI_Model
{
    /**
     * Get older messages before a specific message ID or date.
     *
     * @param string $booking_no
     *     Booking ID to filter messages.
     * @param int|null $before_id
     *     Optional. Fetch messages with ID < this.
     * @param string|null $before_date
     *     Optional. Fetch messages with dateAdded < this.
     * @param int $limit
     *     Maximum number of messages to retrieve.
     *
     * @return array[]
     */
    public function getOlderMessagesByBooking(
        string $booking_no,
        ?int $before_id = null,
        ?string $before_date = null,
        int $limit = 50
    ): array {
        $this->db->from(MED_MESSAGE)
            ->where('bookingNo', $booking_no)
            ->order_by('dateAdded', 'DESC')
            ->limit($limit);

        if ($before_id !== null) {
            $this->db->where('id <', $before_id);
        }

        if ($before_date !== null) {
            $this->db->where('dateAdded <', $before_date);
        }

        $query = $this->db->get();

        if (!$query) {
            return [];
        }

        return $query->result_array();
    }

    /**
     * Get recent messages for a given booking number.
     *
     * @param string $booking_no Booking ID to filter messages.
     * @param int    $limit      Maximum number of messages to retrieve.
     *
     * @return array[]
     *     Array of associative message rows ordered by dateAdded ASC.
     */
    public function getRecentMessagesByBooking(
        string $booking_no,
        int $limit = 50
    ): array {
        $query = $this->db->from(MED_MESSAGE)
            ->where('bookingNo', $booking_no)
            ->order_by('dateAdded', 'ASC')
            ->limit($limit)
            ->get();

        return $query ? $query->result_array() : [];
    }


    /**
     * Insert a chat message into fc_med_message.
     *
     * @param array $data {
     *     @type int    $product_id
     *     @type string $booking_no
     *     @type int    $sender_id
     *     @type int    $receiver_id
     *     @type string $subject
     *     @type string $message
     *     @type string $status        (Pending, Accept, Decline)
     *     @type string $currency_code
     * }
     * @return bool True on success, false on failure.
     */
    public function insertMessage(array $data): bool
    {
        // Basic field validation
        if (
            empty($data['booking_no']) ||
            empty($data['sender_id']) ||
            !isset($data['message'])
        ) {
            return false;
        }

        $insert = [
            'productId' => $data['product_id'] ?? 0,
            'bookingNo' => $data['booking_no'], // booking_id
            'senderId' => $data['sender_id'], // user_id
            'receiverId' => $data['receiver_id'] ?? 0,
            'admin_id' => $data['admin_id'] ?? 0,
            'subject' => $data['subject'] ?? '',
            'message' => $data['message'], // message
            //'dateAdded' => NOW, // Default // date + time
            //'msg_read' => 'No', // Default
            'status' => $data['status'] ?? 'Pending',
            //'point' => 0, // Default
            //'user_archive_status' => 'No', // Default
            //'host_archive_status' => 'No', // Default
            //'user_msgread_status' => 'No', // Default
            //'host_msgread_status' => 'No', // Default
            'msg_status' => 1,
            'currencycode' => $data['currency_code'] ?? 'USD' //,
            //'msg_star_status' => 'No' // Default
        ];

        return $this->db->insert(MED_MESSAGE, $insert);
    }

    /**
     * Mark a specific message as read by the receiver.
     *
     * @param int $message_id  The message ID to mark as read.
     * @param int $receiver_id Optional.
     *     The expected receiver ID (for validation).
     *
     * @return bool True on success, false on failure.
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
}
