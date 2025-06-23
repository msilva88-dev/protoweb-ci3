<?php

/*
 * Copyright (c) 2024-2025, Marcio Delgado <marcio@libreware.info>
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

//namespace Applications\Models;

//use CodeIgniter\Model;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class PwChatModel
 *
 * This model manages all interactions with the `fc_med_message` table,
 * including message creation, updates, retrieval, and bulk operations.
 *
 * PHP version 7.4+ and CodeIgniter version 3.1.13+
 *
 * @author Marcio Delgado <marcio@libreware.info>
 * @copyright 2024-2025 Marcio Delgado
 * @extends CI_Model
 * @license BSD-2-Clause
 * @package Applications\Models
 * @since 2024
 * @version 1.0
 */
final class PwChatModel extends CI_Model
{
    use PwChatDataTypeTrait;
    use PwChatErrorTrait;

    /**
     * Deletes a single message by its ID,
     * optionally returning the deleted message data.
     *
     * This method first retrieves the message to be deleted,
     * validates its ID using BCMath for big integer support,
     * and then attempts the deletion inside a database transaction.
     * If the deletion fails or no rows are affected,
     * the transaction is rolled back and the output is cleared.
     *
     * @param string $id
     *     The ID of the message to delete.
     *     Must be a valid BIGINT-compatible string greater than zero.
     * @param array|null &$deletedMessage
     *     Optional output:
     *         If successful, contains the deleted message data
     *         as an associative array. Null on failure.
     * @param int|null &$error
     *     Optional output:
     *         Error code from PwChatModelErrorEnum,
     *         or NONE (0) on success.
     *
     * @return bool
     *     True if the message was successfully deleted;
     *     false otherwise.
     *
     * @uses MED_MESSAGE
     *     Name of the message table (usually 'fc_med_message').
     *     Defined in 'application/config/constants.php'.
     * @uses selectDataId()
     *     To validate and fetch message data before deletion.
     * @uses logError()
     *     To record and assign error codes to $error.
     * @uses PwChatModelErrorEnum::DB_DELETE_FAILED
     *     Used when no rows are deleted.
     * @uses PwChatModelErrorEnum::NONE
     *     Used when deletion is successful.
     * @uses CI_DB_query_builder::trans_begin()
     *     Starts the database transaction.
     * @uses CI_DB_query_builder::delete()
     *     Executes the delete query.
     * @uses CI_DB_query_builder::affected_rows()
     *     Checks number of affected rows.
     * @uses CI_DB_query_builder::trans_rollback()
     *     Rolls back transaction on error.
     * @uses CI_DB_query_builder::trans_commit()
     *     Commits transaction on success.
     *
     * @see PwChatModelErrorEnum
     *     Enumeration of database-related error codes.
     * @see CI_DB_query_builder
     *     CodeIgniter's query builder for database operations.
     */
    final public function deleteMessage(
        string $id,
        ?array &$deletedMessage = null, // Output
        ?int &$error = null // Output
    ): bool {
        /*
         * Validate message ID as BIGINT (string + BCMath)
         * and fetch message before deletion.
         */
        if (!$this->selectDataId($id, 'message', $deletedMessage, $error)) {
            return false;
        }

        $this->db->trans_begin();
        $this->db->delete(MED_MESSAGE, ['id' => $id]);

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();

            $deletedMessage = null;
            $error = $this->logError(PwChatModelErrorEnum::DB_DELETE_FAILED);

            return false;
        }

        $this->db->trans_commit();

        $error = $this->logError(PwChatModelErrorEnum::NONE);

