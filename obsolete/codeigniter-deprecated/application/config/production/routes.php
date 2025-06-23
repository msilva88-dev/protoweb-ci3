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
$route['chat/http_delete'] = 'PwChatController/httpDelete';
$route['chat/http_get'] = 'PwChatController/httpGet';
$route['chat/http_post'] = 'PwChatController/httpPost';
$route['simple_chat'] = 'PwSimpleChatController';
$route['simple_chatdb'] = 'PwSimpleChatDBController';
