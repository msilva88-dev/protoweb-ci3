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
    private $htmTxtEnv = ' (Development)';
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

    private function
    format(array $msg): array
    {
        return [
            'message_id' => $msg['message_id'],
            'content' => htmlspecialchars(
                $msg['content'],
                ENT_QUOTES,
                'UTF-8'
            ),
            'timestamp' => $msg['timestamp'],
            'sender' => htmlspecialchars(
                $msg['sender'] ?? 'Unknown user',
                ENT_QUOTES,
                'UTF-8'
            ),
            'sender_id' => $msg['sender_id'],
            'receiver' => htmlspecialchars(
                $msg['receiver'] ?? 'public',
                ENT_QUOTES,
                'UTF-8'
            )
        ];
    }

    private function cspGet(): string {
        $scheme = htmlspecialchars(
            $_SERVER['REQUEST_SCHEME'],
            ENT_QUOTES,
            'UTF-8'
        );
        $host = htmlspecialchars(
            $_SERVER['HTTP_HOST'],
            ENT_QUOTES,
            'UTF-8'
        );

        return "default-src 'self';" .
            " connect-src 'self' {$scheme}://{$host} {$this->url['ws']};" .
            " font-src 'self';" .
            " img-src 'self';" .
            " script-src 'self' {$scheme}://{$host};" .
            " style-src 'self' {$scheme}://{$host};";
    }

    public function index(): void
    {
        // Environment configuration
        switch (ENVIRONMENT) {
            case 'production':
                $this->htmTxtEnv = '';
                $this->url['jslog'] = 'assets/js/log.js';

                break;
            case 'testing':
                $this->htmTxtEnv = ' (Testing)';
                $this->url['jslog'] = 'assets/js/testlog.js';
        }

        $data = [
            'htmTxtEnv' => $this->htmTxtEnv,
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

        $this->load->view('chat', $data);
    }

    private function jsonResponse(
        string $stat,
        string $msg,
        ?array $data = null,
        int $httpStat = 200
    ): void
    {
        $this->output->set_header('Content-Type: application/json');
        $this->output->set_status_header($httpStat);

        echo json_encode([
            'status' => $stat,
            'message' => $msg,
            'data' => $data ?? []
        ]);

        exit;
    }


    private function jsonParse(): ?stdClass
    {
        $data = json_decode(file_get_contents('php://input'));

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonResponse(
                'error',
                'Invalid JSON input.',
                null,
                400
            );

            return null;
        }

        return $data;
    }

    private function validateCsrfToken(): void
    {
        if ($this->config->item('csrf_protection')) {
            $csrf_token = $this->input->get_request_header('X-CSRF-Token');

            if ($csrf_token !== $this->security->get_csrf_hash()) {
                $this->jsonResponse(
                    'error',
                    'Invalid CSRF token.',
                    null,
                    403
                );

                exit;
            }
        }
    }

    private function validateMessage(?stdClass $data): ?string
    {
        if (!isset($data->sender_id) || !isset($data->content)
            || empty(trim($data->content)))
        {
            return 'Invalid data: sender_id and content are required.';
        }

        if (strlen($data->content) > self::LENGTH) {
            return 'Message content exceeds the maximum allowed length.';
        }

        if (isset($data->receiver_id) && (!is_numeric($data->receiver_id)
            || $data->receiver_id <= 0))
        {
            return 'Invalid receiver ID.';
        }

        return null;
    }

    public function httpDelete(): void
    {
        $this->validateCsrfToken();

        $data = $this->jsonParse();

        // Check if message_id is provided
        if (!isset($data->message_id) || !is_numeric($data->message_id)) {
            $this->jsonResponse('error', 'Invalid Message ID.',
                                 null, 400);

            return;
        }

        try {
            if ($this->model->delete([(int)$data->message_id])) {
                $this->jsonResponse('success',
                                     'Message deleted successfully.');
            } else {
                $this->jsonResponse('error', 'Failed to delete message.',
                                     null, 500);
            }
        } catch (Exception $e) {
            log_message('error', sprintf(
                'Error in requestDelete: %s',
                $e->getMessage()
            ));

            $this->jsonResponse('error', 'Internal Server Error.',
                                 null, 500);
        }
    }

    public function httpGet(): void
    {
        $this->validateCsrfToken();

        $input_data = $this->jsonParse();

        $user_id =
          isset($input_data['user_id']) ? (int)$input_data['user_id'] : 0;
        $limit =
          isset($input_data['limit'])
          ? (int)$input_data['limit'] : $this->limit;
        $offset =
          isset($input_data['offset']) ? (int)$input_data['offset'] : 0;

        if (!$user_id) {
            $this->jsonResponse('error', 'User ID is required.', null, 400);

            return;
        }

        try {
            $msgs = $this->model->get($user_id, $limit, $offset);
            $formatted_messages =
              array_map([$this, 'format'], $msgs);
            $stat_message = empty($formatted_messages)
              ? 'No messages found.' : 'Messages retrieved successfully.';

            $this->jsonResponse('success', $stat_message,
                                 $formatted_messages);
        } catch (Exception $e) {
            log_message('error', sprintf(
                'Error in get_recent_messages: %s',
                $e->getMessage()
            ));

            $this->jsonResponse('error', 'Internal Server Error.',
                                 null, 500);
        }
    }

    public function httpPost(): void
    {
        $this->validateCsrfToken();

        $data = $this->jsonParse();
        $error = $this->validateMessage($data);

        if ($error) {
            $this->jsonResponse('error', $error, null, 400);

            return;
        }

        try {
            /*
             * CodeIgniter recommends using query bindings
             * for better security
             */
            $msg_id = $this->model->post(
                (int)$data->sender_id,
                isset($data->receiver_id) ? (int)$data->receiver_id : null,
                /*
                 * Bind this through the model using query binding
                 *
                 * Not using these method calls:
                 * $this->db->escape_str($data->content)
                 * $this->db->escape($data->content)
                 */
                $data->content
            );

            $this->db->select('messages.id as message_id,'
                              . ' messages.content,'
                              . ' messages.timestamp,'
                              . ' users.username as sender')
                 ->join('users', 'users.id = messages.sender_id')
                 ->where('messages.id', $msg_id);

            $query = $this->db->get('messages');
            $msg = $query->row();

            $this->jsonResponse('success', 'Message sent successfully.',
                                 (array)$msg);
        } catch (Exception $e) {
            log_message('error', sprintf(
                'Error in send_message: %s',
                $e->getMessage()
            ));

            $this->jsonResponse('error', 'Internal Server Error.',
                                 null, 500);
        }
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->config->load('chat');
        $this->load->helper('url');
        $this->load->model('PwChatModel', 'model');

        if ($this->config->item('websocket')) {
            $this->url['ws'] = $this->config->item('websocket');
        }

        // Security headers
        $this->output->set_header(
            'Content-Security-Policy: ' . $this->cspGet()
        );
        $this->output->set_header('Referrer-Policy: ' . 'no-referrer');
        $this->output->set_header('X-Content-Type-Options: ' . 'nosniff');
        $this->output->set_header('X-Frame-Options: ' . 'DENY');
    }
}
