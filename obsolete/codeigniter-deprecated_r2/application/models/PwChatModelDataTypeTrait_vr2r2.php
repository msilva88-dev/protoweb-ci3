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
 * Trait PwChatModelDataTypeTrait
 *
 * Provides internal validation and normalization routines
 * for data types commonly used in chat models, including:
 * integers, strings, floats, timestamps, and enums.
 *
 * Designed to enforce SQL compatibility (MySQL, PostgreSQL, SQLite)
 * and strict bounds checking for multi-platform chat systems.
 *
 * This trait is intended for internal use in models
 * like `PwChatModel`, offering reusable helpers for:
 * - Integer range and format checks (BIGINT, INT, etc.)
 * - String encoding, length, and enum validation
 * - Float precision and NaN/INF handling
 * - Date/time format validation (ISO 8601, SQL DATETIME)
 * - General bounds validation for typed fields
 *
 * PHP version 7.4+ and CodeIgniter version 3.1.13+
 *
 * @author Marcio Delgado <marcio@libreware.info>
 * @copyright 2025 Marcio Delgado
 * @license BSD-2-Clause
 * @package Applications\Models
 * @since 2025
 * @subpackage Trait
 * @version 1.0
 */
trait PwChatModelDataTypeTrait
{
    /**
     * Maximum value for an 8-bit signed binary data (2^7 - 1).
     *
     * 8-bit signed binary data max value (127).
     * (example: 7 bits integer, 127 bytes string).
     *
     * @var int
     */
    protected const MAX_BIN8 = (2 ** 7) - 1;

    /**
     * Maximum value for a 11-bit signed binary data (2^10 - 1).
     *
     * 11-bit signed binary data max value (1,023).
     * (example: 1.25 byte integer, 1 KiB string).
     *
     * @var int
     */
    protected const MAX_BIN11 = (2 ** 10) - 1;

    /**
     * Maximum value for a 16-bit signed binary data (2^15 - 1).
     *
     * 16-bit signed binary data max value (32,767).
     * (example: 1.875 byte integer, 32 KiB string).
     *
     * @var int
     */
    protected const MAX_BIN16 = (2 ** 15) - 1;

    /**
     * Maximum value for a 24-bit signed binary data (2^23 - 1).
     *
     * 24-bit signed binary data max value (8,388,607).
     * (example: 2.875 bytes integer, 8 MiB string).
     *
     * @var int
     */
    protected const MAX_BIN24 = (2 ** 23) - 1;

    /**
     * Maximum value for a 32-bit signed binary data (2^31 - 1).
     *
     * 32-bit signed binary data max value (2,147,483,647).
     * (example: 3.875 bytes integer, 2 GiB string).
     *
     * @var int
     */
    protected const MAX_BIN32 = (2 ** 31) - 1;

    /**
     * Maximum value for a 53-bit signed binary data (2^52 - 1).
     *
     * 53-bit signed binary data max value (4,503,599,627,370,495).
     * (example: 6.5 bytes integer, 8 PiB string).
     *
     * @var int
     */
    protected const MAX_BIN53 = (2 ** 52) - 1;

    /**
     * Maximum value for a 64-bit signed binary data (2^63 - 1).
     *
     * 64-bit signed binary data max value (9,223,372,036,854,775,807).
     * (example: 7.875 bytes integer, 8 EiB string).
     *
     * @var int
     */
    protected const MAX_BIN64 = (2 ** 63) - 1;

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
    protected const MAX_BIN64_STR = '9223372036854775807';

    /**
     * Maximum value for an 1-bit unsigned binary data (2^1 - 1).
     *
     * 1-bit unsigned binary data max value (1).
     * (example: 1 bit integer, 1 byte string).
     *
     * @var int
     */
    protected const MAX_UBIN1 = (2 ** 1) - 1;

    /**
     * Maximum value for an 6-bit unsigned binary data (2^6 - 1).
     *
     * 6-bit unsigned binary data max value (63).
     * (example: 6 bits integer, 63 bytes string).
     *
     * @var int
     */
    protected const MAX_UBIN6 = (2 ** 6) - 1;

    /**
     * Maximum value for an 8-bit unsigned binary data (2^8 - 1).
     *
     * 8-bit unsigned binary data max value (255).
     * (example: 1 byte integer, 255 bytes string).
     *
     * @var int
     */
    protected const MAX_UBIN8 = (2 ** 8) - 1;

