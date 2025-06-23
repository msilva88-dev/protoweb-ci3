<?php

/*
 * Copyright (c) 2025, Marcio Delgado <marcio@libreware.info>
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
 * Class PwChatModelErrorEnum
 *
 * Centralized enumeration of error codes used across the PwChatModel.
 * These codes standardize how validation, database, and system-level
 * errors are reported and interpreted.
 *
 * The constants are designed to:
 *   - Replace magic numbers in error handling.
 *   - Support clear, predictable logic branching.
 *   - Enable integration with API response codes or logging systems.
 *
 * Grouped into:
 *   • 0–9: Core and unknown errors
 *   • 10–19: Data type and bounds validation
 *   • 20–29: Integer validation
 *   • 30–39: Float validation
 *   • 40–49: String and datetime validation
 *   • 50–59: Database-level errors
 *   • 60–69: User ID resolution errors
 *   • 70–79: User name resolution errors
 *
 * PHP version 7.4+ and CodeIgniter version 3.1.13+
 *
 * @author Marcio Delgado <marcio@libreware.info>
 * @copyright 2025 Marcio Delgado
 * @license BSD-2-Clause
 * @package Applications\Models
 * @since 2025
 * @subpackage Enum
 * @version 1.0
 */
class PwChatModelErrorEnum
{
    /**
     * Error code: No error occurred. Operation completed successfully.
     *
     * Indicates successful execution of a validation or operation.
     * This is the default state for any error output parameter.
     *
     * @var int
     */
    public const NONE = 0;

    /**
     * Error code: An unknown or unclassified error occurred.
     *
     * This value is used when no specific error category is applicable
     * or the error is unexpected.
     *
     * @var int
     */
    public const UNKNOWN = 1;

    /**
     * Error code:
     *     The provided data type is not supported by the validator.
     *
     * Common when the `$type` argument is unrecognized
     * or intentionally falls back to a default `switch` case.
     *
     * @var int
     */
    public const DTYPE_UNSUPPORTED = 10;

    /**
     * Error code:
     *     The maximum length or size is smaller than the minimum.
     *
     * Indicates a misconfiguration in validation bounds logic.
     *
     * @var int
     */
    public const DTYPE_BOUNDS_INVALID = 11;

    /**
     * Error code:
     *     The specified maximum bound is outside the allowed range.
     *
     * For example, exceeding data type limits
     * or implementation-defined caps.
     *
     * @var int
     */
    public const DTYPE_BOUND_MAX = 12;

    /**
     * Error code:
     *     The specified minimum bound is outside the allowed range.
     *
     * For example, being below system-defined or logic-defined minimums.
     *
     * @var int
     */
    public const DTYPE_BOUND_MIN = 13;

    /**
     * Error code:
     *    The actual value or data length is outside
     *    the permitted range.
     *
     * Used in string, BLOB, or numeric validation to flag size violations.
     *
     * @var int
     */
    public const DTYPE_RANGE = 14;

    /**
     * Error code: Invalid or unrecognized type for integer validation.
     *
     * Happens when `$type` is not compatible with integer checks
     * (example: "decimalMySql" used in float context).
     *
     * @var int
     */
    public const INT_DTYPE_INVALID = 20;

    /**
     * Error code:
     *     Invalid max size definition or type for integer validation.
     *
     * For example, passing a float or string instead of an int.
     *
     * @var int
     */
    public const INT_MAXSIZE_DTYPE = 21;

    /**
     * Error code:
     *     Invalid min size definition or type for integer validation.
     *
     * Usually indicates programmer error or logic misplacement.
     *
     * @var int
     */
    public const INT_MINSIZE_DTYPE = 22;

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
    public const INT_NONSTRING = 23;

    /**
     * Error code:
     *     The float value is either infinite (INF)
     *     or not-a-number (NaN).
     *
     * These values are invalid for most strict validations.
     *
     * @var int
     */
    public const FLOAT_INF_OR_NAN = 30;

    /**
     * Error code: The float has excessive decimal precision.
     *
     * For example, exceeding configured digit limits
     * like 18+ decimals.
     *
     * @var int
     */
    public const FLOAT_DIGITS = 31;

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
    public const STR_ENCODING = 40;

    /**
     * Error code:
     *     The input string does not match
     *     any allowed enumerated values.
     *
     * For example, not in ['Yes', 'No'] for an ENUM check.
     *
     * @var int
     */
    public const STR_ENUM_INVALID = 41;

