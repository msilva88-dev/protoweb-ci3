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

//namespace ProtoWeb\CodeIgniter3\Models;

//use CodeIgniter\Model;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PwChatModel
 *
 * This model manages all interactions with the `fc_med_message` table,
 * including message creation, updates, retrieval, and bulk operations.
 *
 * PHP version 7.4+ and CodeIgniter version 3.1.13+
 *
 * @author Marcio Delgado <marcio@libreware.info>
 * @copyright 2025 Marcio Delgado
 * @extends CI_Model
 * @license BSD-2-Clause
 * @package ProtoWeb\CodeIgniter3\Models
 * @since 2025
 * @subpackage Server
 * @version 1.0
 */
final class PwChatModel extends CI_Model
{
    /**
     * Error code: No error occurred.
     *
     * Indicates successful execution of a validation or operation.
     * This is the default state for any error output parameter.
     *
     * @var int
     */
    public const ERR_NONE = 0;

    /**
     * Error code: An unknown or unclassified error occurred.
     *
     * This value is used when no specific error category is applicable
     * or the error is unexpected.
     *
     * @var int
     */
    public const ERR_UNKNOWN = 1;

    /**
     * Error code:
     *     The provided data type is not supported by the validator.
     *
     * Common when the `$type` argument is unrecognized
     * or intentionally falls back to a default `switch` case.
     *
     * @var int
     */
    public const ERR_DTYPE_UNSUPPORTED = 10;

    /**
     * Error code:
     *     The maximum length or size is smaller than the minimum.
     *
     * Indicates a misconfiguration in validation bounds logic.
     *
     * @var int
     */
    public const ERR_DTYPE_BOUNDS_INVALID = 11;

    /**
     * Error code:
     *     The specified maximum bound is outside the allowed range.
     *
     * For example, exceeding data type limits
     * or implementation-defined caps.
     *
     * @var int
     */
    public const ERR_DTYPE_BOUND_MAX = 12;

    /**
     * Error code:
     *     The specified minimum bound is outside the allowed range.
     *
     * For example, being below system-defined or logic-defined minimums.
     *
     * @var int
     */
    public const ERR_DTYPE_BOUND_MIN = 13;

    /**
     * Error code:
     *    The actual value or data length is outside
     *    the permitted range.
     *
     * Used in string, BLOB, or numeric validation to flag size violations.
     *
     * @var int
     */
    public const ERR_DTYPE_RANGE = 14;

    /**
     * Error code: Invalid or unrecognized type for integer validation.
     *
     * Happens when `$type` is not compatible with integer checks
     * (example: "decimalMySql" used in float context).
     *
     * @var int
     */
    public const ERR_INT_DTYPE_INVALID = 20;

    /**
     * Error code:
     *     Invalid max size definition or type for integer validation.
     *
     * For example, passing a float or string instead of an int.
     *
     * @var int
     */
    public const ERR_INT_MAXSIZE_DTYPE = 21;

    /**
     * Error code:
     *     Invalid min size definition or type for integer validation.
     *
     * Usually indicates programmer error or logic misplacement.
     *
     * @var int
     */
    public const ERR_INT_MINSIZE_DTYPE = 22;

    /**
     * Error code:
     *     Integer value is not passed as a string,
     *     making it unsafe to validate large numbers
     *     (example: > 2^53).
     *
     * Used to enforce safe validation of big integers via strings.
     *
     * @var int
     */
    public const ERR_INT_NONSTRING = 23;

    /**
     * Error code:
     *     The float value is either infinite (INF)
     *     or not-a-number (NaN).
     *
     * These values are invalid for most strict validations.
     *
     * @var int
     */
    public const ERR_FLOAT_INF_OR_NAN = 30;

    /**
     * Error code: The float has excessive decimal precision.
     *
     * For example, exceeding configured digit limits
     * like 18+ decimals.
     *
     * @var int
     */
    public const ERR_FLOAT_DIGITS = 31;

    /**
     * Error code:
     *     The input string is not encoded in a valid
     *     or expected format.
     *
     * Usually triggered during UTF-8 validation
     * using `mb_check_encoding()`.
     *
     * @var int
     */
    public const ERR_STR_ENCODING = 40;

    /**
     * Error code:
     *     The input string does not match
     *     any allowed enumerated values.
     *
     * For example, not in ['Yes', 'No'] for an ENUM check.
     *
     * @var int
     */
    public const ERR_STR_ENUM_INVALID = 41;

    /**
     * Error code:
     *     The input string does not conform to expected
     *     date/time formats.
     *
     * Often used to validate timestamps or ISO 8601 strings.
     *
     * @var int
     */
    public const ERR_STR_DTIME_FORMAT = 42;

    /**
     * Error code: The database table is invalid or undefined.
     *
     * This may occur when a required table constant (example: USERS)
     * is not defined or incorrectly named.
     *
     * @var int
     */
    public const ERR_DB_TABLE_INVALID = 50;

    /**
     * Error code: Database insert operation failed.
     *
     * This is set when the insert query completes without errors
     * at the SQL level but no rows were affected
     * (example: duplicate or silent failure).
     *
     * @var int
     */
    public const ERR_DB_INSERT_FAILED = 51;

    /**
     * Error code: The user ID was not found in the database.
     *
     * Common in identity resolution, lookup, or validation queries.
     *
     * @var int
     */
    public const ERR_DB_UID_NOT_FOUND = 60;

    /**
     * Error code: More than one record matched the user ID.
     *
     * Indicates a data integrity issue; `id` should be unique.
     *
     * @var int
     */
    public const ERR_DB_UID_DUPLICATE = 61;

    /**
     * Error code: The user ID retrieved from the database is invalid.
     *
     * For example, not a valid BIGINT string or format mismatch.
     *
     * @var int
     */
    public const ERR_DB_UID_INVALID = 62;

    /**
     * Error code:
     *     The user name retrieved from the database is invalid.
     *
     * Used when the result is not a proper string
     * or fails string validation.
     *
     * @var int
     */
    public const ERR_DB_UNAME_INVALID = 70;

    /**
     * Error code: No user was found with the given user name.
     *
     * Indicates a lookup failure
     * or that the name does not exist in the database.
     *
     * @var int
     */
    public const ERR_DB_UNAME_NOT_FOUND = 71;

    /**
     * Error code: More than one user has the same user name.
     *
     * Indicates a uniqueness violation; `user_name` should be unique.
     *
     * @var int
     */
    public const ERR_DB_UNAME_DUPLICATE = 72;

    /**
     * Maximum value for an 8-bit signed binary data (2^7 - 1).
     *
     * 8-bit signed binary data max value (127).
     * (example: 7 bits integer, 127 bytes string).
     *
     * @var int
     */
    private const MAX_BIN8 = (2 ** 7) - 1;

    /**
     * Maximum value for a 11-bit signed binary data (2^10 - 1).
     *
     * 11-bit signed binary data max value (1,023).
     * (example: 1.25 byte integer, 1 KiB string).
     *
     * @var int
     */
    private const MAX_BIN11 = (2 ** 10) - 1;

    /**
     * Maximum value for a 16-bit signed binary data (2^15 - 1).
     *
     * 16-bit signed binary data max value (32,767).
     * (example: 1.875 byte integer, 32 KiB string).
     *
     * @var int
     */
    private const MAX_BIN16 = (2 ** 15) - 1;

    /**
     * Maximum value for a 24-bit signed binary data (2^23 - 1).
     *
     * 24-bit signed binary data max value (8,388,607).
     * (example: 2.875 bytes integer, 8 MiB string).
     *
     * @var int
     */
    private const MAX_BIN24 = (2 ** 23) - 1;

    /**
     * Maximum value for a 32-bit signed binary data (2^31 - 1).
     *
     * 32-bit signed binary data max value (2,147,483,647).
     * (example: 3.875 bytes integer, 2 GiB string).
     *
     * @var int
     */
    private const MAX_BIN32 = (2 ** 31) - 1;

    /**
     * Maximum value for a 53-bit signed binary data (2^52 - 1).
     *
     * 53-bit signed binary data max value (4,503,599,627,370,495).
     * (example: 6.5 bytes integer, 8 PiB string).
     *
     * @var int
     */
    private const MAX_BIN53 = (2 ** 52) - 1;

    /**
     * Maximum value for a 64-bit signed binary data (2^63 - 1).
     *
     * 64-bit signed binary data max value (9,223,372,036,854,775,807).
     * (example: 7.875 bytes integer, 8 EiB string).
     *
     * @var int
     */
    private const MAX_BIN64 = (2 ** 63) - 1;

    /**
     * Maximum value for a 64-bit signed binary data (2^63 - 1)
     * as string.
     *
     * Useful for BCMath comparisons on platforms
     * without 64-bit int support.
     *
     * 64-bit signed binary data max value (9,223,372,036,854,775,807).
     * (example: 7.875 bytes integer, 8 EiB string).
     *
     * @var int
     */
    private const MAX_BIN64_STR = '9223372036854775807';

    /**
     * Maximum value for an 1-bit unsigned binary data (2^1 - 1).
     *
     * 1-bit unsigned binary data max value (1).
     * (example: 1 bit integer, 1 byte string).
     *
     * @var int
     */
    private const MAX_UBIN1 = (2 ** 1) - 1;

    /**
     * Maximum value for an 6-bit unsigned binary data (2^6 - 1).
     *
     * 6-bit unsigned binary data max value (63).
     * (example: 6 bits integer, 63 bytes string).
     *
     * @var int
     */
    private const MAX_UBIN6 = (2 ** 6) - 1;

    /**
     * Maximum value for an 8-bit unsigned binary data (2^8 - 1).
     *
     * 8-bit unsigned binary data max value (255).
     * (example: 1 byte integer, 255 bytes string).
     *
     * @var int
     */
    private const MAX_UBIN8 = (2 ** 8) - 1;

    /**
     * Maximum value for an 10-bit unsigned binary data (2^10 - 1).
     *
     * 10-bit unsigned binary data max value (1023).
     * (example: 1.25 byte integer, 1023 bytes string).
     *
     * @var int
     */
    private const MAX_UBIN10 = (2 ** 10) - 1;

    /**
     * Maximum value for a 14-bit unsigned binary data (2^14 - 1).
     *
     * 14-bit unsigned binary data max value (16,383).
     * (example: 1.75 byte integer, 16 KiB string).
     *
     * @var int
     */
    private const MAX_UBIN14 = (2 ** 14) - 1;

    /**
     * Maximum value for a 16-bit unsigned binary data (2^16 - 1).
     *
     * 16-bit unsigned binary data max value (65,535).
     * (example: 2 bytes integer, 64 KiB string).
     *
     * @var int
     */
    private const MAX_UBIN16 = (2 ** 16) - 1;

    /**
     * Maximum value for a 20-bit unsigned binary data (2^20 - 1).
     *
     * 20-bit unsigned binary data max value (1,048,575).
     * (example: 2.5 bytes integer, 1 MiB string).
     *
     * @var int
     */
    private const MAX_UBIN20 = (2 ** 20) - 1;

    /**
     * Maximum value for a 24-bit unsigned binary data (2^24 - 1).
     *
     * 24-bit unsigned binary data max value (16,777,215).
     * (example: 3 bytes integer, 16 MiB string).
     *
     * @var int
     */
    private const MAX_UBIN24 = (2 ** 24) - 1;

    /**
     * Maximum value for a 30-bit unsigned binary data (2^30 - 1).
     *
     * 30-bit unsigned binary data max value (1,073,741,823).
     * (example: 3.75 bytes integer, 1 GiB string).
     *
     * @var int
     */
    private const MAX_UBIN30 = (2 ** 30) - 1;

    /**
     * Maximum value for a 31-bit unsigned binary data (2^31 - 1).
     *
     * 31-bit unsigned binary data max value (2,147,483,647).
     * (example: 3.875 bytes integer, 2 GiB string).
     *
     * @var int
     */
    private const MAX_UBIN31 = (2 ** 31) - 1;