    /**
     * Maximum value for an 10-bit unsigned binary data (2^10 - 1).
     *
     * 10-bit unsigned binary data max value (1023).
     * (example: 1.25 byte integer, 1023 bytes string).
     *
     * @var int
     */
    protected const MAX_UBIN10 = (2 ** 10) - 1;

    /**
     * Maximum value for a 14-bit unsigned binary data (2^14 - 1).
     *
     * 14-bit unsigned binary data max value (16,383).
     * (example: 1.75 byte integer, 16 KiB string).
     *
     * @var int
     */
    protected const MAX_UBIN14 = (2 ** 14) - 1;

    /**
     * Maximum value for a 16-bit unsigned binary data (2^16 - 1).
     *
     * 16-bit unsigned binary data max value (65,535).
     * (example: 2 bytes integer, 64 KiB string).
     *
     * @var int
     */
    protected const MAX_UBIN16 = (2 ** 16) - 1;

    /**
     * Maximum value for a 20-bit unsigned binary data (2^20 - 1).
     *
     * 20-bit unsigned binary data max value (1,048,575).
     * (example: 2.5 bytes integer, 1 MiB string).
     *
     * @var int
     */
    protected const MAX_UBIN20 = (2 ** 20) - 1;

    /**
     * Maximum value for a 24-bit unsigned binary data (2^24 - 1).
     *
     * 24-bit unsigned binary data max value (16,777,215).
     * (example: 3 bytes integer, 16 MiB string).
     *
     * @var int
     */
    protected const MAX_UBIN24 = (2 ** 24) - 1;

    /**
     * Maximum value for a 30-bit unsigned binary data (2^30 - 1).
     *
     * 30-bit unsigned binary data max value (1,073,741,823).
     * (example: 3.75 bytes integer, 1 GiB string).
     *
     * @var int
     */
    protected const MAX_UBIN30 = (2 ** 30) - 1;

    /**
     * Maximum value for a 31-bit unsigned binary data (2^31 - 1).
     *
     * 31-bit unsigned binary data max value (2,147,483,647).
     * (example: 3.875 bytes integer, 2 GiB string).
     *
     * @var int
     */
    protected const MAX_UBIN31 = (2 ** 31) - 1;

    /**
     * Maximum value for a 32-bit unsigned binary data (2^32 - 1).
     *
     * 32-bit unsigned binary data max value (4,294,967,295).
     * (example: 4 bytes integer, 4 GiB string).
     *
     * @var int
     */
    protected const MAX_UBIN32 = (2 ** 32) - 1;

    /**
     * Maximum value for a 64-bit unsigned binary data (2^64 - 1).
     *
     * 64-bit unsigned binary data max value
     * (18,446,744,073,709,551,615).
     * (example: 8 bytes integer, 16 EiB string).
     *
     * @var int
     */
    protected const MAX_UBIN64 = (2 ** 64) - 1;

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
    protected const MAX_UBIN64_STR = '18446744073709551615';

    /**
     * Minimum value for an 8-bit signed binary data (-2^7).
     *
     * 8-bit signed binary data min value (-128).
     * (example: -7 bits integer, -128 bytes string).
     *
     * @var int
     */
    protected const MIN_BIN8 = (2 ** 7) - 1;

    /**
     * Minimum value for a 11-bit signed binary data (-2^10).
     *
     * 11-bit signed binary data min value (-1,024).
     * (example: -1.25 byte integer, -1 KiB string).
     *
     * @var int
     */
    protected const MIN_BIN11 = -(2 ** 10);

    /**
     * Minimum value for a 16-bit signed binary data (-2^15).
     *
     * 16-bit signed binary data min value (-32,768).
     * (example: -1.875 byte integer, -32 KiB string).
     *
     * @var int
     */
    protected const MIN_BIN16 = -(2 ** 15);

    /**
     * Minimum value for a 24-bit signed binary data (-2^23).
     *
     * 24-bit signed binary data min value (-8,388,608).
     * (example: -2.875 bytes integer, -8 MiB string).
     *
     * @var int
     */
    protected const MIN_BIN24 = -(2 ** 23);

