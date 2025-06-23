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
 * Class PwChatModel
 *
 * Handles chat-related database operations.
 * Includes message fetching, saving, deletion,
 * and caching for optimal performance.
 */
class PwChatModel extends CI_Model {
    // Constants
    private const DEFAULT = [
        'BATCH_SIZE' => 10,
        'LIMIT' => 20,
        'OFFSET' => 0
    ];

    private const MAX_CONTENT_LENGTH = 1000;

    // Cache duration in seconds
    private const CACHE_DURATION = [
        'SHORT' => 60,
        'LONG' => 300
    ];

    private const DB_TABLES = [
        'MSG_PTCPS' => 'message_participants',
        'MSGS' => 'messages',
        'USRS' => 'users'
    ];

    private const ERRORS = [
        'LOG_DB_DELETE_FAILED'
          => 'Database transaction failed during message deletion.',
        'LOG_EMPTY_MSG' => 'Message content cannot be empty.',
        'LOG_EMPTY_MID' => 'Message ID cannot be empty.',
        'LOG_FMT_UNSUP' => 'Unsupported format requested: %s',
        'LOG_INSERT_FAILED'
          => 'Failed to insert message. Sender ID: %s, Content: %s',
        'LOG_INV_PARAMS_FMB'
          => 'Invalid input parameters: user_id=%d, batch_size=%d',
        'LOG_INV_PARAMS_FRM'
          => 'Invalid input parameters.',
        'LOG_INV_PARAMS_SM'
          => 'Invalid input parameters: sender_id=%d, receiver_id=%d',
        'LOG_INV_UID' => 'Invalid user ID: %d',
        'LOG_JSON_ENC_FAILED' => 'JSON encoding failed: %s',
        'LOG_MSG_TOO_LONG' => 'Message content exceeds maximum length.',
        'LOG_SAVE_FAILED'
          => 'Failed to save message participants for message ID: %s',
        'THROW_BATCH_FAILED' => '[%s] Batch fetch failed: %s',
        'THROW_DB_ERROR'
          => '[%s] Database Error: %s; SQL query redacted for security.',
        'THROW_DB_TRSCTN_FAILED' => '[%s] Transaction failed: %s',
        'THROW_INV_INPUT' => '[%s] Invalid input: %s',
        'THROW_INV_PARAMS' => '[%s] Invalid Parameters: %s',
        'THROW_JSON_ENC_FAILED' => '[%s] JSON encoding failed: %s. Error: %s',
        'THROW_UNEXP_ERROR' => '[%s] Unexpected error: %s'
    ];

    private const LOGS = [
        'MSG_DELETED' => 'Successfully deleted %d message(s).',
        'MSG_SAVED' => 'Message saved successfully with ID: %d'
    ];


