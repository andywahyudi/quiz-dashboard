<?php
// Database configuration
define('DB_HOST', 'rst-database');
define('DB_NAME', 'quizarbiter');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Mailgun configuration
define('MAILGUN_API_KEY', '');
define('MAILGUN_DOMAIN', ''); //mail.andywahyudi.com
define('MAILGUN_FROM_EMAIL', 'andy.wahyudi@arbitersports.com');

// Application settings
define('TIMEZONE', 'Asia/Bangkok'); // GMT+7
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('VERIFICATION_CODE_EXPIRY', 600); // 10 minutes

// Start session
session_start();

// Set timezone
date_default_timezone_set(TIMEZONE);
?>