    /**
     * Error code:
     *     The input string does not conform to expected
     *     date/time formats.
     *
     * Often used to validate timestamps or ISO 8601 strings.
     *
     * @var int
     */
    public const STR_DTIME_FORMAT = 42;

    /**
     * Error code: The database table is invalid or undefined.
     *
     * This may occur when a required table constant (example: USERS)
     * is not defined or incorrectly named.
     *
     * @var int
     */
    public const DB_TABLE_INVALID = 50;

    /**
     * Error code: The provided database data is invalid.
     *
     * This typically occurs when data does not match the
     * table schema or fails validation before query execution.
     *
     * @var int
     */
    public const DB_DATA_INVALID = 51;

    /**
     * Error code: Database ALTER operation failed.
     *
     * This is triggered when an ALTER TABLE statement fails,
     * possibly due to invalid syntax or constraint conflicts.
     *
     * @var int
     */
    public const DB_ALTER_FAILED = 52;

    /**
     * Error code: Database CREATE operation failed.
     *
     * This occurs when the model fails to create a required table
     * or index via SQL CREATE commands.
     *
     * @var int
     */
    public const DB_CREATE_FAILED = 53;

    /**
     * Error code: Database DROP operation failed.
     *
     * This is set when a DROP TABLE or DROP INDEX command
     * fails due to permission issues or nonexistence.
     *
     * @var int
     */
    public const DB_DROP_FAILED = 54;

    /**
     * Error code: Database TRUNCATE operation failed.
     *
     * Occurs when TRUNCATE TABLE fails, often due to
     * foreign key constraints or insufficient privileges.
     *
     * @var int
     */
    public const DB_TRUNCATE_FAILED = 55;

    /**
     * Error code: Database DELETE operation failed.
     *
     * Triggered when an attempt to delete rows fails,
     * commonly due to missing records, foreign key constraints,
     * or engine-level issues.
     *
     * @var int
     */
    public const DB_DELETE_FAILED = 56;

    /**
     * Error code: Database INSERT operation failed.
     *
     * Set when INSERT completes but no rows are affected,
     * typically due to duplicate keys, silent failure,
     * or database configuration issues.
     *
     * @var int
     */
    public const DB_INSERT_FAILED = 57;

    /**
     * Error code: Database SELECT operation failed.
     *
     * This occurs when a SELECT query fails due to syntax errors,
     * inaccessible tables, or invalid field references.
     *
     * @var int
     */
    public const DB_SELECT_FAILED = 58;

    /**
     * Error code: Database UPDATE operation failed.
     *
     * Raised when an UPDATE query executes but no rows are changed,
     * or fails due to permission, constraint, or SQL-level issues.
     *
     * @var int
     */
    public const DB_UPDATE_FAILED = 59;

    /**
     * Error code: More than one record matched the user ID.
     *
     * Indicates a data integrity issue; `id` should be unique.
     *
     * @var int
     */
    public const DB_UID_DUPLICATE = 60;

    /**
     * Error code: The user ID retrieved from the database is invalid.
     *
     * For example, not a valid BIGINT string or format mismatch.
     *
     * @var int
     */
    public const DB_UID_INVALID = 61;

    /**
     * Error code: The user ID was not found in the database.
     *
     * Common in identity resolution, lookup, or validation queries.
     *
     * @var int
     */
    public const DB_UID_NOT_FOUND = 62;

    /**
     * Error code: More than one user has the same user name.
     *
     * Indicates a uniqueness violation; `user_name` should be unique.
     *
     * @var int
     */
    public const DB_UNAME_DUPLICATE = 70;

    /**
     * Error code:
     *     The user name retrieved from the database is invalid.
     *
     * Used when the result is not a proper string
     * or fails string validation.
     *
     * @var int
     */
    public const DB_UNAME_INVALID = 71;

    /**
     * Error code: No user was found with the given user name.
     *
     * Indicates a lookup failure
     * or that the name does not exist in the database.
     *
     * @var int
     */
    public const DB_UNAME_NOT_FOUND = 72;

    /**
     * Private constructor to prevent instantiation.
     *
     * This static class holds only constants
     * and should not be instantiated.
     * Enforces enum-like behavior.
     */
    private function __construct()
    {
        // Static-only class
    }
}
