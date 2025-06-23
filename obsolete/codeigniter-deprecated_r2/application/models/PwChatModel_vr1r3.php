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

    /**
     * Maximum value for an 8-bit signed binary data (2^7 - 1).
     *
     * 8-bit signed binary data max value (127).
     * (example: 6 bits integer, 127 bytes string).
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
     * Maximum value for an 8-bit unsigned binary data (2^8 - 1).
     *
     * 8-bit unsigned binary data max value (255).
     * (example: 1 byte integer, 255 bytes string).
     *
     * @var int
     */
    private const MAX_UBIN8 = (2 ** 8) - 1;

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
     * Minimum value for an unsigned data (2^1 - 1).
     *
     * unsigned data min value (1).
     * (example: 1 bit integer, 2 bytes string).
     *
     * @var int
     */
    private const MIN_SERIAL = (2 ** 1) - 1;

    /**
     * Minimum value for an unsigned data (2^0 - 1).
     *
     * unsigned data min value (0).
     * (example: 0 bit integer, 1 byte string).
     *
     * @var int
     */
    private const MIN_U = (2 ** 0) - 1;

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
     * Validates a binary string against known SQL BLOB-type limits.
     *
     * Supports validation for MySQL/MariaDB, PostgreSQL, SQLite,
     * and string buffer types in JS, Lua, and PHP.
     *
     * BLOB size limits by type:
     * - 'tinyBlob', 'binary': 255 bytes.
     * - 'blob' (default): 65,535 bytes.
     * - 'mediumBlob': 16,777,215 bytes.
     * - 'blobSqlite', 'bytea', 'stringJs': ~1 GiB (2³⁰).
     * - 'blobSql', 'stringLua', 'stringPhp': ~2 GiB (2³¹).
     *
     * @param string $data
     *     Binary data to validate.
     * @param string $type
     *     SQL or language-specific blob type
     *     (example: 'blob', 'bytea').
     * @param int $maxLength
     *     Optional user-defined byte cap
     *     (must not exceed BLOB capacity).
     * @param int $minLength
     *     Minimum allowed byte size (default: 0).
     *
     * @return bool
     *     True if length fits the type range,
     *     false on overflow or unknown type.
     */
    final private function validateSqlBlob(
        string $data,
        string $type = 'blob',
        int $maxLength = 0,
        int $minLength = 0
    ): bool {
        $byteLength = strlen($data);

        if ($maxLength < 0 || $minLength < 0) {
            return false;
        }

        switch ($type) {
            // MySQL BINARY
            case 'binary':
            // MySQL TINYBLOB
            case 'tinyBlob':
                $byteMaxLength = self::MAX_UBIN8;

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
             * (up to 4GiB in some engines)
             *
             * We accept any length > minLength.
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
                return false;
        }

        // Optional override of maximum length (within SQL BLOB limits)
        if ($maxLength > 0) {
            if ($maxLength > $byteMaxLength) {
                return false; // Exceeds BLOB type's capacity
            }

            $byteMaxLength = $maxLength;
        }

        return $byteLength >= $minLength && $byteLength <= $byteMaxLength;
    }

    /**
     * Validates whether a value fits within
     * a defined SQL floating-point type.
     *
     * Supports IEEE 754 formats (half, float, double),
     * SQL-compatible approximations,
     * and numeric emulation types
     * across MySQL, PostgreSQL, SQLite, JS, PHP, etc.
     *
     * Supported types:
     * - 'half', 'float2', 'float16', 'ieee754Bin16'.
     * - 'float', 'float4', 'float32', 'ieee754Bin32', 'real',
     *   'float32MySql', 'realPgSql'.
     * - 'double', 'float8', 'float64', 'ieee754Bin64', 'realSql',
     *   'doublePgSql', 'realMySql', 'realSqlite', 'numericSqlite',
     *   'floatPhp', 'floatLua', 'numericJs'.
     *
     * In safe mode:
     * - Rejects NaN and Infinity.
     * - Enforces rounded-digit precision:
     *     - half (float16): up to 4 significant digits.
     *     - float (float32): up to 7 significant digits.
     *     - double (float64): up to 17 significant digits.
     *
     * In unsafe mode:
     * - Allows extended IEEE 754 range for float16/32/64.
     * - Does not limit significant digits.
     *
     * Range limits:
     * - float16: safe ±2048.0, unsafe ±65504.0.
     * - float32: safe ±16,777,216, unsafe ±3.4028235e+38.
     * - float64:
     *     safe ±9,007,199,254,740,991,
     *     unsafe ±1.7976931348623157e+308.
     *
     * @param float $data
     *     The floating-point value to validate.
     * @param bool $safe
     *     Whether to enforce strict precision and finite-only values.
     * @param string $type
     *     The float type
     *     (example: 'float16', 'float32', 'float64', etc).
     * @param float|null $maxSize
     *     Optional custom upper bound
     *     (must not exceed native/unsafe limits).
     * @param float|null $minSize
     *     Optional custom lower bound
     *     (must not fall below native/unsafe limits).
     * @param string &$error
     *     Error code output if validation fails:
     *     - 'infinite-or-nan'
     *         → NaN or Infinity rejected (safe mode only).
     *     - 'unsupported-type' → Unknown or invalid $type.
     *     - 'invalid-max-bound' → Custom max exceeds internal range.
     *     - 'invalid-min-bound' → Custom min below internal range.
     *     - 'excessive-digits'
     *         → Too many significant digits (safe mode only).
     *     - 'out-of-range' → Value exceeds valid range.
     *     - 'none' → No error (value is valid).
     *
     * @return bool
     *     True if the value is valid in type, range,
     *     and (if safe) precision; false otherwise.
     */
    final private function validateSqlFloat(
        float $data,
        bool $safe = true,
        string $type = 'double',
        ?float $maxSize = null,
        ?float $minSize = null,
        string &$error = 'none'
    ): bool {
        // Reject NaN and Infinity
        if ($safe && !is_finite($data)) {
            $error = 'infinite-or-nan';

            return false;
        }

        switch ($type) {
            case 'float2':
            case 'float16':
            case 'half':
            case 'ieee754Bin16':
                $bitMinSize = $safe
                    ? -self::MAX_BIN11
                    : -65504.0;
                $bitMaxSize = $safe
                    ? self::MAX_BIN11
                    : 65504.0;
                $safeDigits = 4;

                break;
            case 'float':
            case 'float4':
            case 'float32':
            case 'float32MySql':
            case 'ieee754Bin32':
            case 'real':
            case 'realPgSql':
                $bitMinSize = $safe
                    ? -self::MAX_BIN24
                    : -3.4028235e+38;
                $bitMaxSize = $safe
                    ? self::MAX_BIN24
                    : 3.4028235e+38;
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
                $bitMinSize = $safe
                    ? -self::MAX_BIN53
                    : -1.7976931348623157e+308;
                $bitMaxSize = $safe
                    ? self::MAX_BIN53
                    : 1.7976931348623157e+308;
                $safeDigits = 17;

                break;
            default:
                $error = 'unsupported-type';

                return false;
        }

        // Custom maximum bounds
        if ($maxSize !== null) {
            if ($maxSize <= $bitMinSize || $maxSize > $bitMaxSize) {
                $error = 'invalid-max-bound';

                return false;
            }

            $bitMaxSize = $maxSize;
        }

        // Custom minimum bounds
        if ($minSize !== null) {
            if ($minSize < $bitMinSize || $minSize >= $bitMaxSize) {
                $error = 'invalid-min-bound';

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
                $error = 'excessive-digits';

                return false;
            }
        }

        // Range check
        if ($data < $bitMinSize || $data > $bitMaxSize) {
            $error = 'out-of-range';

            return false;
        }

        return true;
    }

    /**
     * Validates whether a numeric value
     * fits within a defined SQL integer type.
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
     * Supported types and value ranges:
     * - 'bool': 0 (false) to 1 (true).
     * - 'tinyInt', 'int1', 'int8': -128 to 127.
     * - 'lossyIdTinyInt': 0 to 127.
     * - 'unsignedTinyInt': 0 to 255.
     * - 'float2', 'float16', 'half', 'ieee754Bin16',
     *     ±2¹¹ (safe range).
     * - 'smallInt', 'int2', 'int16': -32,768 to 32,767.
     * - 'smallSerial': 1 to 32,767.
     * - 'lossyIdSmallInt': 0 to 32,767.
     * - 'unsignedSmallInt': 0 to 65,535.
     * - 'mediumInt', 'int3', 'int24': -8,388,608 to 8,388,607.
     * - 'float', 'float4', 'float32', 'float32MySql', 'ieee754Bin32',
     *   'real', 'realPgSql':
     *     ±2²⁴ (safe range).
     * - 'lossyIdMediumInt': 0 to 8,388,607.
     * - 'unsignedMediumInt': 0 to 16,777,215.
     * - 'int' (default), 'int4', 'int32', 'int32Lua', 'int32Php',
     *   'integer':
     *     -2,147,483,648 to 2,147,483,647.
     * - 'serial': 1 to 2,147,483,647.
     * - 'lossyIdInt': 0 to 2,147,483,647.
     * - 'unsignedInt': 0 to 4,294,967,295.
     * - 'float8', 'float64', 'float64MySql', 'floatLua', 'floatPhp',
     *   'double', 'doublePgSql', 'ieee754Bin64',
     *   'numericJs', 'numericRealSqlite', 'realMySql', 'realSqlite':
     *     ±2⁵³ (safe range).
     * - 'intPhp', 'intSql': up to PHP_INT_MAX (varies by arch).
     *
     * Supported types and value ranges on PHP 64-bit or string:
     * - 'bigInt', 'bigIntJs', 'int8b', 'int64',
     *   'int64Lua', 'int64Php', 'integerSqlite', 'numericIntSqlite':
     *     -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807
     * - 'moneyPgSql' (stored as signed 64-bit integer × 100):
     *     Integer range:
     *         -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807.
     *     Real-world range:
     *         -92,233,720,368,547,758.08 to 92,233,720,368,547,758.07.
     *     Two implicit decimal digits (fixed-point, not float).
     * - 'bigSerial': 1 to 9,223,372,036,854,775,807.
     * - 'lossyIdBigInt': 0 to 9,223,372,036,854,775,807.
     *
     * On all platforms (string only):
     * - 'decimalMySql', 'numericMySql': up to 65 digits (65 bytes).
     * - 'tinyText': up to 255 digits (255 bytes).
     * - 'decimalPgSql', 'numericPgSql': up to 16383 digits (16 KiB).
     * - 'text': limited by 64 KiB.
     * - 'mediumText': limited by 16 MiB.
     * - 'stringJs', 'textPgSql', 'textSqlite': limited by 1 GiB.
     * - 'stringLua', 'stringPhp', 'textSql': limited by 2 GiB.
     *
     * Optional range overrides '$minSize' and '$maxSize'
     * must match the native type:
     * - int/float for numeric values.
     * - string for BCMath-based strings.
     *
     * @param int|float|string $data
     *     Value to validate:
     *         must be integer, float with exact integer part,
     *         or numeric string.
     * @param string $type
     *     SQL or platform type label
     *     (example: 'int32', 'bigInt', 'float64', 'intPhp').
     * @param int|float|string|null $maxSize
     *     Optional upper bound override
     *     (not exceeding native range and must respect type format).
     * @param int|float|string|null $minSize
     *     Optional lower bound override
     *     (not below native range and must respect type format).
     * @param string &$error
     *     Error code output if validation fails:
     *     - 'unsupported-type' → Unknown or invalid $type.
     *     - 'invalid-data-type' → Invalid data type for $data.
     *     - 'invalid-maxsize-type'
     *         → Invalid data type for $maxSize.
     *     - 'invalid-minsize-type'
     *         → Invalid data type for $minSize.
     *     - 'invalid-max-bound' → Custom max exceeds internal range.
     *     - 'invalid-min-bound' → Custom min below internal range.
     *     - 'out-of-range' → Value exceeds valid range.
     *     - 'none' → No error (value is valid).
     * @param string &$phpType
     *     Output PHP type as determined
     *     ('int', 'float', 'string', or 'unsupported').
     *
     * @return bool
     *     True if the value is valid within the specified range;
     *     false if the type is unknown
     *     or data exceeds platform limits.
     */
    final private function validateSqlInt(
        $data,
        string $type = 'int',
        $maxSize = null,
        $minSize = null,
        string &$error = 'none',
        string &$phpType = 'int'
    ): bool {
        // Validate input type and detect PHP type
        if (
            is_int($data)
            || (is_float($data) && $data === (float)(int)$data)
        ) {
            // Already int; $phpType = 'int' by default

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
                $error = 'invalid-maxsize-type';

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
                $error = 'invalid-minsize-type';

                return false;
            }
        ) elseif (is_numeric($data) && preg_match('/^-?\d+$/', $data)) {
            // Numeric string, but must be integer (no decimal point)
            $phpType = 'string';

            if (
                $maxSize !== null
                && (
                    !is_string($maxSize)
                    || !preg_match('/^-?\d+$/', $maxSize)
                )
            ) {
                $error = 'invalid-maxsize-type';

                return false;
            }

            if (
                $minSize !== null
                && (
                    !is_string($minSize)
                    || !preg_match('/^-?\d+$/', $minSize)
                )
            ) {
                $error = 'invalid-minsize-type';

                return false;
            }
        } else {
            $error = 'invalid-data-type';
            $phpType = 'unsupported';

            return false;
        }

        switch ($type) {
            case 'bool':
                $bitMinSize = self::MIN_U; // False (0)
                $bitMaxSize = self::MIN_SERIAL; // True (1)

                break;
            case 'lossyIdTinyInt':
            case 'unsignedTinyInt':
                $bitMinSize = self::MIN_U;
                $bitMaxSize = $type === 'lossyIdTinyInt'
                    ? self::MAX_BIN8
                    : self::MAX_UBIN8;

                break;
            case 'int1':
            case 'int8':
            case 'tinyInt':
                $bitMinSize = self::MIN_BIN8;
                $bitMaxSize = self::MAX_BIN8;

                break;
            case 'float2':
            case 'float16':
            case 'half':
            case 'ieee754Bin16':
                /*
                 * 0 and -0 have different bit patterns in floats
                 * (0.0 !== -0.0)
                 */
                $bitMinSize = -self::MAX_BIN11;
                $bitMaxSize = self::MAX_BIN11;

                break;
            case 'smallSerial':
                $bitMinSize = self::MIN_SERIAL;
                $bitMaxSize = self::MAX_BIN16;

                break;
            case 'lossyIdSmallInt':
            case 'unsignedSmallInt':
                $bitMinSize = self::MIN_U;
                $bitMaxSize = $type === 'lossyIdSmallInt'
                    ? self::MAX_BIN16
                    : self::MAX_UBIN16;

                break;
            case 'int2':
            case 'int16':
            case 'smallInt':
                $bitMinSize = self::MIN_BIN16;
                $bitMaxSize = self::MAX_BIN16;

                break;
            case 'lossyIdMediumInt':
            case 'unsignedMediumInt':
                $bitMinSize = self::MIN_U;
                $bitMaxSize = $type === 'lossyIdMediumInt'
                    ? self::MAX_BIN24
                    : self::MAX_UBIN24;

                break;
            case 'int3':
            case 'int24':
            case 'mediumInt':
                /*
                 * 0 and -0 have same bit patterns in integers
                 * (0 === -0)
                 */
                $bitMinSize = self::MIN_BIN24;
                $bitMaxSize = self::MAX_BIN24;

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
                $bitMinSize = -self::MAX_BIN24;
                $bitMaxSize = self::MAX_BIN24;

                break;
            case 'serial':
                $bitMinSize = self::MIN_SERIAL;
                $bitMaxSize = self::MAX_BIN32;

                break;
            case 'lossyIdInt':
            case 'unsignedInt':
                $bitMinSize = self::MIN_U;
                $bitMaxSize = $type === 'lossyIdInt'
                    ? self::MAX_BIN32
                    : self::MAX_UBIN32;

                break;
            case 'int':
            case 'int4':
            case 'int32':
            case 'int32Lua':
            case 'int32Php':
            case 'integer':
                $bitMinSize = self::MIN_BIN32;
                $bitMaxSize = self::MAX_BIN32;

                break;
            /*
             * PHP Integer and String limit:
             *     MIN_BIN32 to MAX_BIN32 in PHP 32-bit integer,
             *     MIN_BIN64 to MAX_BIN64 in PHP 64-bit integer.
             *     and -2 GiB to 2 GiB string (using BCMath).
             */
            case 'intPhp':
            case 'intSql':
                $bitMinSize = PHP_INT_MIN;
                $bitMaxSize = PHP_INT_MAX;

                if ($phpType === 'string') {
                    $bitMinSize = '-' . str_repeat('9', self::MAX_UBIN31);
                    $bitMaxSize = str_repeat('9', self::MAX_UBIN31);
                }

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
                $bitMinSize = -self::MAX_BIN53;
                $bitMaxSize = self::MAX_BIN53;

                if (
                    $phpType !== 'string'
                    && PHP_INT_MAX === self::MAX_BIN32
                    && $data >= PHP_INT_MAX
                ) {
                    $phpType = 'float';
                }

                break;
            /*
             * PHP 64bit:
             *     It is unsupported in PHP 32-bit.
             *
             * Use string with BCMath for full precision.
             */
            case 'bigSerial':
                if (
                    $phpType !== 'string'
                    && PHP_INT_MAX === self::MAX_BIN32
                ) {
                    $phpType = 'unsupported';

                    return false;
                }

                $bitMinSize = self::MIN_SERIAL;
                $bitMaxSize = self::MAX_BIN64;

                if ($phpType === 'string') {
                    $bitMaxSize = self::MAX_BIN64_STR;
                }

                break;
            /*
             * PHP 64bit:
             *     It is unsupported in PHP 32-bit
             *     and unsigned integer is unsupported.
             *
             * Use string with BCMath for full precision.
             */
            case 'lossyIdBigInt':
            case 'unsignedBigInt':
                if (
                    $phpType !== 'string'
                    && (
                        PHP_INT_MAX === self::MAX_BIN32
                        || $type === 'unsignedBigInt'
                    )
                ) {
                    $phpType = 'unsupported';

                    return false;
                }

                $bitMinSize = self::MIN_U;
                $bitMaxSize = $type === 'lossyIdBigInt'
                    ? self::MAX_BIN64
                    : self::MAX_UBIN64_STR;

                break;
            /*
             * PHP 64bit:
             *     It is unsupported in PHP 32-bit.
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
                    $phpType = 'unsupported';

                    return false;
                }

                $bitMinSize = self::MIN_BIN64;
                $bitMaxSize = self::MAX_BIN64;

                if ($phpType === 'string') {
                    $bitMinSize = self::MIN_BIN64_STR;
                    $bitMaxSize = self::MAX_BIN64_STR;
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
                if ($phpType === 'string') {
                    /*
                     * SQL DECIMAL/NUMERIC:
                     *
                     * '-' (minus sign) does not count as a digit,
                     * but it still occupies space
                     * (as a flag or extra bit).
                     */
                    $bitMinSize = '-' . str_repeat('9', (2 ** 6) + 1);
                    $bitMaxSize = str_repeat('9', (2 ** 6) + 1);

                    break;
                }
            /*
             * MySQL TINYTEXT:
             *     Arbitrary precision integer
             *     (limited by 255 bytes).
             *
             * Use string with BCMath for full precision.
             */
            case 'tinyText':
                if ($phpType === 'string') {
                    // '-' is counted as a character in string length
                    $bitMinSize = '-' . str_repeat('9', self::MAX_UBIN8 - 1);
                    $bitMaxSize = str_repeat('9', self::MAX_UBIN8);

                    break;
                }
            /*
             * PostgreSQL DECIMAL/NUMERIC:
             *     Supports up to 16383 **total** digits
             *     (example: DECIMAL(16383,0) and limited by 16 KiB).
             *
             * Use string with BCMath for full precision.
             */
            case 'decimalPgSql':
            case 'numericPgSql':
                if ($phpType === 'string') {
                    /*
                     * SQL DECIMAL/NUMERIC:
                     *
                     * '-' (minus sign) does not count as a digit,
                     * but it still occupies space
                     * (as a flag or extra bit).
                     */
                    $bitMinSize = '-' . str_repeat('9', self::MAX_UBIN14);
                    $bitMaxSize = str_repeat('9', self::MAX_UBIN14);

                    break;
                }
            /*
             * MySQL TEXT:
             *     Arbitrary precision integer
             *     (limited by 64 KiB).
             *
             * Use string with BCMath for full precision.
             */
            case 'text':
                if ($phpType === 'string') {
                    // '-' is counted as a character in string length
                    $bitMinSize = '-' . str_repeat('9', self::MAX_UBIN16 - 1);
                    $bitMaxSize = str_repeat('9', self::MAX_UBIN16);

                    break;
                }
            /*
             * MySQL MEDIUMTEXT:
             *     Arbitrary precision integer
             *     (limited by 16 MiB).
             *
             * Use string with BCMath for full precision.
             */
            case 'mediumText':
                if ($phpType === 'string') {
                    // '-' is counted as a character in string length
                    $bitMinSize = '-' . str_repeat('9', self::MAX_UBIN24 - 1);
                    $bitMaxSize = str_repeat('9', self::MAX_UBIN24);

                    break;
                }
            /*
             * JavaScript String:
             *     Arbitrary precision integer
             *     (limited by 1 GiB).
             *
             * Use string with BCMath for full precision.
             */
            case 'stringJs':
            /*
             * PostgreSQL/SQlite TEXT:
             *     Arbitrary precision integer
             *     (limited by 1 GiB).
             *
             * Use string with BCMath for full precision.
             */
            case 'textPgSql':
            case 'textSqlite':
                if ($phpType === 'string') {
                    // '-' is counted as a character in string length
                    $bitMinSize = '-' . str_repeat('9', self::MAX_UBIN30 - 1);
                    $bitMaxSize = str_repeat('9', self::MAX_UBIN30);

                    break;
                }
            /*
             * Lua/PHP String:
             *     Arbitrary precision integer
             *     (limited by 2 GiB).
             *
             * Use string with BCMath for full precision.
             */
            case 'stringLua':
            case 'stringPhp':
            /*
             * SQL TEXT types can support very large sizes
             * (up to 4GiB in some engines)
             *
             * Use string with BCMath for full precision.
             */
            case 'textSql':
                if ($phpType === 'string') {
                    // '-' is counted as a character in string length
                    $bitMinSize = '-' . str_repeat('9', self::MAX_UBIN31 - 1);
                    $bitMaxSize = str_repeat('9', self::MAX_UBIN31);

                    break;
                }
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
                $error = 'unsupported-type';
                $phpType = 'unsupported';

                return false; // Unknown type
        }

        if ($phpType === 'string') {
            $bitMinSize = (string)$bitMinSize;
            $bitMaxSize = (string)$bitMaxSize;
        }

        // Custom maximum bounds
        if ($maxSize !== null) {
            if ($phpType === 'string') {
                if (
                    bccomp($maxSize, $bitMinSize) <= 0
                    || bccomp($maxSize, $bitMaxSize) > 0
                ) {
                    $error = 'invalid-max-bound';

                    return false;
                }
            } else {
                if ($maxSize <= $bitMinSize || $maxSize > $bitMaxSize) {
                    $error = 'invalid-max-bound';

                    return false;
                }
            }

            $bitMaxSize = $maxSize;
        }

        // Custom minimum bounds
        if ($minSize !== null) {
            if ($phpType === 'string') {
                if (
                    bccomp($minSize, $bitMinSize) < 0
                    || bccomp($minSize, $bitMaxSize) >= 0
                ) {
                    $error = 'invalid-min-bound';

                    return false;
                }
            } else {
                if ($minSize < $bitMinSize || $minSize >= $bitMaxSize) {
                    $error = 'invalid-min-bound';

                    return false;
                }
            }

            $bitMinSize = $minSize;
        }

        // Integer string range check
        if ($phpType === 'string') {
            if (
                bccomp($data, $bitMinSize) < 0
                || bccomp($data, $bitMaxSize) > 0
            ) {
                $error = 'out-of-range';

                return false;
            }
        // Native int/float range check
        } else {
            if ($data < $bitMinSize || $data > $bitMaxSize) {
                $error = 'out-of-range';

                return false;
            }
        }

        return true;
    }

    /**
     * Validates a string against SQL TEXT and VARCHAR-type limits.
     *
     * Considers both character and byte length,
     * and encoding (default UTF-8).
     * Supports MySQL, PostgreSQL, SQLite, and language string types.
     *
     * TEXT-type size limits:
     * - 'char': fixed length, max 255 chars.
     * - 'varCharLegacy': max 255 bytes (MySQL <5.0).
     * - 'varCharMySql': max 65,535 bytes (~21K chars in utf8mb4).
     * - 'tinyText': max 255 bytes.
     * - 'text' (default): max 65,535 bytes.
     * - 'mediumText': max 16,777,215 bytes.
     * - 'textPgSql', 'textSqlite', 'stringJs': max ~1 GiB (2³⁰).
     * - 'textSql', 'stringLua', 'stringPhp': max 2 GiB (2³¹).
     *
     * @param string $data
     *     The string to validate.
     * @param string $type
     *     String type to validate against
     *     (example: 'text', 'varCharMySql').
     * @param int $maxLength
     *     Maximum allowed size in bytes or characters
     *     (type-dependent).
     * @param string $encoding
     *     Encoding used (default: 'UTF-8'; use 'UTF-16' for JS).
     * @param int $minLength
     *     Minimum character length (default: 0).
     *
     * @return bool
     *     True if the string is valid for the specified type;
     *     false on overflow or invalid configuration.
     */
    final private function validateSqlString(
        string $data,
        string $type = 'text',
        string $encoding = 'UTF-8',
        int $maxLength = 0,
        int $minLength = 0
    ): bool {
        $byteLength = $encoding === 'UTF-8'
            ? strlen($data)
            : strlen(mb_convert_encoding($data, $encoding));
        $charLength = mb_strlen($data, $encoding);

        if ($maxLength < 0 || $minLength < 0) {
            return false;
        }

        switch ($type) {
            // MySQL CHAR
            case 'char':
                $charMaxLength =
                    $maxLength > 0 ? $maxLength : self::MAX_UBIN8;

                if ($charMaxLength < 1 || $charMaxLength > self::MAX_UBIN8) {
                    return false;
                }

                return $charLength >= $minLength
                    && $charLength <= $charMaxLength;
            // MySQL pre-5.0: VARCHAR limited to 255 bytes
            case 'varCharLegacy':
                $byteMaxLength =
                    $maxLength > 0 ? $maxLength : self::MAX_UBIN8;

                if ($byteMaxLength < 1 || $byteMaxLength > self::MAX_UBIN8) {
                    return false;
                }

                break;
            // MySQL TINYTEXT
            case 'tinyText':
                $byteMaxLength = self::MAX_UBIN8;

                break;
            /*
             * Current MySQL: VARCHAR max is 65535 bytes per row
             * (practical VARCHAR ~21844 chars in utf8mb4)
             */
            case 'varCharMySql':
                $byteMaxLength =
                    $maxLength > 0 ? $maxLength : self::MAX_UBIN16;

                if ($byteMaxLength < 1 || $byteMaxLength > self::MAX_UBIN16) {
                    return false;
                }

                break;
            // MySQL TEXT
            case 'text':
                $byteMaxLength = self::MAX_UBIN16;

                break;
            // MySQL MEDIUMTEXT
            case 'mediumText':
                $byteMaxLength = self::MAX_UBIN24;

                break;
            // JavaScript string
            case 'stringJs':
                $encoding = 'UTF-16';
            // PostgreSQL TEXT
            case 'textPgSql':
            // SQLite TEXT
            case 'textSqlite':
                $byteMaxLength = self::MAX_UBIN30;

                break;
            // Lua string
            case 'stringLua':
            // PHP string
            case 'stringPhp':
            /*
             * SQL TEXT types can support very large sizes
             * (up to 4GiB in some engines)
             *
             * We accept any length > minLength.
             */
            case 'textSql':
                $byteMaxLength = self::MAX_UBIN31;

                break;
            // MySQL JSON (MAX_UBIN32)
            case 'jsonMySql':
            // MySQL LONGTEXT (MAX_UBIN32)
            case 'longText':
            // Python (Limited by RAM)
            case 'stringPy':
            // Unknown type
            default:
                return false;
        }

        // Optional override of maximum length (within SQL TEXT limits)
        if ($maxLength > 0) {
            if ($maxLength > $byteMaxLength) {
                return false; // Exceeds TEXT type's capacity
            }

            $byteMaxLength = $maxLength;
        }

        return $byteLength >= $minLength && $byteLength <= $byteMaxLength;
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
     *
     * @return bool
     *     True if the timestamp is valid for the given type;
     *     false otherwise.
     */
    final private function validateSqlTimestampString(
        string $value,
        string $type = 'datetime'
    ): bool {
        if (
            !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)
        ) {
            return false;
        }

        if (($timestamp = strtotime($value)) === false) {
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
                return $timestamp >= 1 && $timestamp <= self::MAX_BIN32;
            /*
             * MariaDB 64bit TIMESTAMP (unsigned 32bit):
             *
             * min: '1970-01-01 00:00:01'
             * max: '2106-02-07 06:28:15'
             *
             * https://mariadb.com/kb/en/timestamp/#supported-values
             */
            case 'mariadb64':
                return $timestamp >= 1 && $timestamp <= self::MAX_UBIN32;
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
                return $value >= '1000-01-01 00:00:00'
                    && $value <= '9999-12-31 23:59:59';
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
                return $value >= '0000-01-01 00:00:00'
                    && $value <= '9999-12-31 23:59:59';
            /*
             * UNIX time (32bit signed)
             *
             * min: '1970-01-01 00:00:00'
             * max: '2038-01-19 03:14:07'
             *
             * https://en.wikipedia.org/wiki/Unix_time#Limitations
             */
            case 'unix32':
                return $timestamp >= self::MIN_U
                    && $timestamp <= self::MAX_BIN32;
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
                return $timestamp >= self::MIN_BIN32
                    && $timestamp <= self::MAX_BIN32;
            /*
             * UNIX time (64bit signed):
             *
             * min: '~292277026596-??-?? ??:??:?? BC'
             * max: '~292277026596-??-?? ??:??:??'
             *
             * https://en.wikipedia.org/wiki/Unix_time#Limitations
             */
            case 'unix64':
                return $timestamp >= self::MIN_BIN64
                    && $timestamp <= self::MAX_BIN64;
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
                return $timestamp >= -210926578509
                    && $timestamp <= 9224318026799;
            case 'text':
                return true;
            default:
                return false;
        }
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
    final private function validateMessage(
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
 * Validates a complete message structure according to SQL schema constraints
 * and database engine-specific limits (currently supports 'mySql').
 *
 * Each field is validated for type, encoding, length, and range
 * using appropriate internal methods such as `validateSqlInt()`,
 * `validateSqlString()`, `validateSqlBlob()` and `validateSqlTimestampString()`.
 *
 * Optional fields are only validated if provided (non-null).
 *
 * @param string $engine
 *     The database engine context for validation. Only 'mySql' is supported.
 *
 * @param int|null $id
 *     Message ID (BIGINT UNSIGNED AUTO_INCREMENT). Must be ≥ 1 if provided.
 *
 * @param int|null $productId
 *     Product ID (BIGINT UNSIGNED). Must be ≥ 1 if provided.
 *
 * @param string|null $bookingNo
 *     Booking reference (VARCHAR(250)). Validated as UTF-8. Empty string is invalid.
 *
 * @param int|null $senderId
 *     Sender user ID (BIGINT UNSIGNED). Must be ≥ 1 if provided.
 *
 * @param int|null $receiverId
 *     Receiver user ID (BIGINT UNSIGNED). Optional, can be 0.
 *
 * @param int|null $adminId
 *     Admin user ID (BIGINT UNSIGNED). Optional, can be 0.
 *
 * @param string|null $subject
 *     Optional subject line (VARCHAR(250), UTF-8).
 *
 * @param string|null $message
 *     Main message content (LONGBLOB). Empty string is invalid if provided.
 *
 * @param string|null $dateAdded
 *     Message timestamp (DATETIME). Format must be 'YYYY-MM-DD HH:MM:SS'.
 *
 * @param bool|null $msgRead
 *     Message read status (ENUM('No', 'Yes')). Not validated here.
 *
 * @param string|null $status
 *     Message status (ENUM('Pending', 'Accept', 'Decline')). Not validated here.
 *
 * @param bool|null $point
 *     Point status (ENUM('0', '1')). Not validated here.
 *
 * @param bool|null $userArchiveStatus
 *     Archive status from user (ENUM('No', 'Yes')). Not validated here.
 *
 * @param bool|null $hostArchiveStatus
 *     Archive status from host (ENUM('No', 'Yes')). Not validated here.
 *
 * @param bool|null $userMsgReadStatus
 *     Read status from user (ENUM('No', 'Yes')). Not validated here.
 *
 * @param bool|null $hostMsgReadStatus
 *     Read status from host (ENUM('No', 'Yes')). Not validated here.
 *
 * @param int|null $msgStatus
 *     Message status code (SMALLINT). Not validated here.
 *
 * @param string|null $currencyCode
 *     Currency code (VARCHAR(10), UTF-8). Optional. e.g., 'USD'.
 *
 * @param bool|null $msgStarStatus
 *     Starred status (ENUM('No', 'Yes')). Not validated here.
 *
 * @return bool
 *     Returns true if all provided fields are valid according to the engine rules;
 *     false if any constraint is violated or engine is unsupported.
 */
    final private function validateMessageData(
        string $engine,
        // BIGINT SIGNED PK AUTO (UNSIGNED)
        ?int $id = null,
        // BIGINT SIGNED (UNSIGNED)
        ?int $productId = null,
        // VARCHAR(250)
        ?string $bookingNo = null,
        // BIGINT SIGNED (UNSIGNED)
        ?int $senderId = null,
        // BIGINT SIGNED DEFAULT 0 (UNSIGNED)
        ?int $receiverId = null,
        // BIGINT SIGNED DEFAULT 0 (UNSIGNED)
        ?int $adminId = null,
        // VARCHAR(250) DEFAULT ''
        ?string $subject = null,
        // LONGBLOB
        ?string $message = null,
        // DATETIME DEFAULT NOW
        ?string $dateAdded = null,
        // ENUM('No', 'YES') DEFAULT 0
        ?bool $msgRead = null,
        // ENUM('Pending', 'Accept', 'Decline') DEFAULT 0
        ?string $status = null,
        // ENUM('0', '1') DEFAULT 0
        ?bool $point = null,
        // ENUM('No', 'YES') DEFAULT 0
        ?bool $userArchiveStatus = null,
        // ENUM('No', 'YES') DEFAULT 0
        ?bool $hostArchiveStatus = null,
        // ENUM('No', 'YES') DEFAULT 0
        ?bool $userMsgReadStatus = null,
        // ENUM('No', 'YES') DEFAULT 0
        ?bool $hostMsgReadStatus = null,
        // SMALLINT SIGNED (TINYINT UNSIGNED)
        ?int $msgStatus = null,
        // VARCHAR(10) DEFAULT 'USD'
        ?string $currencyCode = null,
        // ENUM('No', 'YES') DEFAULT 0
        ?bool $msgStarStatus = null
    ): bool {
        if (
            $id !== null
            && !$this->validateSqlInt($id, 'bigInt', null, 1)
        ) {
            return false;
        }

                if (
                    $productId
                    && !$this->validateSqlInt(
                        $productId,
                        'unsignedBigInt',
                        null, // Ignored value
                        1 // Check if is not 0
                    )
                ) {
                    return false;
                }

                if (
                    $bookingNo
                    && !$this->validateSqlString(
                        $bookingNo,
                        'varCharMySql',
                        'UTF-8',
                        250, // VARCHAR(250)
                        0, // Ignored value
                        1 // Check if is not ''
                    )
                ) {
                    return false;
                }

                if (
                    $senderId
                    && !$this->validateSqlInt(
                        $senderId,
                        'unsignedBigInt',
                        null, // Ignored value
                        1 // Check if is not 0
                    )
                ) {
                    return false;
                }

                if (
                    $receiverId
                    && !$this->validateSqlInt($receiverId, 'unsignedBigInt')
                ) {
                    return false;
                }

                if (
                    $adminId
                    && !$this->validateSqlInt($adminId, 'unsignedBigInt')
                ) {
                    return false;
                }

                if (
                    $subject
                    && !$this->validateSqlString(
                        $subject,
                        'varCharMySql',
                        'UTF-8',
                        250 // VARCHAR(250)
                    )
                ) {
                    return false;
                }

                if (
                    $message
                    && !$this->validateSqlBlob(
                        $message,
                        'longBlob',
                        0, // Ignored value
                        1 // Check if is not ''
                    )
                ) {
                    return false;
                }

                if (
                    $dateAdded
                    && !$this->validateSqlTimestampString(
                        $dateAdded,
                        'datetime'
                    )
                ) {
                    return false;
                }

                if (
                    $currencyCode
                    && !$this->validateSqlString(
                        $currencyCode,
                        'varCharMySql',
                        'UTF-8',
                        10 // VARCHAR(10)
                    )
                ) {
                    return false;
                }

                return true;
            default:
                return false;
        }
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
    public function insertMessage(
        string $bookingNo,
        string $message,
        int $senderId,
        int $adminId = 0,
        string $currencyCode = 'USD',
        int $productId = 0,
        int $receiverId = 0,
        string $status = 'Pending',
        string $subject = ''
    ): bool {
        $status = ucfirst(strtolower(trim($status)));

        // Basic field validation
        if (!$this->validateMessage($bookingNo, $message, $senderId)) {
            return false;
        }

        if (!in_array($status, ['Pending', 'Accept', 'Decline'], true)) {
            $status = 'Pending';
        }

        $insert = [
            'productId' => $productId > 0 ? $productId : 0,
            'bookingNo' => trim($bookingNo), // booking_id
            'senderId' => $senderId, // user_id
            'receiverId' => $receiverId > 0 ? $receiverId : 0,
            'admin_id' => $adminId > 0 ? $adminId : 0,
            'subject' => trim($subject),
            'message' => $message,
            'status' => $status,
            'msg_status' => 1,
            'currencycode' => strtoupper(trim($currencyCode))
        ];

        $this->db->trans_begin();
        $this->db->insert(MED_MESSAGE, $insert);

        if ($this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();

            return false;
        }

        $this->db->trans_commit();

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
        } while ($hasMoreMessages && count($allSelectedMessages) < $selectedLimit);

        $selectedCount = $allSelectedCount;
        $selectedMessages = $allSelectedMessages;

        return $allSelectedCount > 0;
    }

    /**
     * Checks if a user with the given ID exists in the database.
     *
     * If the user exists and the optional reference parameter
     * `$userName` is provided,
     * the method will assign the corresponding username to it.
     *
     * @param int $userId
     *     The user ID to look up. Must be a positive integer.
     * @param string|null &$userName
     *     Optional output parameter.
     *     If not null and the user is found,
     *     the corresponding username will be assigned
     *     to this variable.
     *
     * @return bool
     *     True if the user exists,
     *     false otherwise or if the ID is invalid.
     */
    final public function selectUserId(
        int $userId,
        ?string &$userName = null
    ): bool {
        if ($userId < 1) {
            return false;
        }

        $query = $this->db
            ->select('user_name')
            ->from(USERS)
            ->where('id', $userId)
            ->get();

        if ($query->num_rows() > 0) {
            if ($userName !== null) {
                $row = $query->row();
                $userName = $row->user_name;
            }

            return true;
        }

        return false;
    }

    /**
     * Checks if a user with the given username exists in the database.
     *
     * If the user exists and the optional reference parameter
     * `$userId` is provided,
     * the method will assign the corresponding user ID to it.
     *
     * @param string $userName
     *     The username to look up.
     *     Leading and trailing whitespace is trimmed.
     * @param int|null &$userId
     *     Optional output parameter.
     *     If not null and the user is found,
     *     the corresponding user ID will be assigned to this variable.
     *
     * @return bool
     *     True if the user exists,
     *     false otherwise or if the username is empty.
     */
    final public function selectUserName(
        string $userName,
        ?int &$userId = null
    ): bool {
        if (!($userName = trim($userName))) {
            return false;
        }

        $query = $this->db
            ->select('id')
            ->from(USERS)
            ->where('user_name', $userName)
            ->get();

        if ($query->num_rows() > 0) {
            if ($userId !== null) {
                $row = $query->row();
                $userId = (int)$row->id;
            }

            return true;
        }

        return false;
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
        if ($receiverId > -1 && $receiverId !== (int)$original['receiverId']) {
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
                && $actionFilter['msg_star_status'] !== $original['msg_star_status']
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
            if (!$this->validateMessage($bookingNo, $message, $senderId)) {
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
        $this->db->where('bookingNo', $expectedBookingNo)->delete(MED_MESSAGE);

        foreach ($messages as $msg) {
            if (!$this->validateMessage($bookingNo, $message, $senderId, $expectedBookingNo)) {
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
}
