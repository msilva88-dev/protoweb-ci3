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
//$route['default_controller'] = 'PwController';
$route['chat'] = 'PwChatController';
$route['chat/delete_message'] = 'PwChatController/deleteMessage';
$route['chat/get_messages'] = 'PwChatController/getMessages';
$route['chat/send_message'] = 'PwChatController/sendMessage';
