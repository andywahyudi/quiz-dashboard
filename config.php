<?php
// Database configuration
define('DB_HOST', 'rst-database');
define('DB_NAME', 'quizarbiter');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Mailgun configuration
define('MAILGUN_API_KEY', 'cfe9d84db23eceb32d114ebfd543ca71-97129d72-53c5df77');
define('MAILGUN_DOMAIN', 'sandboxe0c2b16727984d6f817bbf35a2c6decf.mailgun.org'); //mail.andywahyudi.com
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