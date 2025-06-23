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

namespace App\Controllers;

use App\Libraries\PwLoggerLibrary;
use App\Libraries\PwWebSocketLibrary;
use Exception;

/**
 * Class PwController
 *
 * The default controller for the application, specified using
 * `$route['default_controller'] = 'PwController';`
 * in the CodeIgniter 3 routing configuration.
 *
 * This controller initializes critical services during application startup:
 * - A WebSocket server for real-time, bidirectional communication.
 * - A logger for error tracking and system monitoring.
 *
 * Designed for high-performance environments,
 * it ensures WebSocket services are available immediately
 * upon application launch.
 * It supports dependency injection for both the logger and WebSocket server,
 * enhancing testability and code maintainability.
 *
 * Key features:
 * - Complies with PSR-12 and PSR-5 standards.
 * - Optimized for high traffic and minimal memory usage.
 * - Prevents redundant WebSocket server instances.
 *
 * Key responsibilities:
 * - Initialize and start the WebSocket server.
 * - Manage logging for critical errors and events.
 * - Provide real-time communication support on application launch.
 *
 * @package CodeIgniter 3
 * @author Márcio Delgado <marcio@libreware.info>
 * @license BSD-2-Clause
 * @link https://codeigniter.com/userguide3/general/controllers.html
 */
class PwController extends \CI_Controller
{
    /**
     * The logger instance for error reporting.
     *
     * @var PwLoggerLibrary|null
     */
    private ?PwLoggerLibrary $logger;

    /**
     * The WebSocket server instance.
     *
     * @var PwWebSocketLibrary|null
     */
    private ?PwWebSocketLibrary $webSocket;

    /**
     * PwController constructor.
     *
     * Loads essential libraries,
     * including the WebSocket server and logger.
     * Initializes and starts the WebSocket server.
     *
     * @param PwLoggerLibrary|null $logger Custom logger for testing.
     * @param PwWebSocketLibrary|null $webSocket Custom WebSocket for testing.
     *
     * @throws Exception If the WebSocket server fails to start or
     *     required libraries fail to load properly.
     */
    public function __construct(
        ?PwLoggerLibrary $logger = null,
        ?PwWebSocketLibrary $webSocket = null
    ) {
        parent::__construct();

        try {
            // Initialize logger
            $this->logger = $logger ?? new PwLoggerLibrary();

            // Initialize WebSocket server
            $this->webSocket = $webSocket ?? new PwWebSocketLibrary();

            // Start the WebSocket server
            $this->webSocket->run();
        } catch (Exception $e) {
            // Log error if logger is available, fallback otherwise
            if ($this->logger !== null) {
                $this->logger->critical($e);
            } else {
                log_message(
                    'critical',
                    "Unhandled Exception: {$e->getMessage()}"
                );
            }

            // Display a user-friendly error message
            show_error(
                'Failed to start WebSocket server. Please contact support.',
                500
            );
        }
    }
}
