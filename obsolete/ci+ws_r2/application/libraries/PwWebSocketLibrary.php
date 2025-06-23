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
use App\Libraries\PwWebSocketHandlerLibrary;
use Exception;
use InvalidArgumentException;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;
use React\Socket\Server as ReactServer;

/**
 * Class PwWebSocketLibrary
 *
 * A WebSocket server library using Ratchet and ReactPHP
 * for handling real-time bidirectional communication
 * between clients and the server.
 *
 * This library integrates ReactPHP's event-driven components
 * with Ratchet's WebSocket protocol implementation.
 * It supports dependency injection for easier unit testing
 * and provides robust error handling through a custom logger.
 * The server can be started with configurable host and port values
 * and allows runtime reinitialization if the host or port changes.
 *
 * Key features:
 * - Uses PSR-12 and PSR-5 coding standards.
 * - Designed for high traffic and efficient memory usage.
 * - Provides customizable logging and WebSocket handling.
 * - Prevents multiple event loops in a single execution context.
 *
 * Usage:
 * ```php
 * use App\Libraries\PwWebSocketLibrary;
 *
 * $webSocket = new PwWebSocketLibrary('127.0.0.1', 8080);
 * $webSocket->run();
 * ```
 *
 * @package CodeIgniter 3
 * @author Márcio Delgado <marcio@libreware.info>
 * @license BSD-2-Clause
 * @link https://github.com/ratchetphp/Ratchet Ratchet WebSocket Library
 * @link https://reactphp.org/ ReactPHP Library
 * @link https://codeigniter.com/userguide3 CodeIgniter 3 Documentation
 */
class PwWebSocketLibrary
{
    /**
     * The IP address for the WebSocket server.
     *
     * @var string
     */
    private string $host;

    /**
     * The logger instance for error reporting.
     *
     * @var PwLoggerLibrary|null
     */
    private ?PwLoggerLibrary $logger = null;

    /**
     * The ReactPHP event loop instance.
     *
     * @var LoopInterface|null
     */
    private ?LoopInterface $loopInterface = null;

    /**
     * The port number for the WebSocket server.
     *
     * @var int
     */
    private int $port;

    /**
     * The WebSocket server instance.
     *
     * @var IoServer
     */
    private ?IoServer $ioServer = null;

    /**
     * The WebSocket handler instance.
     *
     * @var PwWebSocketHandlerLibrary|null
     */
    private ?PwWebSocketHandlerLibrary $webSocketHandler = null;

    /**
     * Sets the host and port for the WebSocket server.
     *
     * Validates the provided port
     * and updates the host and port values.
     * If the provided values are null,
     * the existing values will be retained.
     *
     * @param string|null $host The host IP address to bind the server.
     *     If null, the existing host value will be used.
     * @param int|null $port The port number for the server.
     *     If null, the existing port value will be used.
     *
     * @throws InvalidArgumentException If the port number is invalid
     *     (not between 1 and 65535).
     *
     * @return void
     */
    private function setHostAndPort(?string $host, ?int $port): void
    {
        // Validate the port to ensure it is within the valid range
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new InvalidArgumentException(
                'Port must be between 1 and 65535.'
            );
        }