        return true;
    }

    /**
     * Deletes all messages associated with a given booking number.
     *
     * This method performs paginated (batch-based) retrieval
     * and deletion of messages filtered by booking number, sender ID,
     * and receiver ID.
     * Each batch is deleted inside a database transaction
     * to ensure atomicity and rollback on partial failure.
     *
     * It supports filtering by action mode
     * (example: 'read', 'archive'),
     * as well as optional enforcement of exact booking match.
     * Batch size is configurable via the `$limit` parameter.
     *
     * If references are provided, this method will return:
     * - The total number of messages deleted
     *     (`$deletedCount`) as a string;
     * - The full deleted message data
     *     (`$deletedMessages`) as an array.
     *
     * If any batch deletion affects fewer rows than expected,
     * the transaction is rolled back and the process stops immediately
     * to preserve consistency.
     *
     * @param string $bookingNo
     *     Booking number used to group messages. Must be non-empty.
     * @param string $action
     *     Optional action/status filter for messages
     *     (default: 'none').
     *     Passed to `batchSelectMessagesByBooking()` for filtering.
     * @param int $limit
     *     Maximum number of messages per batch. Default is 50.
     * @param bool $forceBookingNo
     *     Whether to require an exact match on the booking number.
     *     Default: true.
     * @param string $receiverId
     *     Optional receiver ID filter. Default is '0' (no filter).
     * @param string $senderId
     *     Optional sender ID filter. Default is '0' (no filter).
     * @param string|null &$deletedCount
     *     Optional output:
     *         Total number of deleted messages as a string,
     *         or null on failure.
     * @param array|null &$deletedMessages
     *     Optional output:
     *         Array of deleted message records,
     *         or null if none.
     * @param int|null &$error
     *     Optional output:
     *         Error code from PwChatModelErrorEnum.
     *         Returns NONE (0) on success.
     *
     * @return bool
     *     True if one or more messages were successfully deleted;
     *     false if no deletions occurred
     *     or if an error was encountered.
     *
     * @uses MED_MESSAGE
     *     Name of the message table (usually 'fc_med_message').
     *     Defined in 'application/config/constants.php'.
     * @uses batchSelectMessagesByBooking()
     *     For paginated retrieval of messages to delete.
     * @uses logError()
     *     To record and assign error codes to $error.
     * @uses PwChatModelErrorEnum::DB_DELETE_FAILED
     *     Used when no rows are deleted.
     * @uses PwChatModelErrorEnum::NONE
     *     Used when deletions are successful.
     * @uses bccomp()
     *     For comparing string-based numeric counts.
     * @uses bcadd()
     *     For incrementing deleted count accurately.
     * @uses bcCount()
     *     For counting message IDs reliably with BCMath.
     * @uses CI_DB_query_builder::trans_begin()
     *     Starts transaction per batch.
     * @uses CI_DB_query_builder::delete()
     *     Executes the delete query.
     * @uses CI_DB_query_builder::affected_rows()
     *     Checks number of affected rows.
     * @uses CI_DB_query_builder::trans_rollback()
     *     Rolls back transaction on error.
     * @uses CI_DB_query_builder::trans_commit()
     *     Commits transaction on success.
     *
     * @see PwChatModelErrorEnum
     *     Enumeration of database-related error codes.
     * @see CI_DB_query_builder
     *     CodeIgniter's query builder for database operations.
     */
    final public function deleteMessagesByBooking(
        string $bookingNo,
        string $action = 'none',
        int $limit = 50,
        bool $forceBookingNo = true,
        string $receiverId = '0',
        string $senderId = '0',
        ?string &$deletedCount = null, // Output
        ?array &$deletedMessages = null, // Output
        ?int &$error = null // Output
    ): bool {
        $allDeletedCount = '0';
        $allDeletedMessages = [];
        $lastCreatedAt = '';
        $lastId = '0';

        do {
            $ok = $this->batchSelectMessagesByBooking(
                $bookingNo,
                $lastCreatedAt, // Input/Output
                $lastId, // Input/Output
                $hasMoreMessages, // Output
                $batchCount, // Output
                $batchMessages, // Output
                $action,
                $forceBookingNo,
                $limit,
                $receiverId,
                $senderId
            );

            if (!$ok || bccomp($batchCount, '1') < 0) {
                break;
            }

            $idsToDelete = array_column($batchMessages, 'id');

            $this->db->trans_begin();
            $this->db->delete(MED_MESSAGE, ['id' => $idsToDelete]);

            if ($this->db->affected_rows() !== count($idsToDelete)) {
                $this->db->trans_rollback();

                if ($deletedCount !== null) {
                    $deletedCount = '0';
                }

                if ($deletedMessages !== null) {
                    $deletedMessages = null;
                }

                $error =
                    $this->logError(PwChatModelErrorEnum::DB_DELETE_FAILED);

                return false;
            }

            $this->db->trans_commit();

            $allDeletedCount =
                bcadd($allDeletedCount, $this->bcCount($idsToDelete), 0);
            $allDeletedMessages =
                array_merge($allDeletedMessages, $batchMessages);
        } while ($hasMoreMessages);

        $deletedCount = $allDeletedCount;
        $deletedMessages = $allDeletedMessages;
        $error = $this->logError(PwChatModelErrorEnum::NONE);

        return bccomp($allDeletedCount, '0') > 0;
    }

    /**
     * Inserts a new chat message into the database
     * with full validation, encoding normalization,
     * and transactional safety.
     *
     * Validates sender/receiver/admin user IDs, booking number,
     * currency code, subject, message content, and status.
     *
     * The message is converted to UTF-8
     * and stored in a BLOB column (limited by 1000 bytes).
     * The `point` flag is converted
     * to a SQL-compatible string ('0' or '1').
     *
     * @param string $bookingNo
     *     The booking reference number (VARCHAR 250).
     * @param string $message
     *     The message body (BLOB limited by 1000 bytes).
     * @param string $senderId
     *     Sender user ID (BIGINT, as string).
     * @param string $adminId
     *     Admin user ID (BIGINT, as string, default '0').
     * @param string $currencyCode
     *     Currency code (default 'USD').
     * @param bool $point
     *     Whether this message is marked with a point ('0'/'1').
     * @param string $productId
     *     Product ID (BIGINT, as string, default '0').
     * @param string $receiverId
     *     Receiver user ID (BIGINT, as string, default '0').
     * @param string $status
     *     Status string: 'Pending', 'Accept', or 'Decline'.
     * @param string $subject
     *     Optional message subject (VARCHAR 250).
     * @param int|null &$error
     *     Output parameter. Set to one of:
     *     - $this->logError(PwChatModelErrorEnum::NONE
     *     - $this->logError(PwChatModelErrorEnum::STR_ENUM_INVALID
     *     - $this->logError(PwChatModelErrorEnum::DB_INSERT_FAILED
     *     - or other validation error codes
     *
     * @return bool
     *     True on successful insertion;
     *     false on any validation or DB failure.
     */
    final public function insertMessage(
        string $bookingNo,
        string $message,
        string $senderId,
        string $adminId = '0',
        string $currencyCode = 'USD',
        bool $point = false,
        string $productId = '0',
        string $receiverId = '0',
        string $status = 'Pending',
        string $subject = '',
        ?string &$insertId = null, // Output
        ?int &$error = null // Output
    ): bool {
        // Validate message as BLOB (limited by 1000 bytes)
        if (!$this->validDTypeBlob($message, 'blob', 1000, 1, $error)) {
            return false;
        }

        // Validate sender ID as BIGINT (string + BCMath)
        if (!$this->selectUserId($senderId, 'user', null, $error)) {
            return false;
        }

        // Validate user IDs as BIGINT (string + BCMath)
        foreach (
            [
                [$adminId, 'admin'],
                [$receiverId, 'user'],
                [$senderId, 'user']
            ] as [$userId, $table]
        ) {
            if (
                $userId !== '0'
                && !$this->selectUserId($userId, $table, null, $error)
            ) {
                return false;
            }
        }

        // Validate product ID as BIGINT (string + BCMath)
        if (
            $productId !== '0'
            && !$this->selectDataId($productId, 'product', null, $error)
        ) {
            return false;
        }

        // Trimming strings (and some normalizes)
        $bookingNo = trim($bookingNo);
        $currencyCode = strtoupper(trim($currencyCode));
        $message = trim($message);
        $status = ucfirst(strtolower(trim($status)));
        $subject = trim($subject);

        // Validate booking number as VARCHAR(250)
        if (!$this->selectBookingNo($bookingNo, null, $error)) {
            return false;
        }

        // Validate these variables as VARCHAR
        foreach (
            [
                [3, 3, 'varCharMySql', $currencyCode],
                [250, 0, 'varCharMySql', $subject]
            ] as [$max, $min, $type, $val]
        ) {
            if (!$this->validDTypeStr($val, $type, $max, $min, '', $error)) {
                return false;
            }
        }

        // Validate status as ENUM
        if (!in_array($status, ['Pending', 'Accept', 'Decline'], true)) {
            $error = $this->logError(PwChatModelErrorEnum::STR_ENUM_INVALID);

            return false;
        }

        $insert = [
            'productId' => $productId, // SQL BIGINT
            'bookingNo' => $bookingNo, // SQL VARCHAR(250)
            'senderId' => $senderId, // SQL BIGINT
            'receiverId' => $receiverId, // SQL BIGINT
            'admin_id' => $adminId, // SQL BIGINT
            'subject' => $subject, // SQL VARCHAR(250)
            'message' => $message, // SQL BLOB (VARCHAR(250))
            'status' => $status, // SQL ENUM
            'point' => $point ? '1' : '0', // SQL ENUM (BOOL)
            'msg_status' => 1, // SQL TINYINT
            'currencycode' => $currencyCode // SQL VARCHAR(10)
        ];

        $this->db->trans_begin();
        $this->db->insert(MED_MESSAGE, $insert);

        // Obtain the last ID as BIGINT (string + BCMath)
        $query = $this->db->query("SELECT LAST_INSERT_ID() AS id");

        if (!$query || $query->num_rows() < 1) {
            $this->db->trans_rollback();

            $error = $this->logError(PwChatModelErrorEnum::DB_INSERT_FAILED);

            return false;
        }

        $row = $query->row_array();
        $idRaw = $row['id'] ?? null;

        if (!is_string($idRaw) && !is_numeric($idRaw)) {
            $this->db->trans_rollback();

            $error = $this->logError(
                PwChatModelErrorEnum::DB_BIGINT_DONT_RETURN_AS_STRING
            );

            return false;
        }

        $insertId = $idRaw;

        $this->db->trans_commit();

        $error = $this->logError(PwChatModelErrorEnum::NONE);

        return true;
    }

    final public function insertUserWithId(
        string $id,
        string $name,
        ?int &$error = null
    ): bool {
        // Validate the user ID format and range
        $type = 'lossyIdBigInt';
        if (!$this->validDTypeInt($id, $type, null, null, '', $error)) {
            return false;
        }

        /*
         * Validate the user name as a string.
         * This ensures correct range.
         */
        $type = 'varCharMySql';
        if (!$this->validDTypeStr($name, $type, 50, 1, '', $error)) {
            return false;
        }

        // Check the user ID in database
        if ($this->db->where('id', $id)->limit(1)->count_all_results(USERS)) {
            $error = $this->logError(PwChatModelErrorEnum::DB_UID_DUPLICATED);

            return false;
        }

        // Check the user name in database
        if (
            $this->db
                ->where('LOWER(user_name)', strtolower($name))
                ->limit(1)
                ->count_all_results(USERS)
        ) {
            $error =
                $this->logError(PwChatModelErrorEnum::DB_UNAME_DUPLICATE);

            return false;
        }

        /*
         * Set defaults for required NOT NULL fields
         * that have no default.
         * Also are commented default values.
         */
        $data = [
            'id' => $id,
            'mobile_verification_code' => '',
            // 'loginUserType' => 'normal',
            'f_id' => '',
            'google_id' => '',
            'linkedin_id' => '',
            'full_name' => '',
            'user_name' => $name,
            // 'group' => 'User',
            'email' => '',
            'password' => '',
            // 'status' => 'Active',
            // 'is_verified' => 'No',
            // 'id_verified' => 'No',
            // 'ph_verified' => 'No',
            // 'is_brand' => 'no',
            // 'created' => 'NOW',
            'last_login_ip' => '0.0.0.0',
            'thumbnail' => '',
            'address' => '',
            'address2' => '',
            'city' => '',
            'district' => '',
            'state' => '',
            'country' => '',
            'postal_code' => '',
            // 'country_code' => null,
            'phone_no' => '',
            'ph_country' => '',
            's_address' => '',
            's_city' => '',
            's_district' => '',
            's_state' => '',
            's_country' => '',
            's_postal_code' => '',
            's_phone_no' => '',
            'brand_name' => '',
            'brand_description' => '',
            'commision' => 0.00,
            'web_url' => '',
            'bank_name' => '',
            'bank_no' => '',
            'bank_code' => '',
            // 'request_status' => 'Not Requested',
            'verify_code' => '',
            'feature_product' => 0,
            'followers_count' => 0,
            'following_count' => 0,
            'followers' => '{}',
            'following' => '{}',
            'twitter' => '',
            'facebook' => '',
            'google' => '',
            'about' => '',
            // 'age' => '',
            // 'gender' => 'Unspecified',
            // 'language' => 'en',
            // 'visibility' => 'Everyone',
            // 'display_lists' => 'Yes',
            'email_notifications' => '{}',
            'notifications' => '{}',
            // 'updates' => '1',
            'products' => 0,
            'lists' => 0,
            'likes' => 0,
            'location' => '',
            'following_user_lists' => '{}',
            'following_giftguide_lists' => '{}',
            'api_id' => 0,
            'own_products' => '{}',
            'own_count' => 0,
            'referId' => 0,
            'totalReferalAmount' => 0.00,
            // 'referalAmount' => 0.00,
            // 'referalAmount_currency' => 'USD',
            'want_count' => 0,
            // 'refund_amount' => 0.00,
            'paypal_email' => '',
            'contact_count' => 0,
            'firstname' => '',
            'lastname' => '',
            'image' => '',
            'description' => '{}',
            'udescription_ar' => '{}',
            'response_rate' => 0.00,
            'cardType' => '',
            'cardNumber' => '',
            'CCExpDay' => 0,
            'CCExpMnth' => 0,
            'cvv' => '',
            'dob_date' => 0,
            'dob_month' => 0,
            'dob_year' => 1970,
            'school' => '',
            'work' => '',
            'timezone' => '',
            'member_pakage' => '',
            // 'package_status' => 'Pending',
            // 'social_recommend' => 'no',
            // 'search_by_profile' => 'no',
            'emergency_name' => '',
            'emergency_phone' => '',
            'emergency_email' => '',
            'emergency_relationship' => '',
            'languages_known' => '',
            'accname' => '',
            'accno' => '',
            'bankname' => '',
            'Acccountry' => '',
            'swiftcode' => '',
            // 'subscriber' => 'Yes',
            'login_hit' => 0,
            'through' => '',
            'mobile_key' => '',
            'ios_key' => '',
            'user_currency' => '',
            'confirm_password' => '',
            'seller_id' => '',
            'host_status' => 0,
            'rep_code' => '',
            'repcode_id' => 0,
            // 'is_experienced' => '0',
            // 'receive_text_msg' => 'yes',
            'firebase_token' => '',
            // 'apple_id' => null,
            // 'cus_delete_status' => 0,
            // 'birthday' => NOW,
            // 'modified' => NOW,
            // 'last_login_date' => NOW,
            // 'last_logout_date' => NOW,
            // 'member_purchase_date' => NOW,
            // 'expired_date' => NOW,
            // 'package_exp_date' => NOW,
            'created_by' => 0,
            'created_by_type' => 0,
            'modified_by' => 0,
            'modified_by_type' => 0,
            // 'is_address_verified' => '0',
            'ifsc_code' => '',
            'cus_referal_code' => '',
        ];

        /*
         * Explicit insertion with ID
         * requires disabling automatic escaping.
         */
        if (!$this->db->set($data, null, false)->insert(USERS)) {
            $error = $this->logError(PwChatModelErrorEnum::DB_INSERT_FAILED);

            return false;
        }

        $error = $this->logError(PwChatModelErrorEnum::NONE);

        return true;
    }

    /**
     * Applies a message action
     * (such as read, unread, archive, star, etc.)
     * to a specific message by ID, optionally filtered by receiver ID.
     *
     * All updates are executed within a transaction
     * and only committed if at least one row was affected.
     * If the action is invalid or the message is not found,
     * the transaction is rolled back and false is returned.
     *
     * Supported actions:
     * - read:
     *     Marks the message as fully read (all flags to 'Yes')
     * - readByUser:
     *     Sets user read status to 'Yes' and msg_read to 'Yes'
     * - readByHost:
     *     Sets host read status to 'Yes' and msg_read to 'Yes'
     * - unread:
     *     Sets all read-related fields
     *     ('msg_read', 'user_msgread_status', 'host_msgread_status')
     *     to 'No'
     * - unreadByUser:
     *     Sets user_msgread_status to 'No';
     *     if host_msgread_status is also 'No', msg_read is set to 'No'
     * - unreadByHost:
     *     Sets host_msgread_status to 'No';
     *     if user_msgread_status is also 'No', msg_read is set to 'No'
     * - archive / archiveByUser / archiveByHost:
     *     Sets archive status flags to 'Yes'
     * - unarchive / unarchiveByUser / unarchiveByHost:
     *     Clears archive status flags (set to 'No')
     * - star / unstar:
     *     Sets or unsets the star status ('msg_star_status')
     * - point / unpoint:
     *     Sets or unsets the point flag
     *
     * @param int $messageId
     *     The message ID to update. Must be > 0.
     * @param string $action
     *     One of the supported action strings.
     * @param int $receiverId
     *     Optional: if > 0,
     *     the update will include a check for matching receiverId.
     *
     * @return bool
     *     True if the update was successful
     *     and affected at least one row;
     *     false otherwise.
     */
    final public function markMessage(
        string $id,
        string $action = 'none',
        string $receiverId = '0',
        ?int &$error = null
    ): bool {
        // Validate message ID as BIGINT (string + BCMath)
        $type = 'lossyIdBigInt';
        if (
            !$this->validDTypeInt($id, $type, null, null, '', $error)
        ) {
            return false;
        }

        // Validate receiver ID as BIGINT (string + BCMath)
        if (
            $receiverId !== '0'
            && !$this->selectUserId($receiverId, 'user', null, $error)
        ) {
            return false;
        }

        // Reset query builder
        $this->db->reset_query();

        $this->db->where('id', $id);

        if ($receiverId !== '0') {
            $this->db->where('receiverId', $receiverId);
        }

        switch ($action) {
            case 'archive':
                $data = [
                    'user_archive_status' => 'Yes',
                    'host_archive_status' => 'Yes'
                ];

                break;
            case 'archiveByHost':
                $data = [ 'host_archive_status' => 'Yes' ];

                break;
            case 'archiveByUser':
                $data = [ 'user_archive_status' => 'Yes' ];

                break;
            case 'point':
                $data = [ 'point' => '1' ];

                break;
            case 'read':
                $data = [
                    'msg_read' => 'Yes',
                    'user_msgread_status' => 'Yes',
                    'host_msgread_status' => 'Yes'
                ];

                break;
            case 'readByHost':
                $data = [
                    'msg_read' => 'Yes',
                    'host_msgread_status' => 'Yes'
                ];

                break;
            case 'readByUser':
                $data = [
                    'msg_read' => 'Yes',
                    'user_msgread_status' => 'Yes'
                ];

                break;
            case 'star':
                $data = [ 'msg_star_status' => 'Yes' ];

                break;
            case 'unarchive':
                $data = [
                    'user_archive_status' => 'No',
                    'host_archive_status' => 'No'
                ];

                break;
            case 'unarchiveByHost':
                $data = [ 'host_archive_status' => 'No' ];

                break;
            case 'unarchiveByUser':
                $data = [ 'user_archive_status' => 'No' ];

                break;
            case 'unpoint':
                $data = [ 'point' => '0' ];

                break;
            case 'unread':
                $data = [
                    'msg_read' => 'No',
                    'user_msgread_status' => 'No',
                    'host_msgread_status' => 'No'
                ];

                break;
            case 'unreadByHost':
                $data = ['host_msgread_status' => 'No'];

                if (
                    $this->shouldSetMsgReadToNo(
                        $messageId,
                        'user_msgread_status'
                    )
                ) {
                    $data['msg_read'] = 'No';
                }

                break;
            case 'unreadByUser':
                $data = ['user_msgread_status' => 'No'];

                if (
                    $this->shouldSetMsgReadToNo(
                        $messageId,
                        'host_msgread_status'
                    )
                ) {
                    $data['msg_read'] = 'No';
                }

                break;
            case 'unstar':
                $data = [ 'msg_star_status' => 'No' ];

                break;
            default:
                $this->db->trans_rollback();

                return false;
        }

        $this->db->trans_begin();
        $this->db->update(MED_MESSAGE, $data);

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();

            return false;
        }

        $this->db->trans_commit();

        return true;
    }

    /**
     * Verifies if a booking number exists uniquely
     * in the rentals enquiry table.
     *
     * This method validates the given booking No. as a VARCHAR(250),
     * queries the `fc_rentalsenquiry` table to find an exact match,
     * and ensures that exactly one record is returned. If found,
     * the result is assigned to the output parameter `$re`.
     *
     * @param string $id
     *     The booking number to search for.
     *     Must be a valid VARCHAR(250)-compatible non-empty string.
     * @param array|null &$re
     *     Optional output:
     *         The rental enquiry as an associative array,
     *         or null if not found or on error.
     * @param int|null &$error
     *     Optional output:
     *         Error code from PwChatModelErrorEnum,
     *         or NONE (0) on success.
     *
     * @return bool
     *     True if a single valid record was found and assigned;
     *     false otherwise.
     */
    final public function selectBookingNo(
        string $no,
        ?array &$re = null, // Output
        ?int &$error = null // Output
    ): bool {
        // Validate booking number as VARCHAR(250)
        if (!$this->validDTypeStr($no, 'varCharMySql', 250, 1, '', $error)) {
            return false;
        }

        // Clear previous query state
        $this->db->reset_query();

        // Query by exact booking number
        $query = $this->db
            ->select('*')
            ->from(RENTALENQUIRY)
            ->where('Bookingno', $no)
            ->get();

        $rows = $query->result_array();
        $count = $this->bcCount($rows);

        // Verify if booking number exceeds expected range
        if (bccomp($count, '-1') === 0) {
            $error = $this->logError(PwChatModelErrorEnum::DB_BID_OVERFLOW);

            return false;
        }

        // Verify if booking number might not exist
        if (bccomp($count, '1') < 0) {
            $error = $this->logError(PwChatModelErrorEnum::DB_BID_NOT_FOUND);

            return false;
        }

        // Verify if booking number may be duplicated
        if (bccomp($count, '1') > 0) {
            $error = $this->logError(PwChatModelErrorEnum::DB_BID_DUPLICATED);

            return false;
        }

        // Assign the single valid booking number to output
        $re = $rows[0];

        $error = $this->logError(PwChatModelErrorEnum::NONE);

        return true;
    }

    /**
     * Retrieves a single database record by ID from a specified table.
     *
     * This method supports multiple predefined table types
     * (example: 'message', 'admin', 'user').
     * It validates the ID using BCMath-compatible integer checks,
     * then queries the corresponding table for an exact match.
     *
     * If exactly one record is found,
     * it is assigned to the output `$data` parameter.
     * If not found, duplicated, or overflow occurs,
     * the method returns false with a corresponding error code.
     *
     * @param string $id
     *     The ID to search for.
     *     Must be a valid BIGINT-compatible string greater than zero.
     * @param string $table
     *     The table type to query.
     *     Accepted values:
     *         'admin', 'booking', 'rentalsenquiry',
     *         'message', 'product', 'subadmin', 'user'.
     * @param array|null &$data
     *     Optional output:
     *         The retrieved record as an associative array,
     *         or null if not found or on error.
     * @param int|null &$error
     *     Optional output:
     *         Error code from PwChatModelErrorEnum,
     *         or NONE (0) on success.
     *
     * @return bool
     *     True if a single valid record was found and assigned;
     *     false otherwise.
     */
    final public function selectDataId(
        string $id,
        string $table = 'message',
        ?array &$data = null,
        ?int &$error = null
    ): bool {
        // Validate ID as BIGINT (string + BCMath)
        $type = 'lossyIdBigInt';
        if (!$this->validDTypeInt($id, $type, null, null, '', $error)) {
            return false;
        }

        switch ($table) {
            case 'admin':
                $dataDuplicated = PwChatModelErrorEnum::DB_AID_DUPLICATED;
                $dataNotFound = PwChatModelErrorEnum::DB_AID_NOT_FOUND;
                $dataOverflow = PwChatModelErrorEnum::DB_AID_OVERFLOW;
                $dataTable = ADMIN;

                break;
            case 'message':
                $dataDuplicated = PwChatModelErrorEnum::DB_MID_DUPLICATED;
                $dataNotFound = PwChatModelErrorEnum::DB_MID_NOT_FOUND;
                $dataOverflow = PwChatModelErrorEnum::DB_MID_OVERFLOW;
                $dataTable = MED_MESSAGE;

                break;
            case 'product':
                $dataDuplicated = PwChatModelErrorEnum::DB_PID_DUPLICATED;
                $dataNotFound = PwChatModelErrorEnum::DB_PID_NOT_FOUND;
                $dataOverflow = PwChatModelErrorEnum::DB_PID_OVERFLOW;
                $dataTable = PRODUCT;

                break;
            case 'subadmin':
                $dataDuplicated = PwChatModelErrorEnum::DB_SID_DUPLICATED;
                $dataNotFound = PwChatModelErrorEnum::DB_SID_NOT_FOUND;
                $dataOverflow = PwChatModelErrorEnum::DB_SID_OVERFLOW;
                $dataTable = SUBADMIN;

                break;
            case 'user':
                $dataDuplicated = PwChatModelErrorEnum::DB_UID_DUPLICATED;
                $dataNotFound = PwChatModelErrorEnum::DB_UID_NOT_FOUND;
                $dataOverflow = PwChatModelErrorEnum::DB_UID_OVERFLOW;
                $dataTable = USERS;

                break;
            default:
                $error =
                    $this->logError(PwChatModelErrorEnum::DB_TABLE_INVALID);

                return false;
        }

        // Reset query builder
        $this->db->reset_query();

        // Query the database for the specific data by ID
        $query = $this->db
            ->select('*')
            ->from($dataTable)
            ->where('id', $id)
            ->get();

        $rows = $query->result_array();
        $count = $this->bcCount($rows);

        if (bccomp($count, '-1') === 0) {
            $error = $this->logError($dataOverflow);

            return false;
        }

        if (bccomp($count, '1') < 0) {
            $error = $this->logError($dataNotFound);

            return false;
        }

        if (bccomp($count, '1') > 0) {
            $error = $this->logError($dataDuplicated);

            return false;
        }

        // Assign the valid data to the output parameter
        $data = $rows[0];

        $error = $this->logError(PwChatModelErrorEnum::NONE);

        return true;
    }

    /**
     * Retrieves all messages for a booking number in batches,
     * using forward pagination with limits and filters.
     *
     * This method accumulates messages by repeatedly calling
     * batchSelectMessagesByBooking until either:
     *   - the total `limit` is reached, or
     *   - there are no more messages.
     *
     * @param string $bookingNo
     *     Booking number used as the base filter. Must be non-empty.
     * @param int &$selectedCount
     *     Output: total number of messages retrieved.
     * @param array &$selectedMessages
     *     Output: array of all messages retrieved.
     * @param string $action
     *     Optional status filter (see getActionFilter()).
     * @param int $batchLimit
     *     Max number of messages fetched per batch (default: 127).
     *     Must be between 1 and 32767 (`SMALLINT` range).
     * @param int $receiverId
     *     Optional filter by receiver ID.
     * @param int $selectedLimit
     *     Max total number of messages to retrieve (default: 32767).
     *     Must be between 1 and 9223372036854775807 (`BIGINT` range).
     * @param int $senderId
     *     Optional filter by sender ID.
     *
     * @return bool
     *     True if one or more messages were retrieved; false otherwise.
     */
    final public function selectMessagesByBooking(
        string $bookingNo,
        int &$selectedCount, // Output
        array &$selectedMessages, // Output
        string $action = 'none',
        int $batchLimit = 127,
        bool $forceBookingNo = true,
        int $receiverId = 0,
        int $selectedLimit = 32767, // ~160MiB of array data
        int $senderId = 0
    ): bool {
        $allSelectedCount = 0;
        $allSelectedMessages = [];
        $lastCreatedAt = '';
        $lastId = 0;

        /*
         * Validates bookingNo
         * against the range of SQL VARCHAR(250):
         * Minimum: 1 character
         * Maximum: 250 characters
         */
        $bookingNo = trim($bookingNo);
        $min = $forceBookingNo ? 1 : 0;
        $type = 'varCharMySql';
        if (!$this->validDTypeStr($bookingNo, $type, 250, $min, '', $error)) {
            $selectedCount = 0;
            $selectedMessages = [];

            return false;
        }

        /*
         * Validates batchLimit
         * against the range of SQL SIGNED SMALLINT:
         * Minimum: 1 (positive only)
         * Maximum: 2^15 - 1 (32767)
         */
        if ($batchLimit < 1 || $batchLimit > pow(2, 15) - 1) {
            $selectedCount = 0;
            $selectedMessages = [];

            return false;
        }


        /*
         * Validates selectedLimit
         * against the range of SQL SIGNED BIGINT:
         * Minimum: 1 (positive only)
         * Maximum: 2^63 - 1 (9223372036854775807)
         */
        if ($selectedLimit < 1 || $selectedLimit > pow(2, 63) - 1) {
            $selectedCount = 0;
            $selectedMessages = [];

            return false;
        }

        do {
            $remaining = $selectedLimit - count($allSelectedMessages);

            $ok = $this->batchSelectMessagesByBooking(
                $bookingNo,
                $lastCreatedAt, // Input/Output
                $lastId, // Input/Output
                $hasMoreMessages, // Output
                $batchCount, // Output
                $batchMessages, // Output
                $action,
                $forceBookingNo,
                min($remaining, $batchLimit), // prevent huge batches
                $receiverId,
                $senderId
            );

            if (!$ok || $batchCount < 1) {
                break;
            }

            $allSelectedCount += $batchCount;
            $allSelectedMessages = array_merge(
                $allSelectedMessages,
                $batchMessages
            );
        } while (
            $hasMoreMessages && count($allSelectedMessages) < $selectedLimit
        );

        $selectedCount = $allSelectedCount;
        $selectedMessages = $allSelectedMessages;

        return $allSelectedCount > 0;
    }

    /**
     * Checks whether a user with the given ID exists
     * in the `fc_users` table
     * and retrieves their associated `user_name`.
     *
     * The user ID must be a valid string-represented BIGINT.
     * This method supports full 64-bit ranges
     * and avoids PHP integer truncation by working with string IDs
     * and validating them using `validDTypeInt()`.
     *
     * @param string $id
     *     User ID to search.
     *     Must be a string representing a valid BIGINT.
     * @param string &$name
     *     Output parameter.
     *     On success, receives the associated user name.
     *     On failure, remains empty.
     * @param int &$error
     *     Output parameter for error status.
     *
     * @uses validDTypeInt()
     *     Validates the ID format and BIGINT range.
     * @uses validDTypeStr()
     *     Validates the `user_name` format and length.
     * @uses CI_DB_query_builder::select()
     *     Specifies the column to retrieve.
     * @uses CI_DB_query_builder::from()
     *     Specifies the table source.
     * @uses CI_DB_query_builder::where()
     *     Filters by ID.
     * @uses CI_DB_query_builder::limit()
     *     Restricts the number of returned rows.
     * @uses CI_DB_query_builder::get()
     *     Executes the SQL query.
     * @uses CI_DB_result::result_array()
     *     Retrieves the query result as array.
     *
     * @see USERS
     *     Refers to the database table constant used for querying.
     *
     * @return bool
     *     True if the ID is valid and a unique user was found;
     *     false otherwise.
     */
    final public function selectUserId(
        string $id,
        string $table = 'user',
        ?string &$name = null,
        ?int &$error = null
    ): bool {
        // Validate the UserID
        $type = 'lossyIdBigInt';
        if (!$this->validDTypeInt($id, $type, null, null, '', $error)) {
            return false;
        }

        switch ($table) {
            case 'admin':
                $nameKey = 'admin_name';
                $userTable = ADMIN;
            case 'subadmin':
                $nameKey = 'admin_name';
                $userTable = SUBADMIN;
            case 'user':
                $nameKey = 'user_name';
                $userTable = USERS;
            default:
                $error =
                    $this->logError(PwChatModelErrorEnum::DB_TABLE_INVALID);

                return false;
        }

        // Reset query builder
        $this->db->reset_query();

        // Query the database using the ID
        $query = $this->db
            ->select($nameKey)
            ->from($userTable)
            ->where('id', $id)
            ->limit(2)
            ->get();

        $rows = $query->result_array();
        $count = count($rows);

        if ($count <= 0) {
            $error = $this->logError(PwChatModelErrorEnum::DB_UID_NOT_FOUND);

            return false;
        }

        if ($count > 1) {
            $error = $this->logError(PwChatModelErrorEnum::DB_UID_DUPLICATED);

            return false;
        }

        /*
         * Check if the user name exists in the result and is a string.
         * This avoids PHP integer truncation
         * when fetching BIGINT from the database.
         */
        if (
            !isset($rows[0][$nameKey])
            || !is_string($rows[0][$nameKey])
        ) {
            $error = $this->logError(PwChatModelErrorEnum::DB_UNAME_INVALID);

            return false;
        }

        /*
         * Validate the user name as a string.
         * This ensures correct range.
         */
        $type = 'varCharMySql';
        $value = $rows[0][$nameKey];
        if (!$this->validDTypeStr($value, $type, 50, 1, '', $error)) {
            return false;
        }

        // Assign the valid user ID to the output parameter
        $name = $value;

        $error = $this->logError(PwChatModelErrorEnum::NONE);

        return true;
    }

    /**
     * Checks whether a user exists in the `fc_users` table
     * by `user_name` and retrieves their associated user ID.
     *
     * The user name must be a non-empty UTF-8 string
     * (1–50 characters),
     * and it is expected to be unique in the database.
     * Returns the user ID as a string
     * to ensure full BIGINT range compatibility.
     *
     * @param string $name
     *     The unique user name to search for.
     * @param string &$id
     *     Output parameter. On success, receives the user ID as string.
     *     On failure, it is set to '0'.
     * @param int &$error
     *     Output parameter for error status.
     *
     * @uses validDTypeStr()
     *     To validate the user name string.
     * @uses CI_DB_query_builder::select()
     *     Used to specify the column to retrieve.
     * @uses CI_DB_query_builder::from()
     *     Used to specify the source table.
     * @uses CI_DB_query_builder::where()
     *     Adds a WHERE clause using the user name.
     * @uses CI_DB_query_builder::limit()
     *     Optimize query.
     * @uses CI_DB_query_builder::get()
     *     Executes the SELECT query.
     * @uses CI_DB_result::row_array()
     *     Retrieves the result as an associative array.
     *
     * @return bool
     *     True if user name is valid and ID found, false otherwise.
     */
    final public function selectUserName(
        string $name,
        string $table = 'user',
        ?string &$id = null,
        ?int &$error = null
    ): bool {
        // Set default value of user ID
        $id = '0';

        // Validate user name
        if (!$this->validDTypeStr($name, 'varCharMySql', 50, 1, '', $error)) {
            return false;
        }

        switch ($table) {
            case 'admin':
                $nameKey = 'admin_name';
                $userTable = ADMIN;
            case 'subadmin':
                $nameKey = 'admin_name';
                $userTable = SUBADMIN;
            case 'user':
                $nameKey = 'user_name';
                $userTable = USERS;
            default:
                $error =
                    $this->logError(PwChatModelErrorEnum::DB_TABLE_INVALID);

                return false;
        }

        // Reset query builder
        $this->db->reset_query();

        /*
         * Query the database using the user name
         * (case-insensitive using lowercase).
         */
        $query = $this->db
            ->select('id')
            ->from($userTable)
            ->where("LOWER($nameKey)", strtolower($name))
            ->limit(2)
            ->get();

        $rows = $query->result_array();
        $count = count($rows);

        if ($count <= 0) {
            $error =
                $this->logError(PwChatModelErrorEnum::DB_UNAME_NOT_FOUND);

            return false;
        }

        if ($count > 1) {
            $error =
                $this->logError(PwChatModelErrorEnum::DB_UNAME_DUPLICATE);

            return false;
        }

        /*
         * Check if the user ID exists in the result and is a string.
         * This avoids PHP integer truncation
         * when fetching BIGINT from the database.
         */
        if (!isset($rows[0]['id']) || !is_string($rows[0]['id'])) {
            $error = $this->logError(PwChatModelErrorEnum::DB_UID_INVALID);

            return false;
        }

        /*
         * Validate the user ID as a BIGINT-compatible string.
         * This ensures correct range, positive format,
         * and numeric integrity.
         */
        $type = 'lossyIdBigInt';
        $value = $rows[0]['id'];
        if (!$this->validDTypeInt($value, $type, null, null, '', $error)) {
            return false;
        }

        // Assign the valid user ID to the output parameter
        $id = $value;

        $error = $this->logError(PwChatModelErrorEnum::NONE);

        return true;
    }

    /**
     * Retrieves a single batch of messages for a given booking number,
     * filtered optionally by status, sender, receiver,
     * and pagination boundaries.
     *
     * This method is optimized for high-demand systems
     * using forward pagination.
     * It returns structured results through output references
     * and indicates if more messages exist beyond the current batch.
     *
     * All output data is returned via reference parameters.
     *
     * @param string $bookingNo
     *     Booking number used as the base filter. Must be non-empty.
     * @param string &$lastCreatedAt
     *     Input/Output:
     *         On input:
     *             a timestamp used to fetch older messages
     *             (exclusive).
     *         On output: timestamp of the last message retrieved.
     * @param int &$lastId
     *     Input/Output:
     *         On input:
     *             message ID used to fetch older messages (exclusive).
     *         On output: ID of the last message retrieved.
     * @param bool &$hasMoreMessages
     *     Output: true if more messages exist after this batch.
     * @param int &$selectedCount
     *     Output: total number of messages returned in this batch.
     * @param array &$selectedMessages
     *     Output: list of messages retrieved for the current batch.
     * @param string $action
     *     Optional action-based status filter (see getActionFilter()).
     * @param int $limit
     *     Maximum number of messages per batch (default: 50).
     * @param int $receiverId
     *     Optional filter for receiver ID.
     * @param int $senderId
     *     Optional filter for sender ID.
     *
     * @return bool
     *     True if one or more messages were retrieved;
     *     false if none or invalid input.
     */
    final public function batchSelectMessagesByBooking(
        string $bookingNo,
        string &$lastCreatedAt,
        int &$lastId,
        bool &$hasMoreMessages,
        int &$selectedCount,
        array &$selectedMessages,
        string $action = 'none',
        bool $forceBookingNo = true,
        int $limit = 50,
        int $receiverId = 0,
        int $senderId = 0
    ): bool {
        // Return false if booking no. is empty
        $bookingNo = trim($bookingNo);
        $min = $forceBookingNo ? 1 : 0;
        $type = 'varCharMySql';
        if (!$this->validDTypeStr($bookingNo, $type, 250, $min, '', $error)) {
            $hasMoreMessages = false;
            $selectedCount = 0;
            $selectedMessages = [];

            return false;
        }

        // Add condition to filter messages by booking no.
        if ($bookingNo !== '') {
            $where = ['bookingNo' => $bookingNo];
        }

        // Add condition to filter by receiver ID if provided
        if ($receiverId > 0) {
            $where['receiverId'] = $receiverId;
        }

        // Add condition to filter by sender ID if provided
        if ($senderId > 0) {
            $where['senderId'] = $senderId;
        }

        $this->setAction($action);

        // Return false if the action filter is invalid
        if (($actionFilter = $this->getActionFilter()) === null) {
            $hasMoreMessages = false;
            $selectedCount = 0;
            $selectedMessages = [];

            return false;
        }

        // Merge the action-based filters
        $where += $actionFilter;

        // Reset query builder and prepare base query
        $this->db->reset_query();
        $this->db->from(MED_MESSAGE)->where($where);

        // Apply filter by creation timestamp if provided
        if (trim($lastCreatedAt) !== '') {
            $this->db->where('dateAdded <', trim($lastCreatedAt));
        }

        // Apply filter by ID if provided
        if ($lastId > 0) {
            $this->db->where('id <', $lastId);
        }

        // Order direction depends on pagination context
        $this->db->order_by(
            'dateAdded',
            ($lastCreatedAt !== '' || $lastId > 0) ? 'DESC' : 'ASC'
        );

        /*
         * Request one extra row to check if more messages exist
         * The extra row will be removed after fetch
         * (extra requested messages are removed by $limit + 1)
         */
        $this->db->limit($limit + 1);

        // Execute the query
        $query = $this->db->get();

        // Return false if no messages found
        if (!$query || $query->num_rows() < 1) {
            $hasMoreMessages = false;
            $selectedCount = 0;
            $selectedMessages = [];

            return false;
        }

        // Store result set
        $results = $query->result_array();

        // Count actual results
        $selectedCount = count($results);

        if ($selectedCount > $limit) {
            // Indicate that more messages exist
            $hasMoreMessages = true;

            // Remove the extra message used for overflow check
            array_pop($results);

            // Adjust count after pop
            $selectedCount--;
        }

        // Assign filtered results to output
        $selectedMessages = $results;

        // Track the last ID and timestamp for pagination
        $last = end($results);
        $lastCreatedAt = $last['dateAdded'] ?? '';
        $lastId = $last['id'] ?? 0;

        return true;
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
    final public function updateMessage(
        string $id,
        string $archived = 'none',
        string $read = 'none',
        ?string $adminId = null,
        ?string $bookingNo = null,
        ?string $currencyCode = null,
        ?string $message = null,
        ?bool $point = null,
        ?string $productId = null,
        ?string $receiverId = null,
        ?string $senderId = null,
        ?bool $starred = null,
        ?string $status = null,
        ?string $subject = null,
        ?int &$error = null
    ): bool {
        // Trimming strings and normalize some strings
        $currencyCode = $currencyCode !== null
            ? strtoupper(trim($currencyCode))
            : null;
        $status = $status !== null
            ? ucfirst(strtolower(trim($status)))
            : null;
        foreach (['bookingNo', 'message', 'subject'] as $var) {
            $$var = $$var !== null ? trim($$var) : null;
        }

        foreach (
            [
                ['lossyIdBigInt', $adminId],
                ['lossyIdBigInt', $id],
                ['lossyIdBigInt', $productId],
                ['lossyUnsignedBigInt', $receiverId],
                ['lossyIdBigInt', $senderId],
            ] as [$type, $val]
        ) {
            if (
                $val !== null
                && !$this->validDTypeInt($val, $type, null, null, '', $error)
            ) {
                return false;
            }
        }

        if (
            $message !== null
            && !$this->validDTypeBlob($message, 'blob', 1000, 1, $error)
        ) {
            return false;
        }

        foreach (
            [
                [250, 1, 'varCharMySql', $bookingNo],
                [250, 1, 'varCharMySql', $subject],
                [10, 2, 'varCharMySql', $currencyCode]
            ] as [$max, $min, $typ, $val]
        ) {
            if (
                $val !== null
                && !$this->validDTypeStr($val, $typ, $max, $min, '', $error)
            ) {
                return false;
            }
        }

        if (
            $status !== null
            && !in_array($status, ['Pending', 'Accept', 'Decline'], true)
        ) {
            $error = $this->logError(PwChatModelErrorEnum::STR_ENUM_INVALID);

            return false;
        }

        // Current message query
        $query = $this->db
            ->get_where(MED_MESSAGE, ['id' => $id], 1);

        if (!$query || $query->num_rows() < 1) {
            $error = $this->logError(PwChatModelErrorEnum::DB_DATA_INVALID);

            return false;
        }

        $original = $query->row_array();
        $update = [];

        foreach (
            [
                // Ordened by database table
                ['productId', $productId],
                ['bookingNo', $bookingNo],
                ['senderId', $senderId],
                ['receiverId', $receiverId],
                ['admin_id', $adminId],
                ['subject', $subject],
                ['message', $message]
            ] as [$key, $value]
        ) {
            if (
                $value !== null
                && $value !== $original[$key]
            ) {
                $update[$key] = $value;
            }
        }

        // Set msg_read as separated and sorted by database order
        if (
            in_array($read, [
                'read',
                'readByHost',
                'readByUser',
                'unread',
                'unreadByUser',
                'unreadByHost'
            ], true)
        ) {
            $this->setAction($read);

            $actionFilter = $this->getActionFilter();

            if (
                isset($actionFilter['msg_read'])
                && $actionFilter['msg_read'] !== $original['msg_read']
            ) {
                $update['msg_read'] = $actionFilter['msg_read'];
            }

            // Clear the action
            $actionFilter = [];
        }

        $pointVal = $point !== null
            ? ($point ? '1' : '0')
            : null;
        $starredVal = $starred !== null
            ? ($starred ? 'Yes' : 'No')
            : null;

        foreach (
            [
                // Ordened by database table
                ['currencyCode', $currencyCode],
                ['status', $status],
                ['point', $pointVal]
            ] as [$key, $value]
        ) {
            if (
                $value !== null
                && $value !== $original[$key]
            ) {
                $update[$key] = $value;
            }
        }

        if (
            in_array($archived, [
                'archive',
                'archiveByHost',
                'archiveByUser',
                'unarchive',
                'unarchiveByUser',
                'unarchiveByHost'
            ], true)
        ) {
            $this->setAction($archived);

            $actionFilter = $this->getActionFilter();

            if (
                isset($actionFilter['user_archive_status'])
                && in_array($archived, [
                     'archive',
                     'archiveByUser',
                     'unarchive',
                     'unarchiveByUser'
                ], true)
                && $actionFilter['user_archive_status']
                !== $original['user_archive_status']
            ) {
                $update['user_archive_status'] =
                    $actionFilter['user_archive_status'];
            }

            if (
                isset($actionFilter['host_archive_status'])
                && in_array($archived, [
                     'archive',
                     'archiveByHost',
                     'unarchive',
                     'unarchiveByHost'
                ], true)
                && $actionFilter['host_archive_status']
                !== $original['host_archive_status']
            ) {
                $update['host_archive_status'] =
                    $actionFilter['host_archive_status'];
            }

            // Clear the action
            $actionFilter = [];
        }

        // The msg_read is already set before
        if (
            in_array($read, [
                'read',
                'readByHost',
                'readByUser',
                'unread',
                'unreadByUser',
                'unreadByHost'
            ], true)
        ) {
            $this->setAction($read);

            $actionFilter = $this->getActionFilter();

            if (
                isset($actionFilter['user_msgread_status'])
                && in_array($archived, [
                    'read',
                    'readByUser',
                    'read',
                    'readByUser'
                ], true)
                && $actionFilter['user_msgread_status']
                !== $original['user_msgread_status']
            ) {
                $update['user_msgread_status'] =
                    $actionFilter['user_msgread_status'];
            }

            if (
                isset($actionFilter['host_msgread_status'])
                && in_array($archived, [
                     'read',
                     'readByHost',
                     'unread',
                     'unreadByHost'
                ], true)
                && $actionFilter['host_msgread_status']
                !== $original['host_msgread_status']
            ) {
                $update['host_archive_status'] =
                    $actionFilter['host_archive_status'];
            }

            // clear the action
            $actionFilter = [];
        }

        $starredVal = $starred !== null
            ? ($starred ? 'Yes' : 'No')
            : null;

        foreach (
            [
                // Ordened by database table
                ['currencyCode', $currencyCode],
                ['msg_star_status', $starredVal]
            ] as [$key, $value]
        ) {
            if (
                $value !== null
                && $value !== $original[$key]
            ) {
                $update[$key] = $value;
            }
        }

        if (in_array($starred, [ 'star', 'unstar' ], true)) {
            $this->setAction($starred);

            $actionFilter = $this->getActionFilter();

            if (
                isset($actionFilter['msg_star_status'])
                && $actionFilter['msg_star_status']
                !== $original['msg_star_status']
            ) {
                $update['msg_star_status'] = $actionFilter['msg_star_status'];
            }

            // Clear the action
            $actionFilter = [];
        }

        if (empty($update)) {
            return false; // Nothing to update
        }

        $this->db->trans_begin();
        $this->db->update(MED_MESSAGE, $update, ['id' => $id]);

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();

            return false;
        }

        $this->db->trans_commit();

        return true;
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
    final public function postAllMessages(array $messages): bool
    {
        if (empty($messages)) {
            return false;
        }

        $this->db->trans_start();
        $this->db->truncate(MED_MESSAGE);

        foreach ($messages as $msg) {
            // Trimming booking No.
            $bookingNo = trim($msg['bookingNo']);

            // Validate booking No. as VARCHAR
            if (
                !$this->validDTypeStr(
                     $msg['bookingNo'],
                     'varCharMySql'
                     250,
                     1,
                     'UTF-8',
                     $error
                )
            ) {
                $this->db->trans_rollback();

                return false;
            }

            // Validate sender ID as BIGINT (string + BCMath)
            if (
                !$this->selectUserId($msg['senderId'], 'user', null, $error)
            ) {
                $this->db->trans_rollback();

                return false;
            }

            // Validate message as BLOB (limited by 1000 bytes)
            if (
                !$this
                    ->validDTypeBlob($msg['message'], 'blob', 1000, 1, $error)
            ) {
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
        string $expectedBookingNo,
        array $messages
    ): bool {
        // Trimming expected booking No.
        $expectedBookingNo = trim($expectedBookingNo);

        // Validate expected booking No. as VARCHAR
        if (
            !$this->validDTypeStr(
                 $expectedBookingNo,
                 'varCharMySql'
                 250,
                 1,
                 'UTF-8',
                 $error
            )
        ) {
            return false;
        }

        if (empty($messages)) {
            return false;
        }

        $this->db->trans_start();
        $this->db->where('bookingNo', $expectedBookingNo)
            ->delete(MED_MESSAGE);

        foreach ($messages as $msg) {
            // Trimming booking No.
            $msg['bookingNo'] = trim($msg['bookingNo']);

            // Validate booking No. as VARCHAR
            if (
                !$this->validDTypeStr(
                     $msg['bookingNo'],
                     'varCharMySql'
                     250,
                     1,
                     'UTF-8',
                     $error
                ) || $msg['bookingNo'] !== $expectedBookingNo
            ) {
                $this->db->trans_rollback();

                return false;
            }

            // Validate sender ID as BIGINT (string + BCMath)
            if (
                !$this->selectUserId($msg['senderId'], 'user', null, $error)
            ) {
                $this->db->trans_rollback();

                return false;
            }

            // Validate message as BLOB (limited by 1000 bytes)
            if (
                !$this
                    ->validDTypeBlob($msg['message'], 'blob', 1000, 1, $error)
            ) {
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
     * Gets the current action string.
     *
     * This value determines the filter returned by getActionFilter().
     *
     * @return array
     *     The current action filter array, or an empty array if none.
     */
    final private function getAction(): array
    {
         return $this->action;
    }

    /**
     * Sets the internal action used for filtering message status.
     *
     * The action should correspond to a valid keyword such as:
     * 'archive', 'unreadByHost', 'unstar', etc.
     *
     * @param string $action
     *     The action name to apply.
     *     If invalid, getActionFilter() will return null.
     *
     * @return array
     *     Returns the same action filter as getActionFilter() would return.
     */
    final private function setAction(string $action): array
    {
         $this->action = $action;
    }

    /**
     * Returns SQL WHERE conditions for a given message action.
     *
     * This method maps an action string
     * (such as 'read', 'unarchive', etc.)
     * to a set of key-value pairs representing column filters.
     * These filters are used to select
     * or delete messages with specific states.
     *
     * If the action is empty or 'none',
     * it returns an empty array (no filtering).
     * If the action is invalid or unsupported, it returns null.
     *
     * Supported actions:
     * - 'none' or '': no status filtering
     * - 'archive': messages archived by both user and host
     * - 'archiveByHost':
     *     messages archived by host (`host_archive_status = 'Yes'`)
     * - 'archiveByUser':
     *     messages archived by user (`user_archive_status = 'Yes'`)
     * - 'unarchive': messages unarchived by both user and host
     * - 'unarchiveByHost':
     *     messages unarchived by host (`host_archive_status = 'No'`)
     * - 'unarchiveByUser':
     *     messages unarchived by user (`user_archive_status = 'No'`)
     * - 'point': messages marked with a point (`point = '1'`)
     * - 'unpoint': messages not marked (`point = '0'`)
     * - 'read': messages marked as read (`msg_read = 'Yes'`)
     * - 'readByHost':
     *     messages read by host (`host_msgread_status = 'Yes'`)
     * - 'readByUser':
     *     messages read by user (`user_msgread_status = 'Yes'`)
     * - 'unread': messages not read (`msg_read = 'No'`)
     * - 'unreadByHost':
     *     messages not read by host (`host_msgread_status = 'No'`)
     * - 'unreadByUser':
     *     messages not read by user (`user_msgread_status = 'No'`)
     * - 'star': messages starred (`msg_star_status = 'Yes'`)
     * - 'unstar': messages unstarred (`msg_star_status = 'No'`)
     *
     * @param string $action
     *     A predefined keyword representing a message state filter.
     *     Examples: 'read', 'star', 'unarchiveByUser', etc.
     *
     * @return array|null
     *     An associative array of field conditions,
     *     if the action is valid;
     *     empty array if no filtering is needed;
     *     null if the action is invalid.
     */
    final private function getActionFilter(): ?array
    {
        switch ($this->action) {
            case 'archive':
                return [
                    'host_archive_status' => 'Yes',
                    'user_archive_status' => 'Yes'
                ];
            case 'archiveByHost':
                return ['host_archive_status' => 'Yes'];
            case 'archiveByUser':
                return ['user_archive_status' => 'Yes'];
            case 'point':
                return ['point' => '1'];
            case 'read':
                return ['msg_read' => 'Yes'];
            case 'readByHost':
                return ['host_msgread_status' => 'Yes'];
            case 'readByUser':
                return ['user_msgread_status' => 'Yes'];
            case 'star':
                return ['msg_star_status' => 'Yes'];
            case 'unarchive':
                return [
                    'user_archive_status' => 'No',
                    'host_archive_status' => 'No'
                ];
            case 'unarchiveByHost':
                return ['host_archive_status' => 'No'];
            case 'unarchiveByUser':
                return ['user_archive_status' => 'No'];
            case 'unpoint':
                return ['point' => '0'];
            case 'unread':
                return ['msg_read' => 'No'];
            case 'unreadByHost':
                return ['host_msgread_status' => 'No'];
            case 'unreadByUser':
                return ['user_msgread_status' => 'No'];
            case 'unstar':
                return ['msg_star_status' => 'No'];
            case '':
            case 'none':
                return [];
            default:
                return null; // Invalid action
        }
    }

    /**
     * Determines whether the main message read status ('msg_read')
     * should be set to 'No'.
     *
     * This is used when handling partial unread actions
     * (unreadByUser, unreadByHost).
     * It checks the value of the *other* read-related field
     * (example: if unreading as user,
     * it checks 'host_msgread_status'),
     * and only returns true if both sides are now 'No'.
     *
     * This helps enforce consistent behavior,
     * where 'msg_read' is set to 'No'
     * only if both 'user_msgread_status'
     * and 'host_msgread_status' are 'No'.
     *
     * @param int $messageId
     *     The ID of the message to check.
     * @param string $otherField
     *     The name of the other read-related field to check.
     *     Must be either 'host_msgread_status'
     *     or 'user_msgread_status'.
     *
     * @return bool
     *     True if the other field is explicitly 'No'
     *     (indicating both are now unread);
     *     false otherwise.
     */
    final private function shouldSetMsgReadToNo(
        int $messageId,
        string $otherField
    ): bool {
        if (
            !in_array($otherField, [
                'host_msgread_status',
                'user_msgread_status'
            ], true)
        ) {
            return false; // Invalid field
        }

        // Reset query builder
        $this->db->reset_query();

        $query = $this->db
            ->select($otherField)
            ->from(MED_MESSAGE)
            ->where('id', $messageId)
            ->get();

        if (!$query || $query->num_rows() === 0) {
            return false; // Fallback: don't alter msg_read
        }

        return $query->row($otherField) === 'No';
    }

    /**
     * Stores the current message-related action keyword.
     *
     * Accepted values are:
     * - 'archive', 'archiveByHost', 'archiveByUser'
     * - 'unarchive', 'unarchiveByHost', 'unarchiveByUser'
     * - 'read', 'readByHost', 'readByUser',
     *     'unread', 'unreadByHost', 'unreadByUser'
     * - 'star', 'unstar', 'point', 'unpoint'
     * - 'none' or '' (for no-op)
     *
     * Used internally by getActionFilter() to generate filter arrays.
     *
     * @var string
     */
    private array $action = [];
}