    /**
     * Minimum value for a 32-bit signed binary data (-2^31).
     *
     * 32-bit signed binary data min value (-2,147,483,648).
     * (example: -3.875 bytes integer, -2 GiB string).
     *
     * @var int
     */
    protected const MIN_BIN32 = -(2 ** 31);

    /**
     * Minimum value for a 53-bit signed binary data (-2^52).
     *
     * 53-bit signed binary data min value (-4,503,599,627,370,496).
     * (example: -6.5 bytes integer, -8 PiB string).
     *
     * @var int
     */
    protected const MIN_BIN53 = -(2 ** 52);

    /**
     * Minimum value for a 64-bit signed binary data (-2^63).
     *
     * 64-bit signed binary data min value
     * (-9,223,372,036,854,775,808).
     * (example: -7.875 bytes integer, -8 EiB string).
     *
     * @var int
     */
    protected const MIN_BIN64 = -(2 ** 63);

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
    protected const MIN_BIN64_STR = '-9223372036854775808';

    /**
     * Minimum value for an unsigned data (0).
     *
     * unsigned data min value (0).
     * (example: 0 bit integer, 0 byte string).
     *
     * @var int
     */
    protected const MIN_U = 0;

    /**
     * Counts the number of elements in an array using BCMath,
     * with an upper bound limit defined by MAX_UBIN31.
     *
     * This method performs a manual count using `bcadd()` to ensure
     * compatibility with very large arrays or when integer overflows
     * may occur (example: on 32-bit systems).
     *
     * If the count exceeds MAX_UBIN31,
     * returns '-1' to indicate overflow.
     *
     * Always returns a string representing the total count.
     *
     * @param array $array
     *     The array to count.
     *
     * @return string
     *     Number of elements as a string, or '-1' if overflow occurs.
     */
    final protected function bcCount(array $array): string {
        $count = '0';

        foreach ($array as $_) {
            if ($count === (string)self::MAX_UBIN31) {
                return '-1';
            }

            $count = bcadd($count, '1', 0);
        }

        return $count;
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
     *     Output error code if validation fails.
     *
     * @return bool
     *     True if data is valid for the specified type and bounds;
     *     false otherwise.
     */
    final protected function validDTypeBlob(
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
                /*
                 * Enforces fixed byte-length for BINARY(n).
                 *
                 * @internal
                 * BINARY(n) in MySQL stores
                 * a string as exactly n bytes,
                 * right-padded with \x00 if shorter.
                 * Unlike VARBINARY, the size must match exactly.
                 * This block ensures that minLength and maxLength
                 * are equal before proceeding.
                 */
                if ($minLength !== $maxLength) {
                    $error = $this->logError(DTYPE_BOUNDS_INVALID);

                    return false;
                }

                // BINARY(n) must have equal min/max length
                $byteMinLength = self::MAX_UBIN8;

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
                $error =
                    $this->logError(PwChatModelErrorEnum::DTYPE_UNSUPPORTED);

                return false;
        }

        // Check if maximum length is less than minimum length
        if ($maxLength > 0) {
            if ($maxLength < $minLength) {
                $error = $this->logError(
                    PwChatModelErrorEnum::DTYPE_BOUNDS_INVALID
                );

                return false;
            }
        }

        // Optional override of maximum length (within SQL BLOB limits)
        if ($maxLength > 0) {
            if ($maxLength > $byteMaxLength || $maxLength < self::MIN_U) {
                $error =
                    $this->logError(PwChatModelErrorEnum::DTYPE_BOUND_MAX);

                return false; // Exceeds BLOB type's capacity
            }

            $byteMaxLength = $maxLength;
        }

        // Optional override of minimum length (within SQL TEXT limits)
        if ($minLength > self::MIN_U) {
            if ($minLength > $byteMinLength || $minLength < self::MIN_U) {
                $error =
                    $this->logError(PwChatModelErrorEnum::DTYPE_BOUND_MIN);

                return false; // Exceeds BLOB type's capacity
            }

            $byteMinLength = $minLength;
        }

        // Range check
        if ($byteLength < $byteMinLength || $byteLength > $byteMaxLength) {
            $error = $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

            return false;
        }

        $error = $this->logError(PwChatModelErrorEnum::NONE);

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
     *     Output error code if validation fails.
     *
     * @return bool
     *     True if data is valid for the specified type, bounds,
     *     and (if safe) precision; false otherwise.
     */
    final protected function validDTypeFloat(
        float $data,
        bool $safe = true,
        string $type = 'double',
        ?float $maxSize = null,
        ?float $minSize = null,
        ?int &$error = null
    ): bool {
        // Reject NaN and Infinity
        if ($safe && !is_finite($data)) {
            $error = $this->logError(PwChatModelErrorEnum::FLOAT_INF_OR_NAN);

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
                $error =
                    $this->logError(PwChatModelErrorEnum::DTYPE_UNSUPPORTED);

                return false;
        }

        // Check if maximum bound is less than minimum bound
        if ($maxSize !== null && $minSize !== null) {
            if ($maxSize < $minSize) {
                $error = $this->logError(
                    PwChatModelErrorEnum::DTYPE_BOUNDS_INVALID
                );

                return false;
            }
        }

        // Custom maximum bounds
        if ($maxSize !== null) {
            if ($maxSize <= $bitMinSize || $maxSize > $bitMaxSize) {
                $error =
                    $this->logError(PwChatModelErrorEnum::DTYPE_BOUND_MAX);

                return false;
            }

            $bitMaxSize = $maxSize;
        }

        // Custom minimum bounds
        if ($minSize !== null) {
            if ($minSize < $bitMinSize || $minSize >= $bitMaxSize) {
                $error =
                    $this->logError(PwChatModelErrorEnum::DTYPE_BOUND_MIN);

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
                $error = $this->logError(PwChatModelErrorEnum::FLOAT_DIGITS);

                return false;
            }
        }

        // Range check
        if ($data < $bitMinSize || $data > $bitMaxSize) {
            $error = $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

            return false;
        }

        $error = $this->logError(PwChatModelErrorEnum::NONE);

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
     *     Output error code if validation fails.
     * @param string &$phpType
     *     Output PHP type as determined
     *     ('int', 'float', 'string', or 'unsupported').
     *
     * @return bool
     *     True if data is valid for the specified type and bounds;
     *     false otherwise.
     */
    final protected function validDTypeInt(
        $data,
        string $type = 'int',
        $maxSize = null,
        $minSize = null,
        string $encoding = '',
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
                $error =
                    $this->logError(PwChatModelErrorEnum::INT_MAXSIZE_DTYPE);

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
                $error =
                    $this->logError(PwChatModelErrorEnum::INT_MINSIZE_DTYPE);

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
                $error =
                    $this->logError(PwChatModelErrorEnum::INT_MAXSIZE_DTYPE);

                return false;
            }

            if (
                $minSize !== null
                && (
                    !is_string($minSize)
                    || !preg_match('/^-?\d+$/', $minSize)
                )
            ) {
                $error =
                    $this->logError(PwChatModelErrorEnum::INT_MINSIZE_DTYPE);

                return false;
            }
        } else {
            $error = $this->logError(PwChatModelErrorEnum::INT_DTYPE_INVALID);
            $phpType = 'unsupported';

            return false;
        }

        /*
         * Adjustment for arbitrary integer value
         * (string-based, exceeding PHP_INT_MAX).
         */
        if ($phpType === 'string') {
            if ($encoding === '') {
                if ($type === 'varCharLegacy') {
                    $encoding = 'ISO-8859-1';
                } elseif ($type === 'stringJs') {
                    $encoding = 'UTF-16';
                } else {
                    $encoding = 'UTF-8';
                }
            }

            if ($type === 'stringJs' && $encoding !== 'UTF-16') {
                $error = $this->logError(PwChatModelErrorEnum::STR_ENCODING);

                return false;
            }

            if (!mb_check_encoding($data, $encoding)) {
                $error = $this->logError(PwChatModelErrorEnum::STR_ENCODING);

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
                            $error = $this->logError(
                                PwChatModelErrorEnum::DTYPE_BOUND_MAX
                            );

                            return false;
                        }

                        $maxSize = bcsub(bcpow('2', $maxSize), '1');
                    } else {
                        if (
                            $maxSize < self::MAX_UBIN1
                            || $maxSize > self::MAX_UBIN6 + 1
                        ) {
                            $error = $this->logError(
                                PwChatModelErrorEnum::DTYPE_BOUND_MAX
                            );

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
                            $error = $this->logError(
                                PwChatModelErrorEnum::DTYPE_BOUND_MIN
                            );

                            return false;
                        }

                        $minSize = bcsub(bcpow('2', $minSize), '1');
                    } else {
                        if (
                            $minSize < self::MAX_UBIN1
                            || $minSize > self::MAX_UBIN6 + 1
                        ) {
                            $error = $this->logError(
                                PwChatModelErrorEnum::DTYPE_BOUND_MIN
                            );

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
                    $error =
                        $this->logError(PwChatModelErrorEnum::INT_NONSTRING);
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
                    $error =
                        $this->logError(PwChatModelErrorEnum::INT_NONSTRING);
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
                if ($phpType === 'string') {
                    $maxDefLen = self::MAX_UBIN8;
                    $maxStrLen = $maxSize !== null
                        ? mb_strlen($maxSize, $encoding)
                        : null;
                    $minStrLen = $minSize !== null
                        ? mb_strlen($minSize, $encoding)
                        : null;

                    /*
                     * Ensures the minimum value matches
                     * the expected character length.
                     *
                     * @internal
                     * This length check is specific
                     * to CHAR(n)-like types,
                     * where fixed-width character encoding
                     * is enforced.
                     * Format and numeric comparisons
                     * are validated globally
                     * before and after the switch-case,
                     * so this block only verifies
                     * that $minSize visually fits the CHAR(n)
                     * constraint
                     * (example: rejects '-9' vs '9' in CHAR(1)).
                     */
                    if ($minSize === null && $maxSize !== null) {
                        /*
                         * Define default $minSize
                         * from length of $maxSize.
                         */
                        if ($maxStrLen === 1) {
                            // Set '0' as minimal value
                            $minSize = '0';
                        } else {
                            /*
                             * '-' is counted as a character
                             * in string length
                             */
                            $minSize = '-' . str_repeat('9', $maxStrLen - 1);
                        }
                    } else if ($minSize !== null && $maxSize === null) {
                        if ($minStrLen !== $maxDefLen) {
                            $error = $this->logError(DTYPE_BOUNDS_INVALID);

                            return false;
                        }
                    } else if ($minSize !== null && $maxSize !== null) {
                        if ($minStrLen !== $maxStrLen) {
                            $error = $this->logError(DTYPE_BOUNDS_INVALID);

                            return false;
                        }
                    }
                }

                // Fallthrough
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
                        $error = $this->logError(
                            PwChatModelErrorEnum::DTYPE_RANGE
                        );

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
                if ($phpType === 'string') {
                    $maxDefLen = self::MAX_UBIN20 - self::MAX_UBIN10;
                    $maxStrLen = $maxSize !== null
                        ? mb_strlen($maxSize, $encoding)
                        : null;
                    $minStrLen = $minSize !== null
                        ? mb_strlen($minSize, $encoding)
                        : null;

                    /*
                     * Ensures the minimum value matches
                     * the expected character length.
                     *
                     * @internal
                     * This length check is specific
                     * to CHAR(n)-like types,
                     * where fixed-width character encoding
                     * is enforced.
                     * Format and numeric comparisons
                     * are validated globally
                     * before and after the switch-case,
                     * so this block only verifies
                     * that $minSize visually fits the CHAR(n)
                     * constraint
                     * (example: rejects '-9' vs '9' in CHAR(1)).
                     */
                    if ($minSize === null && $maxSize !== null) {
                        /*
                         * Define default $minSize
                         * from length of $maxSize.
                         */
                        if ($minStrLen === 1) {
                            // Set '0' as minimal value
                            $minSize = '0';
                        } else {
                            /*
                             * '-' is counted as a character
                             * in string length
                             */
                            $minSize = '-' . str_repeat('9', $maxStrLen - 1);
                        }
                    } else if ($minSize !== null && $maxSize === null) {
                        if ($minStrLen !== $maxDefLen) {
                            $error = $this->logError(DTYPE_BOUNDS_INVALID);

                            return false;
                        }
                    } else if ($minSize !== null && $maxSize !== null) {
                        if ($minStrLen !== $maxStrLen) {
                            $error = $this->logError(DTYPE_BOUNDS_INVALID);

                            return false;
                        }
                    }
                }

                // Fallthrough
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
                /*
                 * Comparison between $minSize
                 * and $maxSize for bit length in BIT(n) is unneeded
                 * because value is decimal.
                 */
            case 'varBitPgSql':
                if ($phpType === 'string') {
                    // Adjust maximum bounds
                    if ($maxSize !== null) {
                        if (
                            bccomp($maxSize, (string)self::MAX_UBIN1) < 0
                            || bccomp($maxSize, bcpow('10', '7')) > 0
                        ) {
                            $error = $this->logError(
                                PwChatModelErrorEnum::DTYPE_BOUND_MAX
                            );

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
                            $error = $this->logError(
                                PwChatModelErrorEnum::DTYPE_BOUND_MIN
                            );

                            return false;
                        }

                        $minSize = bcsub(bcpow('2', $minSize), '1');
                    }

                    // Count as integer using BCMath (string-based)
                    $countMaxSize = bcsub(bcpow('2', '30'), '1');
                    $countMinSize = (string)self::MAX_U;

                    break;
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

                $error = $this->logError(PwChatModelErrorEnum::INT_NONSTRING);
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
                $error =
                    $this->logError(PwChatModelErrorEnum::DTYPE_UNSUPPORTED);
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
                    $error = $this->logError(
                        PwChatModelErrorEnum::DTYPE_BOUNDS_INVALID
                    );

                    return false;
                }
            } else {
                if ($maxSize < $minSize) {
                    $error = $this->logError(
                        PwChatModelErrorEnum::DTYPE_BOUNDS_INVALID
                    );

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
                    $error = $this->logError(
                        PwChatModelErrorEnum::DTYPE_BOUND_MAX
                    );

                    return false;
                }
            } else {
                if ($maxSize <= $countMinSize || $maxSize > $countMaxSize) {
                    $error = $this->logError(
                        PwChatModelErrorEnum::DTYPE_BOUND_MAX
                    );

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
                    $error = $this->logError(
                        PwChatModelErrorEnum::DTYPE_BOUND_MIN
                    );

                    return false;
                }
            } else {
                if ($minSize < $countMinSize || $minSize >= $countMaxSize) {
                    $error = $this->logError(
                        PwChatModelErrorEnum::DTYPE_BOUND_MIN
                    );

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
                $error = $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

                return false;
            }
        // Native int/float range check
        } else {
            if ($dataSize < $countMinSize || $dataSize > $countMaxSize) {
                $error = $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

                return false;
            }
        }

        $error = $this->logError(PwChatModelErrorEnum::NONE);

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
     *     Output error code if validation fails.
     *
     * @return bool
     *     True if data is valid for the specified type and bounds;
     *     false otherwise.
     */
    final protected function validDTypeStr(
        string $data,
        string $type = 'text',
        int $maxLength = 0,
        int $minLength = 0,
        string $encoding = '',
        ?int &$error = null
    ): bool {
        if ($encoding === '') {
            if ($type === 'varCharLegacy') {
                $encoding = 'ISO-8859-1';
            } elseif ($type === 'stringJs') {
                $encoding = 'UTF-16';
            } else {
                $encoding = 'UTF-8';
            }
        }

        if ($type === 'stringJs' && $encoding !== 'UTF-16') {
            $error = $this->logError(PwChatModelErrorEnum::STR_ENCODING);

            return false;
        }

        if (!mb_check_encoding($data, $encoding)) {
            $error = $this->logError(PwChatModelErrorEnum::STR_ENCODING);

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
                /*
                 * Enforces fixed character length for CHAR(n).
                 *
                 * @internal
                 * CHAR(n) in MySQL stores a string
                 * as exactly n characters,
                 * right-padded with spaces if shorter.
                 * Unlike VARCHAR, the size must match exactly.
                 * This block ensures that minLength and maxLength
                 * are equal before proceeding.
                 */
                if ($minLength !== $maxLength) {
                    $error = $this->logError(DTYPE_BOUNDS_INVALID);

                    return false;
                }

                // CHAR(n) must have equal min/max length
                $countMinLength = self::MAX_UBIN8;

                // Fallthrough
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
                    $error =
                        $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

                    return false;
                }

                break;
            // MySQL TEXT
            case 'text':
                $countMaxLength = self::MAX_UBIN16;

                break;
            /*
             * PostgreSQL CHAR:
             *     Min is 1 and max is 1,047,552 characters.
             */
            case 'charPgSql':
                /*
                 * Enforces fixed character length for CHAR(n).
                 *
                 * @internal
                 * CHAR(n) in MySQL stores a string
                 * as exactly n characters,
                 * right-padded with spaces if shorter.
                 * Unlike VARCHAR, the size must match exactly.
                 * This block ensures that minLength and maxLength
                 * are equal before proceeding.
                 */
                if ($minLength !== $maxLength) {
                    $error = $this->logError(DTYPE_BOUNDS_INVALID);

                    return false;
                }

                // Fallthrough
            /*
             * PostgreSQL VARCHAR:
             *     Min is 1 and max is 1,047,552 characters.
             */
            case 'varCharPgSql':
                // Count by character length
                $countLength = $charLength;
                $countMaxLength = self::MAX_UBIN20 - self::MAX_UBIN10;
                $countMinLength = $type !== 'charPgSql'
                    // The minimal value of VARCHAR
                    ? self::MAX_UBIN1
                    // CHAR(n) must have equal min/max length
                    : $countMaxLength;

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
                $error =
                    $this->logError(PwChatModelErrorEnum::DTYPE_UNSUPPORTED);

                return false;
        }

        // Check if maximum length is less than minimum length
        if ($maxLength > self::MIN_U) {
            if ($maxLength < $minLength) {
                $error = $this->logError(
                    PwChatModelErrorEnum::DTYPE_BOUNDS_INVALID
                );

                return false;
            }
        }

        // Optional override of maximum length (within SQL TEXT limits)
        if ($maxLength > self::MIN_U) {
            if ($maxLength > $countMaxLength || $maxLength < self::MIN_U) {
                $error =
                     $this->logError(PwChatModelErrorEnum::DTYPE_BOUND_MAX);

                return false; // Exceeds TEXT type's capacity
            }

            $countMaxLength = $maxLength;
        }

        // Optional override of minimum length (within SQL TEXT limits)
        if ($minLength > self::MIN_U) {
            if ($minLength > $countMinLength || $minLength < self::MIN_U) {
                $error =
                    $this->logError(PwChatModelErrorEnum::DTYPE_BOUND_MIN);

                return false; // Exceeds TEXT type's capacity
            }

            $countMinLength = $minLength;
        }

        // Range check
        if (
            $countLength < $countMinLength
            || $countLength > $countMaxLength
        ) {
            $error = $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

            return false;
        }

        $error = $this->logError(PwChatModelErrorEnum::NONE);

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
     *     Output error code if validation fails.
     *
     * @return bool
     *     True if the timestamp is valid for the given type;
     *     false otherwise.
     */
    final protected function validDTypeTStampStr(
        string $value,
        string $type = 'datetime',
        ?int &$error = null
    ): bool {
        if (
            !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)
        ) {
            $error = $this->logError(PwChatModelErrorEnum::STR_DTIME_FORMAT);

            return false;
        }

        if (($timestamp = strtotime($value)) === false) {
            $error = $this->logError(PwChatModelErrorEnum::STR_DTIME_FORMAT);

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
                    $error =
                        $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

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
                    $error =
                        $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

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
                    $error =
                        $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

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
                    $error =
                        $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

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
                    $error =
                        $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

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
                    $error =
                        $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

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
                    $error =
                        $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

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
                    $error =
                        $this->logError(PwChatModelErrorEnum::DTYPE_RANGE);

                    return false;
                }

                break;
            case 'text':
                break;
            default:
                $error =
                    $this->logError(PwChatModelErrorEnum::DTYPE_UNSUPPORTED);

                return false;
        }

        $error = $this->logError(PwChatModelErrorEnum::NONE);

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
     *     Output error code if validation fails.
     *
     * @return bool
     *     True if all provided fields pass validation;
     *     false if any field fails.
     */
    final protected function validMsgData(
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
                $error =
                    $this->logError(PwChatModelErrorEnum::STR_ENUM_INVALID);

                return false;
            }
        }

        $error = $this->logError(PwChatModelErrorEnum::NONE);

        return true;
    }
}
