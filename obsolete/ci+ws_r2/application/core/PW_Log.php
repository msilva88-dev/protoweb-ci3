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
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * This code is optimized for PHP 7.4+.
 * It ensures high traffic, high performance, low memory usage,
 * and clean code.
 * It adheres to coding standards inspired by PEP7, PEP8,
 * and C-style guidelines.
 */

declare(strict_types = 1);

defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * PW_Log
 *
 * Extends the CI_Log class to support additional log levels.
 *
 * @package CodeIgniter 3
 * @author M  rcio Delgado
 * @license BSD-2-Clause
 */
class PW_Log
extends CI_Log
{
    // Extending the log levels to support critical and warning
    protected array $_levels = [
        'CRITICAL' => 1,
        'ERROR' => 2,
        'WARNING' => 3,
        'INFO' => 4,
        'DEBUG' => 5,
        'ALL' => 6
    ];


    /**
     * Custom method to handle additional levels correctly.
     *
     * @param string $level The error level:
     *   'critical', 'debug', 'error', 'info' or 'warning'.
     * @param string $msg The error message.
     *
     * @return bool
     */
    public function
    write_log(string $level, string $msg): bool
    {
        $level = strtoupper($level);

        if (!isset($this->_levels[$level])
            || $this->_levels[$level] > $this->_threshold)
        {
            return false;
        }

        // Call the original logging method
        return parent::write_log($level, $msg);
    }
}
