<?php
// init.php
if (session_status() === PHP_SESSION_NONE) {
    // Set configuration parameters
    $sessionConfig = [
        'cookie_httponly' => 1,
        'cookie_secure' => 1, // Enable only if using HTTPS
        'use_strict_mode' => 1,
        'cookie_samesite' => 'Strict',
        'gc_maxlifetime' => 14400, // 4 hours
        'cookie_lifetime' => 0 // Until browser closes
    ];
    
    // Apply settings
    foreach ($sessionConfig as $key => $value) {
        ini_set("session.$key", $value);
    }
    
    session_start();
    
    if (empty($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }
    
    // Validate session to prevent fixation
    if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'] ||
        $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_regenerate_id(true);
        $_SESSION = [];
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }
}