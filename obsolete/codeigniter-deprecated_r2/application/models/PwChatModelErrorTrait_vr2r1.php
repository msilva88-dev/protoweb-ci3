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
 * Trait PwChatModelErrorTrait
 *
 * Logs errors originating from PwChatModel
 * using standardized error codes.
 * Supports logging to CodeIgniter (`log_message()`),
 * to STDERR (`fwrite()`), or both simultaneously.
 *
 * Intended for internal model-level use only.
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
trait PwChatModelErrorTrait
{
    /**
     * Logs the given error using the selected output mode.
     *
     * Accepts an integer error code defined by PwChatModelErrorEnum,
     * and logs a human-readable message
     * to the selected output channel(s).
     *
     * Valid output modes:
     * - `'ci'` → Uses CodeIgniter's log_message('error', ...)
     * - `'php'` → Sends message to STDERR using fwrite()
     * - `'both'` → Logs to both CodeIgniter and STDERR
     *
     * If the error code is PwChatModelErrorEnum::NONE,
     * this function performs no action and returns immediately.
     *
     * @param int $code
     *     Error code constant defined in PwChatModelErrorEnum.
     * @param string $mode
     *     Logging destination: 'ci', 'php', or 'both'.
     *     Defaults to 'both'.
     *
     * @return int
     *     The numeric error code that was processed and logged.
     */
    final protected function logError(
        int $code,
        string $mode = 'both'
    ): int {
        switch ($code) {
            case PwChatModelErrorEnum::NONE:
                return $code;
            case PwChatModelErrorEnum::UNKNOWN:
                $log = 'Unknown or unspecified error.';

                break;
            case PwChatModelErrorEnum::DTYPE_UNSUPPORTED:
                $log = 'Unsupported data type.';

                break;
            case PwChatModelErrorEnum::DTYPE_BOUNDS_INVALID:
                $log = 'Invalid bounds configuration (max < min).';

                break;
            case PwChatModelErrorEnum::DTYPE_BOUND_MAX:
                $log = 'Maximum bound is too large.';

                break;
            case PwChatModelErrorEnum::DTYPE_BOUND_MIN:
                $log = 'Minimum bound is too small.';

                break;
            case PwChatModelErrorEnum::DTYPE_RANGE:
                $log = 'Value is outside allowed range.';

                break;
            case PwChatModelErrorEnum::INT_DTYPE_INVALID:
                $log = 'Invalid type for integer validation.';

                break;
            case PwChatModelErrorEnum::INT_MAXSIZE_DTYPE:
                $log = 'Invalid max size type for integer.';

                break;
            case PwChatModelErrorEnum::INT_MINSIZE_DTYPE:
                $log = 'Invalid min size type for integer.';

                break;
            case PwChatModelErrorEnum::INT_NONSTRING:
                $log = 'Integer not passed as string (unsafe).';

                break;
            case PwChatModelErrorEnum::FLOAT_INF_OR_NAN:
                $log = 'Float is INF or NaN.';

                break;
            case PwChatModelErrorEnum::FLOAT_DIGITS:
                $log = 'Float has excessive precision.';

                break;
            case PwChatModelErrorEnum::STR_ENCODING:
                $log = 'Invalid string encoding.';

                break;
            case PwChatModelErrorEnum::STR_ENUM_INVALID:
                $log = 'Value not in allowed string ENUM.';

                break;
            case PwChatModelErrorEnum::STR_DTIME_FORMAT:
                $log = 'Invalid date/time string format.';

                break;
            case PwChatModelErrorEnum::DB_TABLE_INVALID:
                $log = 'Invalid or undefined database table.';

                break;
            case PwChatModelErrorEnum::DB_DATA_INVALID:
                $log = 'Invalid or undefined database data.';

                break;
            case PwChatModelErrorEnum::DB_ALTER_FAILED:
                $log = 'Database ALTER operation failed.';

                break;
            case PwChatModelErrorEnum::DB_CREATE_FAILED:
                $log = 'Database CREATE operation failed.';

                break;
            case PwChatModelErrorEnum::DB_DROP_FAILED:
                $log = 'Database DROP operation failed.';

                break;
            case PwChatModelErrorEnum::DB_TRUNCATE_FAILED:
                $log = 'Database TRUNCATE operation failed.';

                break;
            case PwChatModelErrorEnum::DB_DELETE_FAILED:
                $log = 'Database DELETE operation failed.';

                break;
            case PwChatModelErrorEnum::DB_INSERT_FAILED:
                $log = 'Database INSERT operation failed.';

                break;
            case PwChatModelErrorEnum::DB_SELECT_FAILED:
                $log = 'Database SELECT operation failed.';

                break;
            case PwChatModelErrorEnum::DB_UPDATE_FAILED:
                $log = 'Database UPDATE operation failed.';

                break;
            case PwChatModelErrorEnum::DB_UID_DUPLICATE:
                $log = 'Duplicate user ID found in database.';

                break;
            case PwChatModelErrorEnum::DB_UID_INVALID:
                $log = 'Invalid user ID format from database.';

                break;
            case PwChatModelErrorEnum::DB_UID_NOT_FOUND:
                $log = 'User ID not found in database.';

                break;
            case PwChatModelErrorEnum::DB_UNAME_DUPLICATE:
                $log = 'Duplicate user name found in database.';

                break;
            case PwChatModelErrorEnum::DB_UNAME_INVALID:
                $log = 'Invalid user name format from database.';

                break;
            case PwChatModelErrorEnum::DB_UNAME_NOT_FOUND:
                $log = 'User name not found in database.';

                break;
            default:
                $log = 'Unmapped error code.';
        }

        $msg = 'Protoweb CI3 Model Error: ' . $log . ' [' . $code . ']';

        switch ($mode) {
        case 'ci':
            log_message('error', $msg);

            break;
        case 'php':
            fwrite(STDERR, $msg . PHP_EOL);

            break;
        case 'both':
        default:
            log_message('error', $msg);
            fwrite(STDERR, $msg . PHP_EOL);
        }

        return $code;
    }
}
