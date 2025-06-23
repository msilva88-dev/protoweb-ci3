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

use App\Libraries\PwLoggerLibrary;
use Exception;
use InvalidArgumentException;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

/**
 * Class PwWebSocketHandlerLibrary
 *
 * Manages WebSocket connections and events independently.
 * The controller manages the database and service interactions.
 *
 * @package CodeIgniter 3
 * @author Márcio Delgado
 * @license BSD-2-Clause
 */
class PwWebSocketHandlerLibrary implements MessageComponentInterface
{
    /**
     * Collection of active clients.
     *
     * @var SplObjectStorage
     */
    protected SplObjectStorage $clients;

    /**
     * Logger instance for error reporting.
     *
     * @var PwLoggerLibrary
     */
    private PwLoggerLibrary $logger;

    /**
     * Constructor to initialize the WebSocket handler.
     *
     * @param PwLoggerLibrary $logger The logger instance.
     */
    public function __construct(PwLoggerLibrary $logger)
    {
        $this->clients = new SplObjectStorage();
        $this->logger = $logger;
    }

    /**
     * Handles new WebSocket connections.
     *
     * @param ConnectionInterface $conn The new connection instance.
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        $this->logger->log("New connection: {$conn->resourceId}");
    }

    /**
     * Handles incoming messages and delegates them to the controller.
     *
     * @param ConnectionInterface $from The sender connection.
     * @param string $msg The received message.
     */
    public function onMessage(ConnectionInterface $from, string $msg): void
    {
        $this->logger->log("Message received: {$msg}");
    }

    /**
     * Handles disconnections.
     *
     * @param ConnectionInterface $conn The closing connection.
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
        $this->logger->log("Connection closed: {$conn->resourceId}");
    }

    /**
     * Handles errors.
     *
     * @param ConnectionInterface $conn The connection where the error occurred.
     * @param Exception $e The exception thrown.
     */
    public function onError(ConnectionInterface $conn, Exception $e): void
    {
        $this->logger->critical($e);
        $conn->close();
    }
}
