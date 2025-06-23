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


class PwChatController extends CI_Controller
{
    private $html_txt_env = ' (Development)';
    private $limit = 50;
    private $url = [
        'jslog' => 'assets/js/devlog.js',
        'ws' => 'ws://localhost:8080'
    ];

    private const LENGTH = 1000;
    private const URL = [
        'css' => 'assets/css/chat.css',
        'js' => 'assets/js/chat.js',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('url');
        $this->load->model('PwChatModel', 'chatModel');
    }

    public function index(): void {
        /*
        // Environment configuration
        switch (ENVIRONMENT) {
        case 'production':
            $this->html_txt_env = '';
            $this->url['jslog'] = 'assets/js/log.js';
            break;
        case 'testing':
            $this->html_txt_env = ' (Testing)';
            $this->url['jslog'] = 'assets/js/testlog.js';
        }

        $data = [
            'html_txt_env' => $this->html_txt_env,
            'sha2_384' => [
                'css' => base64_encode(
                    hash_file('sha384', FCPATH . self::URL['css'], true)
                ),
                'js' => base64_encode(
                    hash_file('sha384', FCPATH . self::URL['js'], true)
                ),
                'jslog' => base64_encode(
                    hash_file('sha384', FCPATH . $this->url['jslog'], true)
                )
            ],
            'url' => [
                'css' => site_url('/' . self::URL['css']),
                'js' => site_url('/' . self::URL['js']),
                'jslog' => site_url('/' . $this->url['jslog']),
                'ws' => $this->url['ws']
            ]
        ];
        */
        $data = [];

        $this->load->view('chat', $data);
    }

    // Send a message
    public function sendMessage(): void
    {
        $data = json_decode($this->input->raw_input_stream, true);

        if (!isset($data['content'], $data['message_type_id'], $data['sender_id'], $data['receiver_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
            return;
        }

        // Save message and participants
        $messageId = $this->chatModel->saveMessage([
            'content' => $data['content'],
            'message_type_id' => $data['message_type_id'],
            'sender_id' => $data['sender_id'],
            'receiver_id' => $data['receiver_id']
        ]);

        // Respond with success
        echo json_encode(['status' => 'success', 'message_id' => $messageId]);
    }

    // Retrieve messages between two users
    public function getMessages(): void
    {
        $senderId = $this->input->get('sender_id');
        $receiverId = $this->input->get('receiver_id');

        if (!is_numeric($senderId) || !is_numeric($receiverId)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid sender or receiver ID']);
            return;
        }

        $messages = $this->chatModel->getMessages((int)$senderId, (int)$receiverId);

        echo json_encode($messages);
    }

    // Delete a message
    public function deleteMessage(): void
    {
        $data = json_decode($this->input->raw_input_stream, true);

        // Validate input
        if (!isset($data['message_id'], $data['user_id']) ||
            !is_numeric($data['message_id']) ||
            !is_numeric($data['user_id']))
        {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
            return;
        }

        $messageId = (int)$data['message_id'];
        $userId = (int)$data['user_id']; // Sender's ID

        $result = $this->chatModel->deleteMessage($messageId, $userId);

        if ($result) {
            $this->publishDeletionToCrossbar($messageId);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Deletion not allowed or message not found'
            ]);
        }
    }

    private function publishDeletionToCrossbar(int $messageId): void
    {
        $wsUrl = 'ws://localhost:8080';
        $realm = 'realm1';

        // Suppress logging for Thruway
        \Thruway\Logging\Logger::set(new \Psr\Log\NullLogger());

        $wampClient = new \Thruway\Peer\Client($realm);
        $loop = \React\EventLoop\Factory::create();

        $wampClient->on(
            'open',
            function ($session) use ($messageId, $wampClient, $loop) {
                $session->publish('com.chat.message_deleted', [$messageId]);
                $loop->stop();
            }
        );

        $wampClient->addTransportProvider(
            new \Thruway\Transport\PawlTransportProvider($wsUrl)
        );
        $wampClient->start(false);
        $loop->run();
    }
}