    /**
     * Maximum value for a 32-bit unsigned binary data (2^32 - 1).
     *
     * 32-bit unsigned binary data max value (4,294,967,295).
     * (example: 4 bytes integer, 4 GiB string).
     *
     * @var int
     */
    private const MAX_UBIN32 = (2 ** 32) - 1;

    /**
     * Maximum value for a 64-bit unsigned binary data (2^64 - 1).
     *
     * 64-bit unsigned binary data max value
     * (18,446,744,073,709,551,615).
     * (example: 8 bytes integer, 16 EiB string).
     *
     * @var int
     */
    private const MAX_UBIN64 = (2 ** 64) - 1;

    /**
     * Maximum value for a 64-bit unsigned binary data (2^64 - 1)
     * as string.
     *
     * Useful for BCMath comparisons on platforms
     * without 64-bit int support.
     *
     * 64-bit unsigned binary data max value
     * (18,446,744,073,709,551,615).
     * (example: 8 bytes integer, 16 EiB string).
     *
     * @var int
     */
    private const MAX_UBIN64_STR = '18446744073709551615';

    /**
     * Minimum value for an 8-bit signed binary data (-2^7).
     *
     * 8-bit signed binary data min value (-128).
     * (example: -7 bits integer, -128 bytes string).
     *
     * @var int
     */
    private const MIN_BIN8 = (2 ** 7) - 1;

    /**
     * Minimum value for a 11-bit signed binary data (-2^10).
     *
     * 11-bit signed binary data min value (-1,024).
     * (example: -1.25 byte integer, -1 KiB string).
     *
     * @var int
     */
    private const MIN_BIN11 = -(2 ** 10);

    /**
     * Minimum value for a 16-bit signed binary data (-2^15).
     *
     * 16-bit signed binary data min value (-32,768).
     * (example: -1.875 byte integer, -32 KiB string).
     *
     * @var int
     */
    private const MIN_BIN16 = -(2 ** 15);

    /**
     * Minimum value for a 24-bit signed binary data (-2^23).
     *
     * 24-bit signed binary data min value (-8,388,608).
     * (example: -2.875 bytes integer, -8 MiB string).
     *
     * @var int
     */
    private const MIN_BIN24 = -(2 ** 23);

    /**
     * Minimum value for a 32-bit signed binary data (-2^31).
     *
     * 32-bit signed binary data min value (-2,147,483,648).
     * (example: -3.875 bytes integer, -2 GiB string).
     *
     * @var int
     */
    private const MIN_BIN32 = -(2 ** 31);

    /**
     * Minimum value for a 53-bit signed binary data (-2^52).
     *
     * 53-bit signed binary data min value (-4,503,599,627,370,496).
     * (example: -6.5 bytes integer, -8 PiB string).
     *
     * @var int
     */
    private const MIN_BIN53 = -(2 ** 52);

    /**
     * Minimum value for a 64-bit signed binary data (-2^63).
     *
     * 64-bit signed binary data min value
     * (-9,223,372,036,854,775,808).
     * (example: -7.875 bytes integer, -8 EiB string).
     *
     * @var int
     */
    private const MIN_BIN64 = -(2 ** 63);

    /**
     * Minimum value for a 64-bit signed binary data (-2^63)
     * as string.
     *
     * Useful for BCMath comparisons on platforms
     * without 64-bit int support.
     *
     * 64-bit signed binary data min value
     * (-9,223,372,036,854,775,808).
     * (example: -7.875 bytes integer, -8 EiB string).
     *
     * @var int
     */
    private const MIN_BIN64_STR = '-9223372036854775808';

    /**
     * Minimum value for an unsigned data (0).
     *
     * unsigned data min value (0).
     * (example: 0 bit integer, 0 byte string).
     *
     * @var int
     */
    private const MIN_U = 0;

    /**
     * Deletes a single message by its ID,
     * optionally returning the deleted message data.
     *
     * The message is retrieved (if requested) before deletion
     * and the operation is wrapped in a database transaction.
     * If the message does not exist or no rows are affected,
     * the transaction is rolled back
     * and the reference result is cleared.
     *
     * @param int $message_id
     *     The ID of the message to delete.
     *     Must be greater than zero.
     * @param array|null &$deletedMessage
     *     Optional output:
     *         will contain the deleted message data
     *         (as an associative array),
     *         or null if the deletion fails
     *         or the message doesn't exist.
     *
     * @return bool
     *     True if the message was successfully deleted;
     *     false otherwise.
     */
    final public function deleteMessage(
        int $message_id,
        ?array &$deletedMessage = null
    ): bool {
        if ($message_id < 1) {
            return false;
        }

        // Fetch message before deleting if requested
        if ($deletedMessage !== null) {
            $query = $this->db
                ->get_where(MED_MESSAGE, ['id' => $message_id]);

            $deletedMessage = ($query && $query->num_rows() > 0)
                ? $query->row_array()
                : null;
        }

        $this->db->trans_begin();
        $this->db->delete(MED_MESSAGE, ['id' => $message_id]);

        if ($this->db->affected_rows() < 1) {
            $this->db->trans_rollback();

            if ($deletedMessage !== null) {
                $deletedMessage = null;
            }

            return false;
        }

        $this->db->trans_commit();

        return true;
    }

    /**
     * Deletes messages for a given booking number,
     * optionally filtered by action, sender, and receiver.
     *
     * This method uses paginated batch selection
     * to support high-volume deletion without memory exhaustion.
     * Messages are fetched in batches
     * using `batchSelectMessagesByBooking()`
     * and removed within database transactions to ensure consistency.
     *
     * If references are provided, the method outputs the count
     * and content of deleted messages.
     *
     * @param string $bookingNo
     *     Booking number identifying the message group to delete from.
     *     Must be non-empty.
     * @param string $action
     *     Optional message status filter (example: 'read', 'archive').
     *     See `getActionFilter()` for supported values.
     * @param int $limit
     *     Optional batch size for paginated deletion (default: 50).
     * @param int $receiverId
     *     Optional receiver filter (only messages to this user).
     * @param int $senderId
     *     Optional sender filter (only messages from this user).
     * @param int|null &$deletedCount
     *     Output:
     *         total number of messages deleted,
     *         or null if not requested.
     * @param array|null &$deletedMessages
     *     Output:
     *         array of deleted message records,
     *         or null if not requested.
     *
     * @return bool
     *     True if one or more messages were successfully deleted;
     *     false if no matching messages or if a deletion failed.
     */
    final public function deleteMessagesByBooking(
        string $bookingNo,
        string $action = 'none',
        int $limit = 50,
        int $receiverId = 0,
        int $senderId = 0,
        ?int &$deletedCount = null, // Output
        ?array &$deletedMessages = null // Output
    ): bool {
        $allDeletedCount = 0;
        $allDeletedMessages = [];
        $lastCreatedAt = '';
        $lastId = 0;

        do {
            $ok = $this->batchSelectMessagesByBooking(
                $bookingNo,
                $lastCreatedAt, // Input/Output
                $lastId, // Input/Output
                $hasMoreMessages, // Output
                $batchCount, // Output
                $batchMessages, // Output
                $action,
                $limit,
                $receiverId,
                $senderId
            );

            if (!$ok || $batchCount < 1) {
                break;
            }

            $idsToDelete = array_column($batchMessages, 'id');

            $this->db->trans_begin();
            $this->db->delete(MED_MESSAGE, ['id' => $idsToDelete]);

            if ($this->db->affected_rows() !== count($idsToDelete)) {
                $this->db->trans_rollback();

                if ($deletedCount !== null) {
                    $deletedCount = 0;
                }

                if ($deletedMessages !== null) {
                    $deletedMessages = null;
                }

                return false;
            }

            $this->db->trans_commit();

            $allDeletedCount += count($idsToDelete);
            $allDeletedMessages = array_merge(
                $allDeletedMessages,
                $batchMessages
            );
        } while ($hasMoreMessages);

        if ($deletedCount !== null) {
            $deletedCount = $allDeletedCount;
        }

        if ($deletedMessages !== null) {
            $deletedMessages = $allDeletedMessages;
        }

        return $allDeletedCount > 0;
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
     *     - self::ERR_NONE
     *     - self::ERR_STR_ENUM_INVALID
     *     - self::ERR_DB_INSERT_FAILED
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
        ?int &$error = null
    ): bool {
        // Validates Sender ID
        if (!$this->selectUserId($senderId, 'user', null, $error)) {
            return false;
        }

        // Validates BIGINT IDs
        foreach (
            [
                [$adminId, 'admin'],
                [$receiverId, 'user']
            ] as [$id, $table]
        ) {
            if (
                $id !== '0' && !$this->selectUserId($id, $table, null, $error)
            ) {
                return false;
            }
        }

        // Trimming strings (and some normalizes)
        $bookingNo = trim($bookingNo);
        $currencyCode = strtoupper(trim($currencyCode));
        $message = trim($message);
        $subject = trim($subject);
        $status = ucfirst(strtolower(trim($status)));

        // Validates TEXTs
        foreach (
            [
                [250, 1, 'varCharMySql', $bookingNo],
                [10, 0, 'varCharMySql', $currencyCode],
                [250, 0, 'varCharMySql', $subject]
            ] as [$max, $min, $typ, $val]
        ) {
            if (!$this->validDTypeStr($val, $typ, $max, $min, '', $error)) {
                return false;
            }
        }

        // Validates message BLOB limited by 1000 bytes
        if (!$this->validDTypeBlob($message, 'blob', 1000, 1, $error)) {
            return false;
        }

        // Validates status ENUM
        if (!in_array($status, ['Pending', 'Accept', 'Decline'], true)) {
            $error = self::ERR_STR_ENUM_INVALID;

            return false;
        }

        $insert = [
            'productId' => (bccomp($productId, '0') > 0) ? $productId : '0',
            'bookingNo' => $bookingNo, // SQL VARCHAR(250)
            'senderId' => $senderId,
            'receiverId' =>
                (bccomp($receiverId, '0') > 0) ? $receiverId : '0',
            'admin_id' => (bccomp($adminId, '0') > 0) ? $adminId : '0',
            'subject' => $subject, // SQL VARCHAR(250)
            'message' => $message, // SQL BLOB (VARCHAR(250))
            'status' => $status, // SQL ENUM
            'point' => $point ? '1' : '0', // SQL ENUM (BOOL)
            'msg_status' => 1, // SQL TINYINT
            'currencycode' => $currencyCode // SQL VARCHAR(10)
        ];

        $this->db->trans_begin();
        $this->db->insert(MED_MESSAGE, $insert);

        if ($this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();

            $error = self::ERR_DB_INSERT_FAILED;

            return false;
        }

        $this->db->trans_commit();

        $error = self::ERR_NONE;

        return true;
    }

