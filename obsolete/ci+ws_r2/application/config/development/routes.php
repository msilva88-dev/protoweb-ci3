<?php
/*
 * This code is optimized for PHP 7.4+.
 * It ensures high performance, low memory usage, and clean code.
 * It adheres to coding standards inspired by PEP7, PEP8,
 * and C-style guidelines.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

include(APPPATH . 'config/routes.php');

$route['translate_uri_dashes'] = true;
$route['default_controller'] = 'PW_Controller';
$route['chat'] = 'PW_Chat_Controller';
$route['chat/delete_message'] = 'PW_Chat_Controller/delete_message';
$route['chat/pull_messages'] = 'PW_Chat_Controller/pull_messages';
$route['chat/send_message'] = 'PW_Chat_Controller/send_message';