    /**
     * Formats messages into the JSON format.
     *
     * @param array $messages The messages to format.
     *
     * @return string The formatted messages.
     *
     * @throws JsonException If JSON encoding fails.
     * @throws Exception For any unexpected errors.
     */
    private function format(array $messages): string
    {
        try {
            // Attempt to encode messages into JSON and return
            return json_encode(
                $messages,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
        } catch (JsonException $e) {
            // Handle and log JSON encoding error
            log_message('error', sprintf(
                self::ERRORS['THROW_JSON_ENC_FAILED'],
                __METHOD__,
                $e->getMessage(),
                json_last_error_msg()
            ));
        } catch (Exception $e) {
            // Handle and log any general exceptions or errors
            log_message('error', sprintf(
                self::ERRORS['THROW_UNEXP_ERROR'],
                __METHOD__,
                $e->getMessage()
            ));
        }

        // Return empty string as a fallback in case of any failure
        return '';
    }


    /**
     * Fetches recent messages for a user with specified pagination.
     *
     * @param int $user_id The ID of the user.
     * @param int $limit The maximum number of messages to fetch.
     * @param int $offset The pagination offset.
     *
     * @return array The fetched messages,
     *   or an empty array on failure.
     *
     * @throws InvalidArgumentException
     *   If the input parameters are invalid.
     * @throws RuntimeException If JSON encoding of messages fails.
     * @throws Exception For any unexpected errors.
     */
    private function fetch(int $user_id, int $limit, int $offset): array
    {
        try {
            // Throw an exception for invalid input parameters
            if ($user_id <= 0 || $limit <= 0 || $offset < 0) {
                throw new InvalidArgumentException(sprintf(
                    self::ERRORS['LOG_INV_PARAMS_FRM']
                ));
            }

            // Perform the query
            $query = $this->db->select([
                'm.id AS message_id',
                'm.content',
                'm.timestamp',
                'u1.username AS sender',
                'u1.id AS sender_id',
                'u2.username AS receiver'
            ])
              ->from(self::DB_TABLES['MSG_PTCPS'] . ' mp')
              ->join(self::DB_TABLES['MSGS'] . ' m', 'mp.message_id = m.id')
              ->join(self::DB_TABLES['USRS'] . ' u1', 'mp.sender_id = u1.id')
              ->join(self::DB_TABLES['USRS'] . ' u2',
                     'mp.receiver_id = u2.id', 'left')
              ->where('mp.receiver_id', $user_id)
              ->or_where('mp.receiver_id IS NULL')
              ->order_by('m.timestamp', 'DESC')
              ->limit($limit, $offset)
              ->get();

        // Debugging
        $messages = $query->result_array();
        log_message('debug', 'Messages fetched from DB: '
                    . print_r($messages, true));

            // Return the database result as an array
            return $query->result_array() ?: [];
        } catch (InvalidArgumentException $e) {
            // Handle and log validation errors
            log_message('error', sprintf(
                self::ERRORS['THROW_INV_PARAMS'],
                __METHOD__,
                $e->getMessage()
            ));
        } catch (PDOException $e) {
            // Handle and log database errors
            log_message('error', sprintf(
                self::ERRORS['THROW_DB_ERROR'],
                __METHOD__,
                $e->getMessage()
            ));
        } catch (Exception $e) {
            // Handle and log any general exceptions or errors
            log_message('error', sprintf(
                self::ERRORS['THROW_UNEXP_ERROR'],
                __METHOD__,
                $e->getMessage()
            ));
        }

        // Return empty array as a fallback in case of any failure
        return [];
    }


    /**
     * Counts the number of messages for a given user.
     *
     * @param int $user_id The ID of the user
     *   whose messages are to be counted.
     *
     * @return int The total number of messages for the user.
     *
     * @throws OutOfBoundsException If the user ID is invalid (<= 0).
     * @throws PDOException If a database query error occurs.
     * @throws Exception For any unexpected errors.
     */
    public function count(int $user_id): int
    {
        // Throw an exception for invalid user ID
        if ($user_id <= 0) {
            throw new OutOfBoundsException(
                sprintf(self::ERRORS['LOG_INV_UID'], $user_id)
            );
        }

        try {
            // Perform the query to count messages
            $count = (int)$this->db
              ->from(self::DB_TABLES['MSG_PTCPS'] . ' mp')
              ->join(self::DB_TABLES['MSGS'] . ' m',
                     'mp.message_id = m.id', 'inner')
              ->where('mp.receiver_id', $user_id)
              ->count_all_results();

            // Return the database result to count messages
            return max(0, $count);
        } catch (PDOException $e) {
            // Handle and log specific database errors
            log_message('error', sprintf(
                self::ERRORS['THROW_DB_ERROR'],
                __METHOD__,
                $e->getMessage()
            ));

            // Propagate exception (re-throw)
            throw $e;
        } catch (Exception $e) {
            // Handle and log any general exceptions or errors
            log_message('error', sprintf(
                self::ERRORS['THROW_UNEXP_ERROR'],
                __METHOD__,
                $e->getMessage()
            ));

            // Propagate exception (re-throw)
            throw $e;
        }
    }


    /**
     * Delete message(s) by ID
     *
     * @param int[] $message_ids An array of message IDs to delete.
     *
     * @return bool True if deletion is successful, false otherwise.
     *
     * @throws InvalidArgumentException If the message IDs array is empty.
     * @throws RuntimeException If the database transaction fails.
     * @throws Exception For any unexpected errors.
     */
    public function delete(array $message_ids): bool
    {
        try {
            // Check if the message_ids array is empty
            if (empty($message_ids)) {
                throw new InvalidArgumentException(sprintf(
                    self::ERRORS['LOG_EMPTY_MID']
                ));
            }

            // Begin the database transaction
            $this->db->trans_start();
            $this->db->where_in('message_id', $message_ids)
              ->delete(self::DB_TABLES['MSG_PTCPS']);
            $this->db->where_in('id', $message_ids)
              ->delete(self::DB_TABLES['MSGS']);
            $this->db->trans_complete();

            // Check the database transaction status
            if (!$this->db->trans_status()) {
                throw new RuntimeException(sprintf(
                    self::ERRORS['LOG_DB_DELETE_FAILED']
                ));
            }

            // Log successful deletion
            log_message(
                'info',
                sprintf(self::LOGS['MSG_DELETED'], count($message_ids))
            );

            // Return true for successful deletion
            return true;
        } catch (InvalidArgumentException $e) {
            // Handle and log invalid message ID errors
            log_message('error', sprintf(
                self::ERRORS['THROW_INV_INPUT'],
                __METHOD__,
                $e->getMessage()
            ));
        } catch (RuntimeException $e) {
            // Handle and log deletion errors
            log_message('error', sprintf(
                self::ERRORS['THROW_DB_TRSCTN_FAILED'],
                __METHOD__,
                $e->getMessage()
            ));
        } catch (Exception $e) {
            // Handle and log any general exceptions or errors
            log_message('error', sprintf(
                self::ERRORS['THROW_UNEXP_ERROR'],
                __METHOD__,
                $e->getMessage()
            ));
        }

        // Return false as a fallback in case of any failure
        return false;
    }


    /**
     * Fetches messages for a user in batches.
     *
     * This method retrieves messages incrementally in batches to
     * optimize memory usage and handle large data sets efficiently.
     *
     * @param int $user_id The ID of the user
     *   whose messages are being fetched.
     * @param int $batch_size The number of messages
     *   to fetch per batch.
     *
     * @return array An array containing all fetched messages.
     *
     * @throws InvalidArgumentException If the user ID
     *   or batch size is invalid.
     * @throws Exception For any unexpected errors
     *   during message retrieval.
     */
    public function fetch_in_batches(int $user_id, int $batch_size): array
    {
        // Validate input parameters
        if ($user_id <= 0 || $batch_size <= 0) {
            throw new InvalidArgumentException(sprintf(
                self::ERRORS['LOG_INV_PARAMS_FMB'],
                $user_id,
                $batch_size
            ));
        }

        // Initialize variables
        $messages = [];
        $offset = 0;

        try {
            // Fetch messages in batches until no more results
            do {
                // Retrieve a batch of messages
                $batch = $this->fetch_recent_messages(
                    $user_id,
                    $batch_size,
                    $offset
                );

                // Merge batch into the result set
                $messages = array_merge($messages, $batch);

                // Increment the offset
                $offset += $batch_size;
            // Continue while full batch is fetched
            } while (count($batch) === $batch_size);
        } catch (Exception $e) {
            // Log unexpected errors
            log_message('error', sprintf(
                self::ERRORS['THROW_UNEXP_ERROR'],
                __METHOD__,
                $e->getMessage()
            ));
        }

        // Return all messages or an empty array if none were fetched
        return $messages;
    }


    /**
     * Retrieves recent messages for a user, with caching.
     *
     * @param int $user_id The ID of the user.
     * @param int $limit The maximum number of messages to retrieve.
     * @param int $offset The offset for pagination.
     * @param int $cache_time The cache duration in seconds.
     * @return string The recent messages
     *   in the specified format.
     */
    public function get(
        int $user_id,
        int $limit = self::DEFAULT['LIMIT'],
        int $offset = self::DEFAULT['OFFSET'],
        int $batch_size = self::DEFAULT['BATCH_SIZE'],
        bool $bypass_cache = false
    ): array
    {
        $cache_key = "recent_messages_{$user_id}_{$limit}_{$offset}";

        try {
            if ($this->cache->get($cache_key)) {
                return $this->cache->get($cache_key);
            }

            $messages =
              $this->fetch_recent_messages($user_id, $limit, $offset);

            // Save messages to cache for 5 minutes
            $this->cache->save($cache_key, $messages, 300);

            return $messages;
        } catch (Exception $e) {
            // Handle and log batch errors
            log_message('error', sprintf(
                self::ERRORS['THROW_BATCH_FAILED'],
                __METHOD__,
                $e->getMessage()
            ));

            return [];
        }
    }


    public function invalidate_get_cache(
        int $user_id,
        int $limit,
        int $offset
    ): void
    {
        $this->cache->delete("recent_messages_{$user_id}_{$limit}_{$offset}");
    }


    /**
     * Saves a new message to the database.
     *
     * Validates the input data before performing
     * the database operations.  Utilizes a transaction to ensure
     * both message and participant data are inserted successfully.
     * If an error occurs, the transaction is rolled back.
     *
     * @param int $sender_id The ID of the sender (must be > 0).
     * @param int|null $receiver_id The ID of the receiver
     *   or `null` for public messages.
     * @param string $content The content of the message
     *   (max length: 1000).
     * @return int Returns the ID of the saved message
     *   or `0` on failure.
     *
     * @throws RuntimeException If database insertion
     *   or transaction fails.
     * @throws InvalidArgumentException If `$sender_id` is invalid
     *   or `$content` is empty.
     */
    public function post(
        int $sender_id,
        ?int $receiver_id,
        string $content
    ): int
    {
        // Validate sender and receiver IDs
        if ($sender_id <= 0 || ($receiver_id !== null && $receiver_id <= 0)) {
            log_message('error', sprintf(
                self::ERRORS['LOG_INV_PARAMS_SM'],
                $sender_id,
                $receiver_id
            ));

            return 0;
        }

        // Validate content (if it empty)
        if (empty(trim($content))) {
            log_message('error', self::ERRORS['LOG_EMPTY_MSG']);

            return 0;
        }

        // Validate content (if too bigger)
        if (strlen($content) > self::MAX_CONTENT_LENGTH) {
            log_message('error', self::ERRORS['LOG_MSG_TOO_LONG']);

            return 0;
        }

        try {
            $this->db->trans_begin();

            /*
             * Data Binding & Security:
             * Use query bindings instead of raw data insertion
             */
            $this->db->insert(self::DB_TABLES['MSGS'],
                              ['content' => $content]);

            $message_id = $this->db->insert_id();

            if (!$message_id) {
                throw new RuntimeException(sprintf(
                    self::ERRORS['LOG_INSERT_FAILED'],
                    $sender_id,
                    $content
                ));
            }

            // Insert message participants using query bindings for security
            $this->db->insert(self::DB_TABLES['MSG_PTCPS'], [
                'message_id' => $message_id,
                'sender_id' => $sender_id,
                'receiver_id' => $receiver_id
            ]);

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();

                throw new RuntimeException(
                    sprintf(self::ERRORS['LOG_SAVE_FAILED'], $message_id)
                );
            }

            $this->db->trans_commit();

            // Log successful
            log_message(
                'info',
                sprintf(self::LOGS['MSG_SAVED'], $message_id)
            );

            // Return true for successful
            return $message_id;
        } catch (Exception $e) {
            $this->db->trans_rollback();

            // Handle and log any general exceptions or errors
            log_message('error', sprintf(
                self::ERRORS['THROW_UNEXP_ERROR'],
                __METHOD__,
                $e->getMessage()
            ));

            return 0;
        }
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // Load the database
        $this->load->database();

        // Load the cache driver
        $this->load->driver('cache', [
            'adapter' => 'file',
            'backup' => 'dummy'
        ]);
    }
}