    final public function insertUserWithId(
        string $id,
        string $name,
        ?int &$error = null
    ): bool {
        // Validate the UserID
        $type = 'lossyIdBigInt';
        if (!$this->validDTypeInt($id, $type, null, null, null, $error)) {
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

        // Check the UserID in database
        if ($this->db->where('id', $id)->limit(1)->count_all_results(USERS)) {
            $error = self::ERR_DB_UID_DUPLICATE;

            return false;
        }

        // Check the User name in database
        if (
            $this->db
                ->where('LOWER(user_name)', strtolower($name))
                ->limit(1)
                ->count_all_results(USERS)
        ) {
            $error = self::ERR_DB_UNAME_DUPLICATE;

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
            $error = self::ERR_UNKNOWN;

            return false;
        }

        $error = self::ERR_NONE;

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
        int $messageId,
        string $action = 'none',
        int $receiverId = 0
    ): bool {
        if ($messageId < 1) {
            return false;
        }

        $this->db->reset_query();
        $this->db->where('id', $messageId);

        if ($receiverId > 0) {
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
        if ($bookingNo === '' || mb_strlen($bookingNo) > 250) {
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
     *     Output parameter for error status:
     *     - self::ERR_NONE on success
     *     - 'invalid-integer' if ID format is not valid
     *     - self::ERR_DTYPE_RANGE if ID exceeds BIGINT bounds
     *     - self::ERR_DB_UID_NOT_FOUND if no match found
     *     - self::ERR_DB_UID_DUPLICATE if multiple matches found
     *     - self::ERR_DB_UNAME_INVALID if user_name missing or not a string
     *     - 'invalid-string' if user_name failed string validation
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
        if (!$this->validDTypeInt($id, $type, null, null, null, $error)) {
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
                $error = self::ERR_DB_TABLE_INVALID;

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
            $error = self::ERR_DB_UID_NOT_FOUND;

            return false;
        }

        if ($count > 1) {
            $error = self::ERR_DB_UID_DUPLICATE;

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
            $error = self::ERR_DB_UNAME_INVALID;

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

        $error = self::ERR_NONE;

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
     *     Output parameter for error status:
     *     - self::ERR_NONE if successful
     *     - 'invalid-string' if name is not valid
     *     - 'database-notfound' if user not found
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
                $error = self::ERR_DB_TABLE_INVALID;

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
            $error = self::ERR_DB_UNAME_NOT_FOUND;

            return false;
        }

        if ($count > 1) {
            $error = self::ERR_DB_UNAME_DUPLICATE;

            return false;
        }

        /*
         * Check if the user ID exists in the result and is a string.
         * This avoids PHP integer truncation
         * when fetching BIGINT from the database.
         */
        if (!isset($rows[0]['id']) || !is_string($rows[0]['id'])) {
            $error = self::ERR_DB_UID_INVALID;

            return false;
        }

        /*
         * Validate the user ID as a BIGINT-compatible string.
         * This ensures correct range, positive format,
         * and numeric integrity.
         */
        $type = 'lossyIdBigInt';
        $value = $rows[0]['id'];
        if (!$this->validDTypeInt($value, $type, null, null, null, $error)) {
            return false;
        }

        // Assign the valid user ID to the output parameter
        $id = $value;

        $error = self::ERR_NONE;

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
        int $limit = 50,
        int $receiverId = 0,
        int $senderId = 0
    ): bool {
        // Return false if booking no. is empty
        if (!($bookingNo = trim($bookingNo))) {
            $hasMoreMessages = false;
            $selectedCount = 0;
            $selectedMessages = [];

            return false;
        }

        // Add condition to filter messages by booking no.
        $where = ['bookingNo' => $bookingNo];

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
        int $id,
        int $adminId = 0,
        string $archived = 'none',
        string $bookingNo = '',
        string $currencyCode = 'USD',
        string $message = '',
        int $point = -1,
        int $productId = 0,
        string $read = 'none',
        int $receiverId = -1,
        int $senderId = 0,
        string $starred = 'none',
        string $status = 'Pending',
        string $subject = ''
    ): bool {
        if ($id < 1) {
            return false;
        }

        // Current message query
        $query = $this->db
            ->get_where(MED_MESSAGE, ['id' => $id], 1);

        if (!$query || $query->num_rows() < 1) {
            return false;
        }

        $original = $query->row_array();
        $update = [];

        if ($productId > 0 && $productId !== (int)$original['productId']) {
            $update['productId'] = $productId;
        }

        if (
            trim($bookingNo) !== ''
            && $bookingNo !== $original['bookingNo']
        ) {
            $update['bookingNo'] = trim($bookingNo);
        }

        if ($senderId > 0 && $senderId !== (int)$original['senderId']) {
            $update['senderId'] = $senderId;
        }

        // Zero is valid in receiverId (used as unknown receiver)
        if (
            $receiverId > -1 && $receiverId !== (int)$original['receiverId']
        ) {
            $update['receiverId'] = $receiverId;
        }

        if ($adminId > 0 && $adminId !== (int)$original['admin_id']) {
            $update['admin_id'] = $adminId;
        }

        if (trim($subject) !== '' && $subject !== $original['subject']) {
            $update['subject'] = trim($subject);
        }

        if (trim($message) !== '' && $message !== $original['message']) {
            $update['message'] = $message;
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

        $status = ucfirst(strtolower(trim($status)));
        if (
            in_array($status, ['Pending', 'Accept', 'Decline'], true)
            && $status !== $original['status']
        ) {
            $update['status'] = $status;
        }

        // Point is a string enumerator in database
        // Values: '0' is false, '1' is true
        if (
            $point > -1
            && $point < 2
            && (string)$point !== $original['point']
        ) {
            $update['point'] = (string)$point;
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

        $currencyCode = strtoupper(trim($currencyCode));
        if (
            $currencyCode !== ''
            && $currencyCode !== $original['currencycode']
        ) {
            $update['currencycode'] = $currencyCode;
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
            if (!$this->validMsg($bookingNo, $message, $senderId)) {
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
        if (empty($expectedBookingNo) || empty($messages)) {
            return false;
        }

        $this->db->trans_start();
        $this->db->where('bookingNo', $expectedBookingNo)
            ->delete(MED_MESSAGE);

        foreach ($messages as $msg) {
            if (
                !$this->validMsg(
                    $bookingNo,
                    $message,
                    $senderId,
                    $expectedBookingNo
                )
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
     * Validates a binary/blob string
     * against SQL BLOB types and length bounds.
     *
     * This function checks if a binary string (`$data`) conforms
     * to the specified SQL BLOB/BINARY or language-specific type.
     * It validates that the string length (in bytes) falls within
     * the allowed or custom-specified range.
     *
     * Supports validation for MySQL/MariaDB, PostgreSQL, SQLite,
     * and string buffer types in JS, Lua, and PHP.
     *
     * BLOB size limits by type:
     * - 'tinyBlob' — 255 bytes.
     * - 'binary' — 1 to 255 bytes.
     * - 'varBinary' — 1 to 65,533 bytes + 2 bytes overhead.
     * - 'blob' (default) — 65,535 bytes.
     * - 'mediumBlob' — 16,777,215 bytes.
     * - 'blobSqlite', 'bytea', 'stringJs' — ~1 GiB (2³⁰).
     * - 'blobSql', 'stringLua', 'stringPhp' — ~2 GiB (2³¹).
     *
     * Custom `$minLength` and `$maxLength`
     * may override default bounds if valid.
     *
     * @param string $data
     *     Binary string value to validate.
     * @param string $type
     *     SQL BLOB type or language label (default 'blob').
     * @param int $maxLength
     *     Optional upper bound for length (0 = no override).
     * @param int $minLength
     *     Optional lower bound for length (0 = no override).
     * @param int &$error
     *     Output error code if validation fails:
     *     - self::ERR_DTYPE_UNSUPPORTED — Unknown or invalid type.
     *     - self::ERR_DTYPE_BOUNDS_INVALID
     *         — Custom min exceeds custom max.
     *     - self::ERR_DTYPE_BOUND_MAX
     *         — Custom max exceeds allowed type limit.
     *     - self::ERR_DTYPE_BOUND_MIN — Custom min exceeds allowed minimum.
     *     - self::ERR_DTYPE_RANGE — Actual value out of allowed range.
     *     - self::ERR_NONE — No error (value is valid).
     *
     * @return bool
     *     True if data is valid for the specified type and bounds;
     *     false otherwise.
     */
    final private function validDTypeBlob(
        string $data,
        string $type = 'blob',
        int $maxLength = 0,
        int $minLength = 0,
        ?int &$error = null
    ): bool {
        $byteLength = strlen($data);
        $byteMinLength = self::MIN_U;

        switch ($type) {
            // MySQL BINARY
            case 'binary':
                $byteMinLength = self::MAX_UBIN1;

                // Fallthrough
            // MySQL TINYBLOB
            case 'tinyBlob':
                $byteMaxLength = self::MAX_UBIN8;

                break;
            // MySQL VARBINARY
            case 'varBinary':
                // -2 bytes overhead
                $byteMaxLength = self::MAX_UBIN16 - 2;
                $byteMinLength = self::MAX_UBIN1;

                break;
            // MySQL BLOB
            case 'blob':
                $byteMaxLength = self::MAX_UBIN16;

                break;
            // MySQL MEDIUMBLOB
            case 'mediumBlob':
                $byteMaxLength = self::MAX_UBIN24;

                break;
            // PostgreSQL BYTEA
            case 'bytea':
            // SQLite BLOB
            case 'blobSqlite':
            // JavaScript string
            case 'stringJs':
                $byteMaxLength = self::MAX_UBIN30;

                break;
            /*
             * SQL BLOB types can support very large sizes
             * (limited by 2 GiB range).
             */
            case 'blobSql':
            // Lua string
            case 'stringLua':
            // PHP string
            case 'stringPhp':
                $byteMaxLength = self::MAX_UBIN31;

                break;
            // MySQL LONGBLOB (MAX_UBIN32)
            case 'longBlob':
            // Python (Limited by RAM)
            case 'stringPy':
            // Unknown type
            default:
                $error = self::ERR_DTYPE_UNSUPPORTED;

                return false;
        }

        // Check if maximum length is less than minimum length
        if ($maxLength > 0) {
            if ($maxLength < $minLength) {
                $error = self::ERR_DTYPE_BOUNDS_INVALID;

                return false;
            }
        }

        // Optional override of maximum length (within SQL BLOB limits)
        if ($maxLength > 0) {
            if ($maxLength > $byteMaxLength || $maxLength < self::MIN_U) {
                $error = self::ERR_DTYPE_BOUND_MAX;

                return false; // Exceeds BLOB type's capacity
            }

            $byteMaxLength = $maxLength;
        }

        // Optional override of minimum length (within SQL TEXT limits)
        if ($minLength > self::MIN_U) {
            if ($minLength > $byteMinLength || $minLength < self::MIN_U) {
                $error = self::ERR_DTYPE_BOUND_MIN;

                return false; // Exceeds BLOB type's capacity
            }

            $byteMinLength = $minLength;
        }

        // Range check
        if ($byteLength < $byteMinLength || $byteLength > $byteMaxLength) {
            $error = self::ERR_DTYPE_RANGE;

            return false;
        }

        $error = self::ERR_NONE;

        return true;
    }

    /**
     * Validates a floating-point value
     * against type, range, and precision.
     *
     * This method checks whether a given float value is valid
     * according to a specified floating-point type (`$type`),
     * optional minimum/maximum bounds,
     * and optionally restricts to values considered "safe"
     * (finite and with limited decimal precision).
     *
     * Supported `$type` values correspond to common SQL
     * and language-specific float types:
     *
     * - Half-precision (~4 decimal digits):
     *     'half', 'float2', 'float16', 'ieee754Bin16'.
     * - Single-precision (~7 decimal digits):
     *     'float', 'float4', 'float32', 'ieee754Bin32', 'real',
     *     'float32MySql', 'realPgSql'.
     * - Double-precision (~17 decimal digits):
     *     'double', 'float8', 'float64', 'ieee754Bin64', 'realSql',
     *     'doublePgSql', 'realMySql', 'realSqlite', 'numericSqlite',
     *     'floatPhp', 'floatLua', 'numericJs'.
     *
     * Range limits:
     * - Half-precision — safe ±2048.0, unsafe ±65504.0.
     * - Single-precision — safe ±16,777,216.0, unsafe ±3.4028235e+38.
     * - Double-precision —
     *     safe ±9,007,199,254,740,991.0,
     *     unsafe ±1.7976931348623157e+308.
     *
     * If `$safe` is true (default),
     * the value must be finite (not INF or NAN)
     * and respect decimal digit limits per IEEE-754 recommendation.
     *
     * @param float $data
     *     The floating-point value to validate.
     * @param bool $safe
     *     If true (default), the value must be finite
     *     and limited in significant digits.
     * @param string $type
     *     SQL FLOAT type or language label (default 'double').
     * @param float|null $maxSize
     *     Optional upper bound for value (null = no override).
     * @param float|null $minSize
     *     Optional lower bound for length (null = no override).
     * @param int &$error
     *     Error code output if validation fails:
     *     - self::ERR_FLOAT_INF_OR_NAN
     *         — Value is INF or NAN while $safe is true.
     *     - self::ERR_DTYPE_UNSUPPORTED → Unknown or invalid $type.
     *     - self::ERR_DTYPE_BOUNDS_INVALID
     *         — Custom min exceeds custom max.
     *     - self::ERR_DTYPE_BOUND_MAX
     *         — Custom max exceeds allowed type limit.
     *     - self::ERR_DTYPE_BOUND_MIN — Custom min exceeds allowed minimum.
     *     - self::ERR_FLOAT_DIGITS
     *         — Value has more significant digits than safe limit.
     *     - self::ERR_DTYPE_RANGE — Actual value out of allowed range.
     *     - self::ERR_NONE — No error (value is valid).
     *
     * @return bool
     *     True if data is valid for the specified type, bounds,
     *     and (if safe) precision; false otherwise.
     */
    final private function validDTypeFloat(
        float $data,
        bool $safe = true,
        string $type = 'double',
        ?float $maxSize = null,
        ?float $minSize = null,
        ?int &$error = null
    ): bool {
        // Reject NaN and Infinity
        if ($safe && !is_finite($data)) {
            $error = self::ERR_FLOAT_INF_OR_NAN;

            return false;
        }

        switch ($type) {
            case 'float2':
            case 'float16':
            case 'half':
            case 'ieee754Bin16':
                $bitMaxSize = $safe
                    ? self::MAX_BIN11
                    : 65504.0;
                $bitMinSize = $safe
                    ? -self::MAX_BIN11
                    : -65504.0;
                $safeDigits = 4;

                break;
            case 'float':
            case 'float4':
            case 'float32':
            case 'float32MySql':
            case 'ieee754Bin32':
            case 'real':
            case 'realPgSql':
                $bitMaxSize = $safe
                    ? self::MAX_BIN24
                    : 3.4028235e+38;
                $bitMinSize = $safe
                    ? -self::MAX_BIN24
                    : -3.4028235e+38;
                $safeDigits = 7;

                break;
            case 'double':
            case 'doublePgSql':
            case 'float8':
            case 'float64':
            case 'float64MySql':
            case 'floatPhp':
            case 'floatLua':
            case 'floatPy':
            case 'ieee754Bin64':
            case 'numericJs':
            case 'numericSqlite':
            case 'realMySql':
            case 'realSql':
            case 'realSqlite':
                $bitMaxSize = $safe
                    ? self::MAX_BIN53
                    : 1.7976931348623157e+308;
                $bitMinSize = $safe
                    ? -self::MAX_BIN53
                    : -1.7976931348623157e+308;
                $safeDigits = 17;

                break;
            default:
                $error = self::ERR_DTYPE_UNSUPPORTED;

                return false;
        }

        // Check if maximum bound is less than minimum bound
        if ($maxSize !== null && $minSize !== null) {
            if ($maxSize < $minSize) {
                $error = self::ERR_DTYPE_BOUNDS_INVALID;

                return false;
            }
        }

        // Custom maximum bounds
        if ($maxSize !== null) {
            if ($maxSize <= $bitMinSize || $maxSize > $bitMaxSize) {
                $error = self::ERR_DTYPE_BOUND_MAX;

                return false;
            }

            $bitMaxSize = $maxSize;
        }

        // Custom minimum bounds
        if ($minSize !== null) {
            if ($minSize < $bitMinSize || $minSize >= $bitMaxSize) {
                $error = self::ERR_DTYPE_BOUND_MIN;

                return false;
            }

            $bitMinSize = $minSize;
        }

        // Precision check
        if ($safe) {
            $normalized =
                rtrim(rtrim(sprintf('%.99g', $data), '0'), '.');
            $digits = preg_replace('/[^0-9]/', '', $normalized);

            if (strlen($digits) > $safeDigits) {
                $error = self::ERR_FLOAT_DIGITS;

                return false;
            }
        }

        // Range check
        if ($data < $bitMinSize || $data > $bitMaxSize) {
            $error = self::ERR_DTYPE_RANGE;

            return false;
        }

        $error = self::ERR_NONE;

        return true;
    }

    /**
     * Validates an integer or integer-like value
     * against SQL or language-specific types.
     *
     * This function supports validation of numeric values
     * (`int`, `float`, or `string`)
     * against a wide variety of integer-like types used in SQL engines
     * (MySQL, PostgreSQL, SQLite)
     * and programming languages (PHP, JS, Lua, Python).
     * It allows validating bit size, signed/unsigned limits,
     * and custom ranges.
     *
     * Supports detection and enforcement of limits for:
     * - 8/16/24/32/64-bit signed/unsigned integers.
     * - Lossy identifiers (clipped unsigned IDs).
     * - Float-based integer ranges (IEEE 754 binary16/32/64).
     * - SQL engine types (PostgreSQL, MySQL, SQLite).
     *
     * Automatically detects PHP 32-bit vs 64-bit architecture
     * and adjusts behavior accordingly, including fallback to 'float'
     * for oversized values on 32-bit platforms
     * or 'string' if provided.
     *
     * Strings are supported with optional encoding validation
     * (UTF-8, ISO-8859-1, UTF-16),
     * and arbitrary precision comparisons are handled using BCMath.
     *
     * Supported types and value ranges:
     * - 'bool' — 0 (false) to 1 (true).
     * - 'lossyIdTinyInt' — 1 to 127.
     * - 'lossyUnsignedTinyInt' — 0 to 127.
     * - 'unsignedTinyInt' — 0 to 255.
     * - 'tinyInt', 'int1', 'int8' — -128 to 127.
     * - 'half', 'float2', 'float16', 'ieee754Bin16' —
     *     ±2¹¹ (safe range).
     * - 'lossyIdSmallInt', 'smallSerial' — 1 to 32,767.
     * - 'lossyUnsignedSmallInt' — 0 to 32,767.
     * - 'unsignedSmallInt' — 0 to 65,535.
     * - 'smallInt', 'int2', 'int16' — -32,768 to 32,767.
     * - 'lossyIdMediumInt' — 1 to 8,388,607.
     * - 'lossyUnsignedMediumInt' — 0 to 8,388,607.
     * - 'unsignedMediumInt' — 0 to 16,777,215.
     * - 'mediumInt', 'int3', 'int24' — -8,388,608 to 8,388,607.
     * - 'float', 'float4', 'float32', 'float32MySql', 'ieee754Bin32',
     *   'real', 'realPgSql' —
     *     ±2²⁴ (safe range).
     * - 'lossyIdInt', 'serial' — 1 to 2,147,483,647.
     * - 'lossyUnsignedInt' — 0 to 2,147,483,647.
     * - 'unsignedInt' — 0 to 4,294,967,295.
     * - 'int' (default), 'integer', 'int4', 'int32',
     *   'int32Lua', 'int32Php' —
     *     -2,147,483,648 to 2,147,483,647.
     * - 'intSql', 'intPhp' — up to PHP_INT_MAX (varies by arch).
     * - 'double', 'doublePgSql', 'float8', 'float64', 'float64MySql',
     *   'floatLua', 'floatPhp', 'floatPy', 'ieee754Bin64',
     *   'realMySql', 'realSqlite', 'numericJs', 'numericRealSqlite' —
     *     ±2⁵³ (safe range).
     *
     * Supported types and value ranges on BCMath+string or PHP 64-bit:
     * - 'bigInt', 'bigIntJs', 'int8b', 'int64',
     *   'int64Lua', 'int64Php', 'integerSqlite', 'numericIntSqlite' —
     *     -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807
     * - 'moneyPgSql' (stored as signed 64-bit integer × 100) —
     *     Integer range:
     *         -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807.
     *     Real-world range:
     *         -92,233,720,368,547,758.08 to 92,233,720,368,547,758.07.
     *     Two implicit decimal digits (fixed-point, not float).
     * - 'lossyIdBigInt', 'bigSerial' — 1 to 9,223,372,036,854,775,807.
     * - 'lossyUnsignedBigInt' — 0 to 9,223,372,036,854,775,807.
     *
     * On all platforms (BCMath-based strings only):
     * - 'unsignedBigInt', 'bitMySql' (1-bit to 64-bit) —
     *     0 to 18,446,744,073,709,551,615.
     * - 'decimalMySql', 'numericMySql' — 1 to 65 digits (65 bytes).
     * - 'charMySql', 'enumMySql', 'varCharLegacy' —
     *     1 to 255 characters (1 byte per character; 255 bytes).
     * - 'tinyText' — up to 255 characters (255 bytes).
     * - 'decimalPgSql', 'numericPgSql' — 1 to 16,383 digits (16 KiB).
     * - 'varCharMySql' — 1 to 65,533 characters (64 KiB - 2 bytes).
     * - 'text' — up to 65,535 characters (64 KiB).
     * - 'charPgSql', 'varCharPgSql' —
     *     1 to 1,047,552 characters (999 KiB).
     * - 'bitPgSql' (1-bit to 10⁷-bit),
     *   'varBitPgSql' (by 1-bit to 10⁷-bit) —
     *     0 to ~9.54 MiB (number).
     * - 'mediumText' — limited by 16 MiB string.
     * - 'textPgSql', 'textSqlite', 'enumPgSql', 'stringJs' —
     *     limited by 1 GiB string.
     * - 'textSql', 'stringLua', 'stringPhp' — limited by 2 GiB string.
     *
     * Optional range overrides '$minSize' and '$maxSize'
     * must match the native type:
     * - int/float for numeric values.
     * - string for BCMath-based strings.
     *
     * @param int|float|string $data
     *     Integer value to validate
     *     (supports large integers via string).
     * @param string $type
     *     SQL INT type or language label (default 'int').
     * @param int|float|string|null $maxSize
     *     Optional upper bound for value (null = no override).
     *     Type must match $data (example: string for string input).
     * @param int|float|string|null $minSize
     *     Optional lower bound for value (null = no override).
     *     Type must match $data (example: string for string input).
     * @param string $encoding
     *     Character encoding used (byte length strings only):
     *     - Defaults to 'UTF-8'.
     *     - Automatically set to 'UTF-16' if $type is 'stringJs'.
     *     - Automatically set to 'ISO-8859-1'
     *         if $type is 'varCharLegacy'.
     * @param int &$error
     *     Output error code if validation fails:
     *     - self::ERR_INT_MAXSIZE_DTYPE — Invalid type for custom max.
     *     - self::ERR_INT_MINSIZE_DTYPE — Invalid type for custom min.
     *     - self::ERR_INT_DTYPE_INVALID — Invalid type for data.
     *     - self::ERR_DTYPE_UNSUPPORTED — Unknown or invalid type.
     *     - self::ERR_STR_ENCODING — The encoding is invalid.
     *     - self::ERR_INT_NONSTRING — input requires string+BCMath.
     *     - self::ERR_DTYPE_BOUNDS_INVALID
     *         — Custom min exceeds custom max.
     *     - self::ERR_DTYPE_BOUND_MAX
     *         — Custom max exceeds allowed type limit.
     *     - self::ERR_DTYPE_BOUND_MIN — Custom min exceeds allowed minimum.
     *     - self::ERR_DTYPE_RANGE — Actual value out of allowed range.
     *     - self::ERR_NONE — No error (value is valid).
     * @param string &$phpType
     *     Output PHP type as determined
     *     ('int', 'float', 'string', or 'unsupported').
     *
     * @return bool
     *     True if data is valid for the specified type and bounds;
     *     false otherwise.
     */
    final private function validDTypeInt(
        $data,
        string $type = 'int',
        $maxSize = null,
        $minSize = null,
        ?string $encoding = null,
        ?int &$error = null,
        ?string &$phpType = null
    ): bool {
        // Set data size for non-arbitrary integer value
        $dataSize = $data;

        // Validate input type and detect PHP type
        if (
            is_int($data)
            || (is_float($data) && $data === (float)(int)$data)
        ) {
            $phpType = 'int';

            if (is_float($data)) {
                // Float with no fractional part (example: 123.0)
                $phpType = 'float';
            }

            if (
                $maxSize !== null && (
                    (!is_int($maxSize) && !is_float($maxSize))
                    || (
                        is_float($maxSize)
                        && $maxSize !== (float)(int)$maxSize
                    )
                )
            ) {
                $error = self::ERR_INT_MAXSIZE_DTYPE;

                return false;
            }

            if (
                $minSize !== null && (
                    (!is_int($minSize) && !is_float($minSize))
                    || (
                        is_float($minSize)
                        && $minSize !== (float)(int)$minSize
                    )
                )
            ) {
                $error = self::ERR_INT_MINSIZE_DTYPE;

                return false;
            }
        } elseif (is_numeric($data) && preg_match('/^-?\d+$/', $data)) {
            // Numeric string, but must be integer (no decimal point)
            $phpType = 'string';

            if (
                $maxSize !== null
                && (
                    !is_string($maxSize)
                    || !preg_match('/^-?\d+$/', $maxSize)
                )
            ) {
                $error = self::ERR_INT_MAXSIZE_DTYPE;

                return false;
            }

            if (
                $minSize !== null
                && (
                    !is_string($minSize)
                    || !preg_match('/^-?\d+$/', $minSize)
                )
            ) {
                $error = self::ERR_INT_MINSIZE_DTYPE;

                return false;
            }
        } else {
            $error = self::ERR_INT_DTYPE_INVALID;
            $phpType = 'unsupported';

            return false;
        }

        /*
         * Adjustment for arbitrary integer value
         * (string-based, exceeding PHP_INT_MAX).
         */
        if ($phpType === 'string') {
            if ($encoding === null) {
                if ($type === 'varCharLegacy') {
                    $encoding = 'ISO-8859-1';
                } elseif ($type === 'stringJs') {
                    $encoding = 'UTF-16';
                } else {
                    $encoding = 'UTF-8';
                }
            }

            if ($type === 'stringJs' && $encoding !== 'UTF-16') {
                $error = self::ERR_STR_ENCODING;

                return false;
            }

            if (!mb_check_encoding($data, $encoding)) {
                $error = self::ERR_STR_ENCODING;

                return false;
            }

            $byteLength = $encoding === 'UTF-8'
                ? strlen($data)
                : strlen(mb_convert_encoding($data, $encoding));
        }

        switch ($type) {
            case 'bool':
                $countMaxSize = self::MAX_UBIN1; // True (1)
                $countMinSize = self::MIN_U; // False (0)

                break;
            case 'lossyIdTinyInt':
            case 'lossyUnsignedTinyInt':
            case 'unsignedTinyInt':
                $countMaxSize = $type !== 'unsignedTinyInt'
                    ? self::MAX_BIN8
                    : self::MAX_UBIN8;
                $countMinSize = $type !== 'lossyIdTinyInt'
                    ? self::MIN_U
                    : self::MAX_UBIN1;

                break;
            case 'int1':
            case 'int8':
            case 'tinyInt':
                $countMaxSize = self::MAX_BIN8;
                $countMinSize = self::MIN_BIN8;

                break;
            case 'float2':
            case 'float16':
            case 'half':
            case 'ieee754Bin16':
                /*
                 * 0 and -0 have different bit patterns in floats
                 * (0.0 !== -0.0)
                 */
                $countMaxSize = self::MAX_BIN11;
                $countMinSize = -self::MAX_BIN11;

                break;
            case 'smallSerial':
            case 'lossyIdSmallInt':
            case 'lossyUnsignedSmallInt':
            case 'unsignedSmallInt':
                $countMaxSize = $type !== 'unsignedSmallInt'
                    ? self::MAX_BIN16
                    : self::MAX_UBIN16;
                $countMinSize = self::MIN_U;

                if (
                    in_array($type, ['lossyIdSmallInt', 'smallSerial'], true)
                ) {
                    $countMinSize = self::MAX_UBIN1;
                }

                break;
            case 'int2':
            case 'int16':
            case 'smallInt':
                $countMaxSize = self::MAX_BIN16;
                $countMinSize = self::MIN_BIN16;

                break;
            case 'lossyIdMediumInt':
            case 'lossyUnsignedMediumInt':
            case 'unsignedMediumInt':
                $countMaxSize = $type !== 'unsignedMediumInt'
                    ? self::MAX_BIN24
                    : self::MAX_UBIN24;
                $countMinSize = $type !== 'lossyIdMediumInt'
                    ? self::MIN_U
                    : self::MAX_UBIN1;

                break;
            case 'int3':
            case 'int24':
            case 'mediumInt':
                /*
                 * 0 and -0 have same bit patterns in integers
                 * (0 === -0)
                 */
                $countMaxSize = self::MAX_BIN24;
                $countMinSize = self::MIN_BIN24;

                break;
            case 'float':
            case 'float4':
            case 'float32':
            case 'float32MySql':
            case 'ieee754Bin32':
            case 'real':
            case 'realPgSql':
                /*
                 * 0 and -0 have different bit patterns in floats
                 * (0.0 !== -0.0)
                 */
                $countMaxSize = self::MAX_BIN24;
                $countMinSize = -self::MAX_BIN24;

                break;
            case 'serial':
            case 'lossyIdInt':
            case 'lossyUnsignedInt':
            case 'unsignedInt':
                $countMaxSize = $type !== 'unsignedInt'
                    ? self::MAX_BIN32
                    : self::MAX_UBIN32;
                $countMinSize = self::MIN_U;

                if (in_array($type, ['lossyIdInt', 'serial'], true)) {
                    $countMinSize = self::MAX_UBIN1;
                }

                break;
            case 'int':
            case 'int4':
            case 'int32':
            case 'int32Lua':
            case 'int32Php':
            case 'integer':
                $countMaxSize = self::MAX_BIN32;
                $countMinSize = self::MIN_BIN32;

                break;
            /*
             * PHP 64-bit:
             *     In PHP 32-bit, big digits 'int' are converted
             *     to 'float' (IEEE 754 binary64).
             *
             * Use string with BCMath for full precision.
             */
            case 'double':
            case 'doublePgSql':
            case 'float8':
            case 'float64':
            case 'float64MySql':
            case 'floatLua':
            case 'floatPhp':
            case 'floatPy':
            case 'ieee754Bin64':
            case 'numericJs':
            case 'numericRealSqlite':
            case 'realMySql':
            case 'realSqlite':
                /*
                 * 0 and -0 have different bit patterns in floats
                 * (0.0 !== -0.0)
                 */
                $countMaxSize = self::MAX_BIN53;
                $countMinSize = -self::MAX_BIN53;

                if (
                    $phpType !== 'string'
                    && PHP_INT_MAX === self::MAX_BIN32
                    && $dataSize >= PHP_INT_MAX
                ) {
                    $phpType = 'float';
                }

                break;
            /*
             * PHP Integer and String limits:
             *     MIN_BIN32 to MAX_BIN32 in PHP 32-bit integer,
             *     MIN_BIN64 to MAX_BIN64 in PHP 64-bit integer,
             *     and -2 GiB to +2 GiB for string (when using BCMath).
             */
            case 'intPhp':
            case 'intSql':
                $countMaxSize = PHP_INT_MAX;
                $countMinSize = PHP_INT_MIN;

                // Count as integer using BCMath (string-based)
                if ($phpType === 'string') {
                    $countMaxSize = str_repeat('9', self::MAX_UBIN31);
                    // '-' is counted as a character in string length
                    $countMinSize = '-' . str_repeat('9', self::MAX_UBIN31 - 1);
                }

                break;
            /*
             * MySQL BIT 1–64 bits (stored as BINARY):
             *     It is unsupported in PHP 32-bit integer/float.
             *
             * Use string with BCMath for full precision.
             */
            case 'bitMySql':
                // Adjust maximum bounds
                if ($maxSize !== null) {
                    if ($phpType === 'string') {
                        if (
                            bccomp($maxSize, (string)self::MAX_UBIN1) < 0
                            || bccomp($maxSize, (string)(
                                self::MAX_UBIN6 + 1
                            )) > 0
                        ) {
                            $error = self::ERR_DTYPE_BOUND_MAX;

                            return false;
                        }

                        $maxSize = bcsub(bcpow('2', $maxSize), '1');
                    } else {
                        if (
                            $maxSize < self::MAX_UBIN1
                            || $maxSize > self::MAX_UBIN6 + 1
                        ) {
                            $error = self::ERR_DTYPE_BOUND_MAX;

                            return false;
                        }

                        $maxSize = (2 ** $maxSize) - 1;
                    }
                }

                // Adjust minimum bounds
                if ($minSize !== null) {
                    if ($phpType === 'string') {
                        if (
                            bccomp($minSize, (string)self::MAX_UBIN1) < 0
                            || bccomp($minSize, (string)(
                                self::MAX_UBIN6 + 1
                            )) > 0
                        ) {
                            $error = self::ERR_DTYPE_BOUND_MIN;

                            return false;
                        }

                        $minSize = bcsub(bcpow('2', $minSize), '1');
                    } else {
                        if (
                            $minSize < self::MAX_UBIN1
                            || $minSize > self::MAX_UBIN6 + 1
                        ) {
                            $error = self::ERR_DTYPE_BOUND_MIN;

                            return false;
                        }

                        $minSize = (2 ** $minSize) - 1;
                    }
                }

                // Fallthrough
            /*
             * PHP 64bit:
             *     It is unsupported in PHP 32-bit integer/float
             *     and unsigned integer in integer/float
             *     is unsupported.
             *
             * Use string with BCMath for full precision.
             */
            case 'bigSerial':
            case 'lossyIdBigInt':
            case 'lossyUnsignedBigInt':
            case 'unsignedBigInt':
                if (
                    $phpType !== 'string'
                    && (
                        PHP_INT_MAX === self::MAX_BIN32
                        || in_array(
                            $type,
                            ['unsignedBigInt', 'bitMySql'],
                            true
                        )
                    )
                ) {
                    $error = self::ERR_INT_NONSTRING;
                    $phpType = 'unsupported';

                    return false;
                }

                $countMaxSize = self::MAX_BIN64;
                $countMinSize = self::MIN_U;

                // Count as integer using BCMath (string-based)
                if (in_array($type, ['unsignedBigInt', 'bitMySql'], true)) {
                    $countMaxSize = self::MAX_UBIN64_STR;
                }

                if (in_array($type, ['lossyIdBigInt', 'bigSerial'], true)) {
                    $countMinSize = self::MAX_UBIN1;
                }

                break;
            /*
             * PHP 64bit:
             *     It is unsupported in PHP 32-bit integer/float.
             *
             * Use string with BCMath for full precision.
             */
            case 'bigInt':
            case 'bigIntJs':
            case 'int8b':
            case 'int64':
            case 'int64Lua':
            case 'int64Php':
            case 'integerSqlite':
            /*
             * Fixed-point MONEY stored as signed 64-bit integer × 100.
             * Two decimal places are assumed (<< 2 digits).
             * PostgreSQL internally uses similar representation.
             *
             * Real-world range (in full monetary units):
             *     -92,233,720,368,547,758.08
             *     to
             *     +92,233,720,368,547,758.07
             *
             * Stored integer values:
             *     -9223372036854775808 to 9223372036854775807
             *
             * Use string with BCMath for full precision.
             */
            case 'moneyPgSql':
            case 'numericIntSqlite':
                if (
                    $phpType !== 'string'
                    && PHP_INT_MAX === self::MAX_BIN32
                ) {
                    $error = self::ERR_INT_NONSTRING;
                    $phpType = 'unsupported';

                    return false;
                }

                $countMaxSize = self::MAX_BIN64;
                $countMinSize = self::MIN_BIN64;

                // Count as integer using BCMath (string-based)
                if ($phpType === 'string') {
                    $countMaxSize = self::MAX_BIN64_STR;
                    $countMinSize = self::MIN_BIN64_STR;
                }

                break;
            /*
             * MySQL DECIMAL/NUMERIC:
             *     Supports up to 65 **total** digits
             *     (example: DECIMAL(65,0) and limited by 65 bytes).
             *
             * Use string with BCMath for full precision.
             */
            case 'decimalMySql':
            case 'numericMySql':
                // Count as integer using BCMath (string-based)
                if ($phpType === 'string') {
                    $countMaxSize = str_repeat('9', self::MAX_UBIN6 + 2);
                    /*
                     * SQL DECIMAL/NUMERIC:
                     *
                     * '-' (minus sign) does not count as a digit,
                     * but it still occupies space
                     * (as a flag or extra bit).
                     */
                    $countMinSize =
                        '-' . str_repeat('9', self::MAX_UBIN6 + 2);

                    break;
                }

                // Fallthrough
            // MySQL CHAR: Max is 255 characters
            case 'charMySql':
            // MySQL ENUM: Max of each ENUM value is 255 characters
            case 'enumMySql':
            /*
             * MySQL pre-5.0 VARCHAR:
             *     Max is 255 characters (including 1 byte overhead).
             */
            case 'varCharLegacy':
                // Count as integer using BCMath (string-based)
                if ($phpType === 'string') {
                    $countMaxSize = str_repeat('9', self::MAX_UBIN8);
                    // '-' is counted as a character in string length
                    $countMinSize =
                        '-' . str_repeat('9', self::MAX_UBIN8 - 1);

                    break;
                }

                // Fallthrough
            /*
             * MySQL TINYTEXT:
             *     Arbitrary precision integer
             *     (limited by 255 bytes).
             *
             * Use string with BCMath for full precision.
             */
            case 'tinyText':
                // Count as byte length (string)
                if ($phpType === 'string') {
                    $countMaxSize = str_repeat('9', self::MAX_UBIN8);
                    // Minimum count is 1, but TEXT allows empty string
                    $countMinSize = str_repeat('0', self::MAX_UBIN1);
                    $dataSize = $byteLength;

                    break;
                }

                // Fallthrough
            /*
             * PostgreSQL DECIMAL/NUMERIC:
             *     Supports up to 16383 **total** digits
             *     (example: DECIMAL(16383,0) and limited by 16 KiB).
             *
             * Use string with BCMath for full precision.
             */
            case 'decimalPgSql':
            case 'numericPgSql':
                // Count as integer using BCMath (string-based)
                if ($phpType === 'string') {
                    $countMaxSize = str_repeat('9', self::MAX_UBIN14);
                    /*
                     * SQL DECIMAL/NUMERIC:
                     *
                     * '-' (minus sign) does not count as a digit,
                     * but it still occupies space
                     * (as a flag or extra bit).
                     */
                    $countMinSize = '-' . str_repeat('9', self::MAX_UBIN14);

                    break;
                }

                // Fallthrough
            /*
             * Current MySQL VARCHAR:
             * Max is 65,533 bytes per row and 2 bytes overhead
             * (practical VARCHAR ~21,844 chars in utf8mb4).
             * Up to 65,533 characters (1 character per byte).
             *
             * Use string with BCMath for full precision.
             */
            case 'varCharMySql':
                // Count as integer using BCMath (string-based)
                if ($phpType === 'string') {
                    // Without 2 bytes overhead
                    $countMaxSize = str_repeat('9', self::MAX_UBIN16 - 2);
                    /*
                     * '-' is counted as a character in string length;
                     * without 2 bytes overhead.
                     */
                    $countMinSize =
                        '-' . str_repeat('9', self::MAX_UBIN16 - 3);

                    // Without 2 bytes overhead
                    $byteMaxSize = self::MAX_UBIN16 - 2;
                    // Check if data does not exceed 65,533 bytes
                    if ($byteLength > $byteMaxSize) {
                        $error = self::ERR_DTYPE_RANGE;

                        return false;
                    }

                    break;
                }

                // Fallthrough
            /*
             * MySQL TEXT:
             *     Arbitrary precision integer
             *     (limited by 64 KiB).
             *
             * Use string with BCMath for full precision.
             */
            case 'text':
                // Count as byte length (string)
                if ($phpType === 'string') {
                    $countMaxSize = str_repeat('9', self::MAX_UBIN16);
                    // Minimum count is 1, but TEXT allows empty string
                    $countMinSize = str_repeat('0', self::MAX_UBIN1);
                    $dataSize = $byteLength;

                    break;
                }

                // Fallthrough
            /*
             * PostgreSQL CHAR/VARCHAR:
             *     Min is 1 and max is 1,047,552 characters.
             *
             * Use string with BCMath for full precision.
             */
            case 'charPgSql':
            case 'varCharPgSql':
                // Count as integer using BCMath (string-based)
                if ($phpType === 'string') {
                    $countMaxSize = str_repeat(
                        '9',
                        self::MAX_UBIN20 - self::MAX_UBIN10
                    );
                    // '-' is counted as a character in string length
                    $countMaxSize = '-' . str_repeat(
                        '9',
                        self::MAX_UBIN20 - self::MAX_UBIN10 - 1
                    );

                    break;
                }

                // Fallthrough
            /*
             * PostgreSQL BIT/VARBIT:
             *     Min is 1 and max is 10,000,000 bits.
             *
             * Use string with BCMath for full precision.
             */
            case 'bitPgSql':
            case 'varBitPgSql':
                if ($phpType === 'string') {
                    // Adjust maximum bounds
                    if ($maxSize !== null) {
                        if (
                            bccomp($maxSize, (string)self::MAX_UBIN1) < 0
                            || bccomp($maxSize, bcpow('10', '7')) > 0
                        ) {
                            $error = self::ERR_DTYPE_BOUND_MAX;

                            return false;
                        }

                        $maxSize = bcsub(bcpow('2', $maxSize), '1');
                    }

                    // Adjust minimum bounds
                    if ($minSize !== null) {
                        if (
                            bccomp($minSize, (string)self::MAX_UBIN1) < 0
                            || bccomp($minSize, bcpow('10', '7')) > 0
                        ) {
                            $error = self::ERR_DTYPE_BOUND_MIN;

                            return false;
                        }

                        $minSize = bcsub(bcpow('2', $minSize), '1');
                    }

                    // Count as integer using BCMath (string-based)
                    $countMaxSize = bcsub(bcpow('2', $minSize), '1');
                    $countMinSize = self::MAX_U;
                }

                // Fallthrough
            /*
             * MySQL MEDIUMTEXT:
             *     Arbitrary precision integer
             *     (limited by 16 MiB).
             *
             * Use string with BCMath for full precision.
             */
            case 'mediumText':
                // Count as byte length (string)
                if ($phpType === 'string') {
                    $countMaxSize = str_repeat('9', self::MAX_UBIN24);
                    // Minimum count is 1, but TEXT allows empty string
                    $countMinSize = str_repeat('0', self::MAX_UBIN1);
                    $dataSize = $byteLength;

                    break;
                }

                // Fallthrough
            /*
             * PostgreSQL ENUM:
             *     Each value of ENUM is limited to 1 GiB range.
             *
             * Use string with BCMath for full precision.
             */
            case 'enumPgSql':
            /*
             * JavaScript String:
             *     Arbitrary precision integer
             *     (limited by 1 GiB range).
             *
             * Use string with BCMath for full precision.
             */
            case 'stringJs':
            /*
             * PostgreSQL/SQlite TEXT:
             *     Arbitrary precision integer
             *     (limited by 1 GiB range).
             *
             * Use string with BCMath for full precision.
             */
            case 'textPgSql':
            case 'textSqlite':
                // Count as byte length (string)
                if ($phpType === 'string') {
                    $countMaxSize = str_repeat('9', self::MAX_UBIN30);
                    // Minimum count is 1, but TEXT allows empty string
                    $countMinSize = str_repeat('0', self::MAX_UBIN1);
                    $dataSize = $byteLength;

                    break;
                }

                // Fallthrough
            /*
             * Lua/PHP String:
             *     Arbitrary precision integer
             *     (limited by 2 GiB range).
             *
             * Use string with BCMath for full precision.
             */
            case 'stringLua':
            case 'stringPhp':
            /*
             * SQL TEXT types can support very large sizes
             * (limited by 2 GiB range).
             *
             * Use string with BCMath for full precision.
             */
            case 'textSql':
                // Count as byte length (string)
                if ($phpType === 'string') {
                    $countMaxSize = str_repeat('9', self::MAX_UBIN31);
                    // Minimum count is 1, but TEXT allows empty string
                    $countMinSize = str_repeat('0', self::MAX_UBIN1);
                    $dataSize = $byteLength;

                    break;
                }

                $error = self::ERR_INT_NONSTRING;
                $phpType = 'unsupported';

                return false;
            // MySQL JSON (4 GiB range)
            case 'jsonMySql':
            // MySQL LONGTEXT (4 GiB range)
            case 'longText':
            // Python (Limited by RAM)
            case 'stringPy':
            /*
             * Python Integer (unsupported):
             *     Arbitrary precision integer
             *     (limited by RAM).
             *
             * Use 'textSql' and string with BCMath instead.
             */
            case 'intPy':
            // Unknown type
            default:
                $error = self::ERR_DTYPE_UNSUPPORTED;
                $phpType = 'unsupported';

                return false;
        }

        // Converts to string if $phpType is string
        if ($phpType === 'string') {
            $countMaxSize = (string)$countMaxSize;
            $countMinSize = (string)$countMinSize;
        }

        // Check if maximum bound is less than minimum bound
        if ($maxSize !== null && $minSize !== null) {
            if ($phpType === 'string') {
                if (bccomp($maxSize, $minSize) < 0) {
                    $error = self::ERR_DTYPE_BOUNDS_INVALID;

                    return false;
                }
            } else {
                if ($maxSize < $minSize) {
                    $error = self::ERR_DTYPE_BOUNDS_INVALID;

                    return false;
                }
            }
        }

        // Custom maximum bounds
        if ($maxSize !== null) {
            if ($phpType === 'string') {
                if (
                    bccomp($maxSize, $countMinSize) <= 0
                    || bccomp($maxSize, $countMaxSize) > 0
                ) {
                    $error = self::ERR_DTYPE_BOUND_MAX;

                    return false;
                }
            } else {
                if ($maxSize <= $countMinSize || $maxSize > $countMaxSize) {
                    $error = self::ERR_DTYPE_BOUND_MAX;

                    return false;
                }
            }

            $countMaxSize = $maxSize;
        }

        // Custom minimum bounds
        if ($minSize !== null) {
            if ($phpType === 'string') {
                if (
                    bccomp($minSize, $countMinSize) < 0
                    || bccomp($minSize, $countMaxSize) >= 0
                ) {
                    $error = self::ERR_DTYPE_BOUND_MIN;

                    return false;
                }
            } else {
                if ($minSize < $countMinSize || $minSize >= $countMaxSize) {
                    $error = self::ERR_DTYPE_BOUND_MIN;

                    return false;
                }
            }

            $countMinSize = $minSize;
        }

        // Integer string range check
        if ($phpType === 'string') {
            if (
                bccomp($dataSize, $countMinSize) < 0
                || bccomp($dataSize, $countMaxSize) > 0
            ) {
                $error = self::ERR_DTYPE_RANGE;

                return false;
            }
        // Native int/float range check
        } else {
            if ($dataSize < $countMinSize || $dataSize > $countMaxSize) {
                $error = self::ERR_DTYPE_RANGE;

                return false;
            }
        }

        $error = self::ERR_NONE;

        return true;
    }

    /**
     * Validates a string against SQL CHAR/VARCHAR/TEXT
     * or language-specific types.
     *
     * This method verifies that a string value (`$data`) complies
     * with encoding rules and falls within the character
     * or byte length limits defined by `$type`.
     * It also allows custom minimum and maximum length bounds
     * (`$minLength`, `$maxLength`) and supports validation
     * across various SQL engines (MySQL, PostgreSQL, SQLite)
     * and languages (PHP, JS, Lua, Python).
     *
     * The encoding is detected automatically by `$type`
     * when not explicitly set.
     * Supported encodings include UTF-8 (default),
     * UTF-16 ('stringJs'), and ISO-8859-1 ('varCharLegacy').
     *
     * TEXT-type size limits:
     * - 'charMySql', 'enumMySql', 'varCharLegacy' —
     *     max 255 characters.
     * - 'tinyText' — max 255 bytes.
     * - 'text' (default) — max 65,535 bytes.
     * - 'varCharMySql' —
     *     max 65,533 bytes (~21K characters in utf8mb4).
     * - 'charPgSql', 'varCharPgSql' —
     *     min 1 character and max 1,047,552 characters.
     * - 'mediumText' — max 16,777,215 bytes.
     * - 'textPgSql', 'textSqlite', 'enumPgSql', 'stringJs' —
     *     max ~1 GiB (2³⁰).
     * - 'textSql', 'stringLua', 'stringPhp' — max 2 GiB (2³¹).
     *
     * @param string $data
     *     The string value to validate.
     * @param string $type
     *     SQL TEXT type or language label (default 'text').
     * @param int $maxLength
     *     Optional upper bound for length (0 = no override).
     * @param int $minLength
     *     Optional lower bound for length (0 = no override).
     * @param string $encoding
     *     Character encoding used:
     *     - Defaults to 'UTF-8'.
     *     - Automatically set to 'UTF-16' if $type is 'stringJs'.
     *     - Automatically set to 'ISO-8859-1'
     *         if $type is 'varCharLegacy'.
     * @param int &$error
     *     Output error code if validation fails:
     *     - self::ERR_STR_ENCODING — The encoding is invalid.
     *     - self::ERR_DTYPE_UNSUPPORTED — Unknown or invalid type.
     *     - self::ERR_DTYPE_BOUNDS_INVALID
     *         — Custom min exceeds custom max.
     *     - self::ERR_DTYPE_BOUND_MAX
     *         — Custom max exceeds allowed type limit.
     *     - self::ERR_DTYPE_BOUND_MIN — Custom min exceeds allowed minimum.
     *     - self::ERR_DTYPE_RANGE — Actual value out of allowed range.
     *     - self::ERR_NONE — No error (value is valid).
     *
     * @return bool
     *     True if data is valid for the specified type and bounds;
     *     false otherwise.
     */
    final private function validDTypeStr(
        string $data,
        string $type = 'text',
        int $maxLength = 0,
        int $minLength = 0,
        string $encoding = '',
        ?int &$error = null
    ): bool {
        if ($type === 'varCharLegacy' && $encoding === '') {
            $encoding = 'ISO-8859-1';
        } elseif ($type === 'stringJs' && $encoding === '') {
            $encoding = 'UTF-16';
        } elseif ($encoding === '') {
            $encoding = 'UTF-8';
        }

        if ($type === 'stringJs' && $encoding !== 'UTF-16') {
            $error = self::ERR_STR_ENCODING;

            return false;
        }

        if (!mb_check_encoding($data, $encoding)) {
            $error = self::ERR_STR_ENCODING;

            return false;
        }

        $byteLength = $encoding === 'UTF-8'
            ? strlen($data)
            : strlen(mb_convert_encoding($data, $encoding));
        $charLength = mb_strlen($data, $encoding);
        $countLength = $byteLength;
        $countMinLength = self::MIN_U;

        switch ($type) {
            // MySQL CHAR: Max is 255 characters
            case 'charMySql':
            // MySQL ENUM: Max of each ENUM value is 255 characters
            case 'enumMySql':
            /*
             * MySQL pre-5.0 VARCHAR:
             *     Max is 255 characters (including 1 byte overhead).
             */
            case 'varCharLegacy':
                // Count by character length
                $countLength = $charLength;

                // Fallthrough
            // MySQL TINYTEXT
            case 'tinyText':
                $countMaxLength = self::MAX_UBIN8;

                break;
            /*
             * MySQL VARCHAR limit: maximum row size is 65,535 bytes,
             * and each VARCHAR column includes a 1–2 byte overhead
             * to store the string length.
             *
             * This restricts the maximum practical VARCHAR size
             * depending on the character set used. For example:
             * - utf8mb4: up to 4 bytes per character.
             * - utf8: up to 3 bytes per character.
             * - latin1: 1 byte per character.
             *
             * Safe maximum character counts:
             * - 65,533 characters (1 byte per character).
             * - 32,766 characters (2 bytes per character).
             * - 21,844 characters (3 bytes per character).
             * - 16,383 characters (4 bytes per character).
             *
             * Note:
             *     $maxLength refers to the character count,
             *     not the byte size.
             *     The actual storage size depends on encoding
             *     and collation.
             */
            case 'varCharMySql':
                // Count by character length
                $countLength = $charLength;
                // -2 bytes overhead
                $countMaxLength = self::MAX_UBIN16 - 2;

                // Ensure data is within the 65,533-byte limit
                if ($byteLength > $countMaxLength) {
                    $error = self::ERR_DTYPE_RANGE;

                    return false;
                }

                break;
            // MySQL TEXT
            case 'text':
                $countMaxLength = self::MAX_UBIN16;

                break;
            // PostgreSQL VARCHAR: Min is 1 and max is 1,047,552 characters
            case 'charPgSql':
            // PostgreSQL VARCHAR: Min is 1 and max is 1,047,552 characters
            case 'varCharPgSql':
                // Count by character length
                $countLength = $charLength;
                $countMaxLength = self::MAX_UBIN20 - self::MAX_UBIN10;
                $countMinLength = self::MAX_UBIN1;

                break;
            // MySQL MEDIUMTEXT
            case 'mediumText':
                $countMaxLength = self::MAX_UBIN24;

                break;
            // PostgreSQL ENUM: Each value of ENUM is limited to 1 GiB
            case 'enumPgSql':
            // JavaScript string
            case 'stringJs':
            // PostgreSQL TEXT
            case 'textPgSql':
            // SQLite TEXT
            case 'textSqlite':
                $countMaxLength = self::MAX_UBIN30;

                break;
            // Lua string
            case 'stringLua':
            // PHP string
            case 'stringPhp':
            /*
             * SQL TEXT types can support very large sizes
             * (limited by 2 GiB range).
             */
            case 'textSql':
                $countMaxLength = self::MAX_UBIN31;

                break;
            // MySQL JSON (MAX_UBIN32)
            case 'jsonMySql':
            // MySQL LONGTEXT (MAX_UBIN32)
            case 'longText':
            // Python (Limited by RAM)
            case 'stringPy':
            // Unknown type
            default:
                $error = self::ERR_DTYPE_UNSUPPORTED;

                return false;
        }

        // Check if maximum length is less than minimum length
        if ($maxLength > self::MIN_U) {
            if ($maxLength < $minLength) {
                $error = self::ERR_DTYPE_BOUNDS_INVALID;

                return false;
            }
        }

        // Optional override of maximum length (within SQL TEXT limits)
        if ($maxLength > self::MIN_U) {
            if ($maxLength > $countMaxLength || $maxLength < self::MIN_U) {
                $error = self::ERR_DTYPE_BOUND_MAX;

                return false; // Exceeds TEXT type's capacity
            }

            $countMaxLength = $maxLength;
        }

        // Optional override of minimum length (within SQL TEXT limits)
        if ($minLength > self::MIN_U) {
            if ($minLength > $countMinLength || $minLength < self::MIN_U) {
                $error = self::ERR_DTYPE_BOUND_MIN;

                return false; // Exceeds TEXT type's capacity
            }

            $countMinLength = $minLength;
        }

        // Range check
        if (
            $countLength < $countMinLength
            || $countLength > $countMaxLength
        ) {
            $error = self::ERR_DTYPE_RANGE;

            return false;
        }

        $error = self::ERR_NONE;

        return true;
    }

    /**
     * Validates a timestamp string against the supported range
     * of various SQL and database-specific timestamp formats.
     *
     * The input must be a string in the format 'YYYY-MM-DD HH:MM:SS'.
     * The 'type' parameter determines which database
     * or format rules apply.
     *
     * Supported types and their ranges:
     * - 'mysql32', 'mariadb32':
     *     TIMESTAMP using 32-bit signed integer (MySQL/MariaDB).
     *     Range: 1970-01-01 00:00:01 to 2038-01-19 03:14:07.
     * - 'mariadb64':
     *     64-bit unsigned TIMESTAMP (MariaDB >= 10.2.0).
     *     Range: 1970-01-01 00:00:01 to 2106-02-07 06:28:15.
     * - 'datetime' (default):
     *     MySQL/MariaDB DATETIME.
     *     Range: 1000-01-01 00:00:00 to 9999-12-31 23:59:59.
     * - 'textSql', 'textSqlite':
     *     SQLite or similar formats stored as TEXT.
     *     Range: 0000-01-01 00:00:00 to 9999-12-31 23:59:59.
     * - 'unix32':
     *     Unix 32-bit signed timestamp.
     *     Range: 1970-01-01 00:00:00 to 2038-01-19 03:14:07.
     * - 'unix32+':
     *     Unix 32-bit signed with negative support (example: SQLite).
     *     Range: -2147483648 to 2147483647 (timestamp values).
     * - 'unix64':
     *     Unix 64-bit signed timestamp.
     *     Range: ±292 billion years. Technically:
     *     -9223372036854775808 to 9223372036854775807.
     * - 'postgresql':
     *     PostgreSQL TIMESTAMP.
     *     Range: 4713 BC to 294276 AD (note: not fully validated).
     * - 'julian':
     *     Julian day as REAL (used by SQLite).
     *     Approximate range:
     *         -210926578509 to 9224318026799 (converted to Unix epoch).
     * - 'text':
     *     Bypasses all validation and always returns true.
     *
     * @param string $value
     *     Timestamp in 'YYYY-MM-DD HH:MM:SS' format.
     * @param string $type
     *     One of: 'mysql32', 'mariadb32', 'mariadb64', 'datetime',
     *     'textSql', 'textSqlite', 'unix32', 'unix32+', 'unix64',
     *     'postgresql', 'julian', 'text'.
     * @param int &$error
     *     Output error code if validation fails:
     *     - self::ERR_STR_DTIME_FORMAT —
     *         Invalid format or unparsable string.
     *     - self::ERR_DTYPE_RANGE — Parsed timestamp is outside valid range.
     *     - self::ERR_DTYPE_UNSUPPORTED — Unknown or invalid type.
     *     - self::ERR_NONE — No error (value is valid).
     *
     * @return bool
     *     True if the timestamp is valid for the given type;
     *     false otherwise.
     */
    final private function validDTypeTStampStr(
        string $value,
        string $type = 'datetime',
        ?int &$error = null
    ): bool {
        if (
            !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)
        ) {
            $error = self::ERR_STR_DTIME_FORMAT;

            return false;
        }

        if (($timestamp = strtotime($value)) === false) {
            $error = self::ERR_STR_DTIME_FORMAT;

            return false;
        }

        switch ($type) {
            /*
             * MySQL/MariaDB TIMESTAMP (signed 32bit):
             *
             * min: '1970-01-01 00:00:01'
             * max: '2038-01-19 03:14:07'
             *
             * https://dev.mysql.com/doc/refman/9.3/en/datetime.html
             * https://mariadb.com/kb/en/timestamp/#supported-values
             */
            case 'mysql32':
            case 'mariadb32':
                if (
                    $timestamp < self::MAX_UBIN1
                    || $timestamp > self::MAX_BIN32
                ) {
                    $error = self::ERR_DTYPE_RANGE;

                    return false;
                }

                break;
            /*
             * MariaDB 64bit TIMESTAMP (unsigned 32bit):
             *
             * min: '1970-01-01 00:00:01'
             * max: '2106-02-07 06:28:15'
             *
             * https://mariadb.com/kb/en/timestamp/#supported-values
             */
            case 'mariadb64':
                if (
                    $timestamp < self::MAX_UBIN1
                    || $timestamp > self::MAX_UBIN32
                ) {
                    $error = self::ERR_DTYPE_RANGE;

                    return false;
                }

                break;
            /*
             * MySQL/MariaDB DATETIME:
             *
             * min: -30610224000
             * max: 253402300799
             *
             * https://dev.mysql.com/doc/refman/9.3/en/datetime.html
             * https://mariadb.com/kb/en/datetime/#supported-values
             */
            case 'datetime':
                if (
                    $value < '1000-01-01 00:00:00'
                    || $value > '9999-12-31 23:59:59'
                ) {
                    $error = self::ERR_DTYPE_RANGE;

                    return false;
                }

                break;
            /*
             * SQL TEXT:
             * (example: SQLite TEXT):
             *
             * min: -62167219200
             * max: 253402300799
             *
             * https://sqlite.org/datatype3.html
             */
            case 'textSql':
            case 'textSqlite':
                if (
                    $value < '0000-01-01 00:00:00'
                    || $value > '9999-12-31 23:59:59'
                ) {
                    $error = self::ERR_DTYPE_RANGE;

                    return false;
                }

                break;
            /*
             * UNIX time (32bit signed)
             *
             * min: '1970-01-01 00:00:00'
             * max: '2038-01-19 03:14:07'
             *
             * https://en.wikipedia.org/wiki/Unix_time#Limitations
             */
            case 'unix32':
                if (
                    $timestamp < self::MIN_U
                    || $timestamp > self::MAX_BIN32
                ) {
                    $error = self::ERR_DTYPE_RANGE;

                    return false;
                }

                break;
            /*
             * UNIX signed range including negatives
             * (example: SQLite INTEGER):
             *
             * min: '1970-01-01 00:00:00'
             * max: '2038-01-19 03:14:07'
             *
             * https://en.wikipedia.org/wiki/Unix_time#Limitations
             * https://sqlite.org/datatype3.html
             */
            case 'unix32+':
                if (
                    $timestamp < self::MIN_BIN32
                    || $timestamp > self::MAX_BIN32
                ) {
                    $error = self::ERR_DTYPE_RANGE;

                    return false;
                }

                break;
            /*
             * UNIX time (64bit signed):
             *
             * min: '~292277026596-??-?? ??:??:?? BC'
             * max: '~292277026596-??-?? ??:??:??'
             *
             * https://en.wikipedia.org/wiki/Unix_time#Limitations
             */
            case 'unix64':
                if (
                    $timestamp < self::MIN_BIN64
                    || $timestamp > self::MAX_BIN64
                ) {
                    $error = self::ERR_DTYPE_RANGE;

                    return false;
                }

                break;
            /*
             * PostgreSQL TIMESTAMP:
             *
             * min: '4713-01-01 00:00:00 BC'
             * max: '294276-12-31 23:59:59'
             *
             * https://www.postgresql.org
             * /docs/current/datatype-datetime.html
             */
            case 'postgresql':
            /*
             * SQL Julian REAL:
             * (example: SQLite REAL):
             *
             * min: '4713-01-01 00:00:00 BC'
             * max: '294276-12-31 23:59:59'
             *
             * https://sqlite.org/datatype3.html
             * https://en.wikipedia.org
             * /wiki/Proleptic_Gregorian_calendar
             */
            case 'julian':
                if (
                    $timestamp < -210926578509
                    || $timestamp > 9224318026799
                ) {
                    $error = self::ERR_DTYPE_RANGE;

                    return false;
                }

                break;
            case 'text':
                break;
            default:
                $error = self::ERR_DTYPE_UNSUPPORTED;

                return false;
        }

        $error = self::ERR_NONE;

        return true;
    }

    /**
     * Validates message fields and checks if the sender
     * and (optionally) receiver exist.
     *
     * This method ensures the sender ID is valid
     * and exists in the database,
     * that the booking number and message are not empty,
     * and that the booking number matches an expected value
     * if one is provided.
     * Optionally, it also validates the existence of the receiver ID.
     *
     * @param string $bookingNo
     *     The booking number associated with the message.
     *     Must be non-empty.
     * @param string $message
     *     The message content. Must be non-empty.
     * @param int $senderId
     *     The sender's user ID. Must be a valid ID (greater than 0)
     *     and exist in the database.
     * @param string $expectedBookingNo
     *     Optional expected booking number to enforce exact match.
     *     Empty string disables this check.
     * @param int $receiverId
     *     Optional receiver's user ID.
     *     If greater than 0, must exist in the database.
     *
     * @return bool
     *     True if all validations pass, false otherwise.
     */
    final private function validMsg(
        string $bookingNo,
        string $message,
        int $senderId,
        string $expectedBookingNo = '',
        int $receiverId = 0
    ): bool {
        if ($senderId < 1) {
            return false;
        }

        if (trim($bookingNo) === '' || trim($message) === '') {
            return false;
        }

        /*
         * If a specific bookingNo is expected,
         * it must match the message's bookingNo
         */
        if ($expectedBookingNo) {
            if ($bookingNo !== $expectedBookingNo) {
                return false;
            }
        }

        // Reset query builder
        $this->db->reset_query();

        // Validate senderId exists in fc_users
        if (
            $this->db
                ->select('id')
                ->from(USERS)
                ->where('id', $senderId)
                ->get()
                ->num_rows() < 1
        ) {
            return false;
        }

        // Validate receiverId if provided
        if ($receiverId > 0) {
            // Reset query builder
            $this->db->reset_query();

            if (
                $this->db
                    ->select('id')
                    ->from(USERS)
                    ->where('id', $receiverId)
                    ->get()
                    ->num_rows() < 1
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates a complete chat message data entry
     * with individual field checks.
     *
     * This includes BIGINT identifiers, strings, timestamps,
     * ENUM-like flags, and binary data.
     * Ensures all fields conform to expected types
     * and value constraints based on the database model.
     *
     * Each field is validated for type, encoding, length, and range
     * using appropriate internal methods such as `validDTypeInt()`,
     * `validDTypeStr()`, `validDTypeBlob()`
     * and `validDTypeTStampStr()`.
     *
     * Optional fields are only validated if provided (non-null).
     *
     * @param int|null $id
     *     Message ID (BIGINT SIGNED AUTO_INCREMENT).
     *     Must be ≥ 1 if provided.
     * @param int|null $productId
     *     Product ID (BIGINT SIGNED). Must be ≥ 1 if provided.
     * @param string|null $bookingNo
     *     Booking reference (VARCHAR(250)).
     *     Validated as UTF-8.
     *     Empty string is invalid.
     * @param int|null $senderId
     *     Sender user ID (BIGINT SIGNED). Must be ≥ 1 if provided.
     * @param int|null $receiverId
     *     Receiver user ID (BIGINT SIGNED). Optional, can be 0.
     * @param int|null $adminId
     *     Admin user ID (BIGINT SIGNED). Optional, can be 0.
     * @param string|null $subject
     *     Optional subject line (VARCHAR(250), UTF-8).
     * @param string|null $message
     *     Main message content (BLOB).
     *     Empty string is invalid if provided.
     * @param string|null $dateAdded
     *     Message timestamp (DATETIME).
     *     Format must be 'YYYY-MM-DD HH:MM:SS'.
     * @param bool|null $msgRead
     *     Message read status (ENUM('No', 'Yes')). Not validated here.
     * @param string|null $status
     *     Message status (ENUM('Pending', 'Accept', 'Decline')).
     *     Not validated here.
     * @param bool|null $point
     *     Point status (ENUM('0', '1')). Not validated here.
     * @param bool|null $userArchiveStatus
     *     Archive status from user (ENUM('No', 'Yes')).
     *     Not validated here.
     * @param bool|null $hostArchiveStatus
     *     Archive status from host (ENUM('No', 'Yes')).
     *     Not validated here.
     * @param bool|null $userMsgReadStatus
     *     Read status from user (ENUM('No', 'Yes')).
     *     Not validated here.
     * @param bool|null $hostMsgReadStatus
     *     Read status from host (ENUM('No', 'Yes')).
     *     Not validated here.
     * @param int|null $msgStatus
     *     Message status code (SMALLINT). Not validated here.
     * @param string|null $currencyCode
     *     Currency code (VARCHAR(10), UTF-8).
     *     Optional (example: 'USD').
     * @param bool|null $msgStarStatus
     *     Starred status (ENUM('No', 'Yes')). Not validated here.
     * @param int &$error
     *     Output parameter for validation errors.
     *     Returns values such as:
     *     self::ERR_DTYPE_RANGE, self::ERR_STR_ENUM_INVALID,
     *     self::ERR_STR_DTIME_FORMAT, self::ERR_DTYPE_UNSUPPORTED,
     *     self::ERR_STR_ENCODING, etc.
     *     Note: self::ERR_STR_ENUM_INVALID — Incorrect value from SQL ENUM.
     *
     * @return bool
     *     True if all provided fields pass validation;
     *     false if any field fails.
     */
    final private function validMsgData(
        // BIGINT SIGNED PK AUTO (UNSIGNED)
        ?string $id = null,
        // BIGINT SIGNED (UNSIGNED)
        ?string $productId = null,
        // VARCHAR(250)
        ?string $bookingNo = null,
        // BIGINT SIGNED (UNSIGNED)
        ?string $senderId = null,
        // BIGINT SIGNED DEFAULT 0 (UNSIGNED)
        ?string $receiverId = null,
        // BIGINT SIGNED DEFAULT 0 (UNSIGNED)
        ?string $adminId = null,
        // VARCHAR(250) DEFAULT ''
        ?string $subject = null,
        // BLOB
        ?string $message = null,
        // DATETIME DEFAULT NOW
        ?string $dateAdded = null,
        // ENUM('No', 'Yes') DEFAULT 0
        ?string $msgRead = null,
        // ENUM('Pending', 'Accept', 'Decline') DEFAULT 0
        ?string $status = null,
        // ENUM('0', '1') DEFAULT 0
        ?string $point = null,
        // ENUM('No', 'Yes') DEFAULT 0
        ?string $userArchiveStatus = null,
        // ENUM('No', 'Yes') DEFAULT 0
        ?string $hostArchiveStatus = null,
        // ENUM('No', 'Yes') DEFAULT 0
        ?string $userMsgReadStatus = null,
        // ENUM('No', 'Yes') DEFAULT 0
        ?string $hostMsgReadStatus = null,
        // SMALLINT SIGNED (TINYINT UNSIGNED)
        ?int $msgStatus = null,
        // VARCHAR(10) DEFAULT 'USD'
        ?string $currencyCode = null,
        // ENUM('No', 'Yes') DEFAULT 0
        ?string $msgStarStatus = null,
        // Output error
        ?int &$error = null
    ): bool {
        foreach (
            [
                ['lossyIdBigInt', $id],
                ['lossyIdBigInt', $productId],
                ['lossyIdBigInt', $senderId],
                ['lossyUnsignedBigInt', $receiverId],
                ['lossyUnsignedBigInt', $adminId],
                ['bool', $point],
                ['tinyInt', $msgStatus]
            ] as [$typ, $val]
        ) {
            if (
                $val !== null
                && !$this->validDTypeInt($val, $typ, null, null, null, $error)
            ) {
                return false;
            }
        }

        foreach (
            [
                [250, 1, 'varCharMySql', $bookingNo],
                [250, 0, 'varCharMySql', $subject],
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
            $message !== null
            && !$this->validDTypeBlob($message, 'blobSql', 0, 1, $error)
        ) {
            return false;
        }

        if (
            $dateAdded !== null
            && !$this->validDTypeTStampStr($dateAdded, 'datetime', $error)
        ) {
            return false;
        }

        foreach (
            [
                [['No', 'Yes'], $msgRead],
                [['Pending', 'Accept', 'Decline'], $status],
                [['No', 'Yes'], $userArchiveStatus],
                [['No', 'Yes'], $hostArchiveStatus],
                [['No', 'Yes'], $userMsgReadStatus],
                [['No', 'Yes'], $hostMsgReadStatus],
                [['No', 'Yes'], $msgStarStatus]
            ] as [$enum, $val]
        ) {
            if ($val !== null && !in_array($val, $enum, true)) {
                $error = self::ERR_STR_ENUM_INVALID;

                return false;
            }
        }

        $error = self::ERR_NONE;

        return true;
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
