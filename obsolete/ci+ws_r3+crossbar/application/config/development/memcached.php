<?php
/*
 * This code is optimized for PHP 7.4+.
 * It ensures high performance, low memory usage, and clean code.
 * It adheres to coding standards inspired by PEP7, PEP8,
 * and C-style guidelines.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

include(APPPATH . 'config/memcached.php');

$config = [
    'hostname' => 'localhost',
    'port' => 11211,
    'weight' => 1,
    'timeout' => 5,
    'status' => true,
    'failure_callback' => null
];
