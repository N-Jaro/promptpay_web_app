<?php
// backend/public/auth/logout.php
session_start();
session_destroy();
header('Location: /promptpay_web_app/');
exit;
