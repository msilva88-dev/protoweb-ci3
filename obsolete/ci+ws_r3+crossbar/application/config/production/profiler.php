<?php
/*
 * This code is optimized for PHP 7.4+.
 * It ensures high performance, low memory usage, and clean code.
 * It adheres to coding standards inspired by PEP7, PEP8,
 * and C-style guidelines.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

include(APPPATH . 'config/profiler.php');

$config['enable_profiler'] = false;

$config['profiler_sections'] = [
    'benchmarks' => true,
    'config' => false,
    'controller_info' => false,
    'get' => false,
    'post' => false,
    'queries' => false,
    'uri_string' => false,
    'memory_usage' => true,
    'http_headers' => false,
    'session_data' => false
];
