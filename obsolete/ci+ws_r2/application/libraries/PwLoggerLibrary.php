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

namespace App\Libraries;

use Exception;
use InvalidArgumentException;

/**
 * Class PwLoggerLibrary
 *
 * A lightweight, environment-aware logging library
 * for CodeIgniter 3 projects.
 *
 * This class provides a flexible and efficient logging mechanism
 * supporting multiple severity levels including
 * `critical`, `error`, `warning`, `info`, and `debug`.
 * It integrates with CodeIgniter's built-in `log_message` function,
 * ensuring consistency across the application.
 *
 * Logging Behavior:
 * - Production: Only logs critical issues.
 * - Testing: Logs `critical`, `error`, and `warning` messages.
 * - Development: Logs all severity levels including `debug`.
 *
 * Key Features:
 * - Adapts logging behavior based on the current environment (`ENVIRONMENT`).
 * - Validates log levels before logging messages.
 * - Provides a dedicated method for logging critical exceptions
 *   with stack traces.
 * - Complies with PSR-12 and PSR-5 standards.
 *
 * Usage:
 * ```php
 * $logger = new PwLoggerLibrary();
 *
 * $logger->log('An info message', 'info');
 * $logger->critical(new Exception('Critical failure!'));
 * ```
 *
 * @package CodeIgniter 3
 * @author Márcio Delgado <marcio@libreware.info>
 * @license BSD-2-Clause
 * @link https://codeigniter.com/userguide3/general/errors.html
 */
class PwLoggerLibrary
{
    /**
     * List of valid log levels allowed in the application.
     *
     * @var string[]
     */
    const VALID_LEVELS = ['critical', 'debug', 'error', 'info', 'warning'];

    /**
     * Determines if logging is enabled for the current environment.
     *
     * This method checks whether logging
     * should be performed for a given severity level
     * based on the current value of the `ENVIRONMENT` constant.
     *
     * Logging behavior per environment:
     * - Development: All log levels are enabled:
     *   `debug`, `info`, `warning`, `error`, `critical`.
     * - Testing: Only logs `critical`, `error`, and `warning` messages.
     * - Production: Only `critical` messages are logged.
     *
     * @param string $level The severity level to check.
     *     Valid values: `critical`, `debug`, `error`, `info`, `warning`.
     *
     * @return bool Returns `true` if logging is permitted
     *     for the provided severity level, otherwise `false`.
     */
    private function isLoggingEnabled(string $level): bool
    {
        $env = ENVIRONMENT;

        // Enables logging based on the environment and severity level.
        return ($env === 'development') ||
               (
                   $env === 'testing' &&
                   in_array($level, ['critical', 'error', 'warning'], true)
               ) ||
               ($env === 'production' && $level === 'critical');
    }

    /**
     * Logs a message based on the severity level and application environment.
     *
     * This method checks the current application environment
     * and determines if the provided severity level should be logged.
     *
     * - Production: Logs only critical issues.
     * - Testing: Logs warnings, errors, and critical issues.
     * - Development: Logs all levels including debug.
     *
     * If the log level is invalid, an exception is thrown.
     *
     * @param string $message The message to be logged.
     * @param string $level The severity level:
     *     critical, debug, error, info, or warning.
     *
     * @throws InvalidArgumentException If an invalid log level is provided.
     *
     * @return void
     */
    public function log(string $message, string $level = 'info'): void
    {
        // Validate the log level
        if (!in_array($level, self::VALID_LEVELS, true)) {
            throw new InvalidArgumentException(
                "Invalid log level: {$level}"
            );
        }

        // Determine if logging is permitted based on the environment
        if ($this->isLoggingEnabled($level)) {
            log_message($level, $message);
        }
    }

    /**
     * Logs a critical exception along with its stack trace.
     *
     * This method captures the exception message and stack trace,
     * then logs them with the `critical` severity level.
     * The log will only be recorded
     * if the environment allows critical logging.
     *
     * @param Exception $e The exception object containing the error message
     *     and stack trace for logging.
     *
     * @return void
     */
    public function critical(Exception $e): void
    {
        // Log the exception message with the critical level
        $this->log(
            'Critical error in ' . self::class . ": {$e->getMessage()}",
            'critical'
        );

        // Log the full stack trace for better debugging visibility
        $this->log("Stack trace: {$e->getTraceAsString()}", 'critical');
    }
}
