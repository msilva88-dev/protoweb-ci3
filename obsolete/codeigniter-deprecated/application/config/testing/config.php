<?php
/*
 * This code is optimized for PHP 7.4+.
 * It ensures high performance, low memory usage, and clean code.
 * It adheres to coding standards inspired by PEP7, PEP8,
 * and C-style guidelines.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

include(APPPATH . 'config/config.php');

//$config['base_url'] = '';
$config['base_url'] = 'http://172.27.0.3/protoweb/development/codeigniter/';
$config['index_page'] = '';
$config['charset'] = 'utf8mb4';
$config['subclass_prefix'] = 'PW';
$config['composer_autoload'] = FCPATH . 'vendor/autoload.php';
$config['log_threshold'] = 3;
$config['sess_driver'] = 'memcached';
//$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'ci_session';
$config['sess_samesite'] = 'Lax';
$config['sess_expiration'] = 7200;
$config['sess_save_path'] = 'localhost:11211';
//$config['sess_save_path'] = sys_get_temp_dir();
$config['sess_match_ip'] = true;
$config['sess_time_to_update'] = 300;
$config['sess_regenerate_destroy'] = true;
$config['cookie_prefix'] = '';
$config['cookie_domain'] = '';
$config['cookie_path'] = '/';
$config['cookie_secure'] = false;
$config['cookie_httponly'] = true;
$config['cookie_samesite'] = 'Lax';
$config['csrf_protection'] = false;
$config['csrf_token_name'] = 'csrf_token';
$config['csrf_cookie_name'] = 'csrf_cookie';
$config['csrf_expire'] = 7200;
$config['csrf_regenerate'] = true;
$config['csrf_exclude_uris'] = [];
$config['compress_output'] = true;
