<?php
/*
 * This code is optimized for PHP 7.4+.
 * It ensures high performance, low memory usage, and clean code.
 * It adheres to coding standards inspired by PEP7, PEP8,
 * and C-style guidelines.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

include(APPPATH . 'config/database.php');

$active_group = 'protoweb';

$db['protoweb'] = [
    'dsn' => '',
    'hostname' => '172.27.0.2',
    'username' => 'rentme',
    'password' => 'Thaiyee4',
    'database' => 'protoweb',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => false,
    'db_debug' => true,
    'cache_on' => false,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_general_ci',
    'swap_pre' => '',
    'encrypt'  => false,
    'compress' => false,
    'stricton' => false,
    'failover' => [],
    'save_queries' => true
];