        $this->host = $host ?? $this->host;
        $this->port = $port ?? $this->port;
    }

    /**
     * Initializes the WebSocket server using Ratchet and ReactPHP.
     *
     * Allows dependency injection for testing purposes.
     * Prevents multiple event loops
     * from being created in a single execution context.
     *
     * @param HttpServer|null $httpServer Custom HTTP server for WebSocket.
     * @param IoServer|null $ioServer Custom IO server for handling sockets.
     * @param ReactServer|null $socket Custom React server for the WebSocket.
     * @param WsServer|null $wsServer Custom WebSocket server for handling
     *     connections.
     *
     * @throws Exception If WebSocket initialization fails.
     *
     * @return void
     */
    protected function initWebSocket(
        ?HttpServer $httpServer = null,
        ?IoServer $ioServer = null,
        ?ReactServer $socket = null,
        ?WsServer $wsServer = null
    ): void {
        try {
            // Check if loop already exists, avoid multiple loops
            if ($this->loopInterface === null) {
                $this->logger->log('Initializing WebSocket Server...');

                $this->loopInterface = LoopFactory::create();
            } else {
                $this->logger->log('Reinitializing WebSocket Server...',
                                   'warning');
            }

            // Initialize WebSocket server
            $wsServer ??= new WsServer($this->webSocketHandler);

            // HTTP server manages WebSocket handshakes and protocol handling
            $httpServer ??= new HttpServer($wsServer);

            // ReactPHP socket binds the WebSocket server to the host and port
            $socket ??= new ReactServer(
                "{$this->host}:{$this->port}",
                $this->loopInterface
            );

            // IoServer integrates the HTTP server, socket, and event loop
            $this->ioServer ??= new IoServer(
                $httpServer,
                $socket,
                $this->loopInterface
            );

            $this->logger->log(
                "WebSocket server bound to {$this->host}:{$this->port}"
            );
        } catch (Exception $e) {
            // Log critical error
            $this->logger->critical($e);

            // Rethrow exception
            throw $e;
        }
    }

    /**
     * Runs the WebSocket server. Optionally allows changing the host and port.
     *
     * If the provided host or port differs from the current values,
     * the WebSocket server will be reinitialized with the new configuration.
     * The port number will be validated to ensure it falls
     * within the valid range (1-65535).
     *
     * @param string|null $host Optional host IP address to bind the server.
     * @param int|null $port Optional port number to bind the server.
     *
     * @throws Exception If the WebSocket server fails to run
     *     or if the port is invalid.
     *
     * @return void
     */
    public function run(
        ?string $host = null,
        ?int $port = null,
        ?HttpServer $httpServer = null,
        ?IoServer $ioServer = null,
        ?ReactServer $socket = null,
        ?WsServer $wsServer = null
    ): void {
        try {
            // Reinitialize WebSocket only if host or port has changed
            if ($host !== $this->host || $port !== $this->port ) {
                // Set custom host and port
                $this->setHostAndPort($host, $port);

                $this->logger->log(
                    'Host or port changed. Reinitializing server...',
                    'warning'
                );

                // Reinitialize WebSocket with the new configuration
                $this->initWebSocket(
                    $httpServer,
                    $ioServer,
                    $socket,
                    $wsServer
                );
            }

            $this->logger->log(
                "WebSocket Server running on {$this->host}:{$this->port}"
            );

            // Run the WebSocket server
            $this->ioServer->run();
        } catch (Exception $e) {
            // Log critical error
            $this->logger->critical($e);

            // Rethrow exception
            throw $e;
        }
    }

    /**
     * PwWebSocketLibrary constructor.
     *
     * Initializes the WebSocket server with the specified host, port,
     * and optional logger and WebSocket handler instances
     * for testing purposes.
     *
     * @param string $host Host IP address for the WebSocket server.
     * @param int $port Port number for the WebSocket server.
     * @param PwLoggerLibrary|null $logger Custom logger instance for testing.
     * @param PwWebSocketHandlerLibrary|null $webSocketHandler
     *     Custom WebSocket handler for testing.
     *
     * @throws Exception If the WebSocket server fails to initialize
     *     or if the port is invalid.
     */
    public function __construct(
        string $host = 'localhost',
        int $port = 8080,
        ?PwLoggerLibrary $logger = null,
        ?PwWebSocketHandlerLibrary $webSocketHandler = null,
        ?HttpServer $httpServer = null,
        ?IoServer $ioServer = null,
        ?ReactServer $socket = null,
        ?WsServer $wsServer = null
    ) {
        try {
            // Set custom host and port
            $this->setHostAndPort($host, $port);

            // Initialize logger
            $this->logger = $logger ?? new PwLoggerLibrary();

            // Initialize WebSocket handler
            $this->webSocketHandler =
                $webSocketHandler ?? new PwWebSocketHandlerLibrary();

            // Auto-initialize the WebSocket server
            $this->initWebSocket(
                $httpServer,
                $ioServer,
                $socket,
                $wsServer
            );
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

            // Rethrow exception
            throw $e;
        }
    }
}
