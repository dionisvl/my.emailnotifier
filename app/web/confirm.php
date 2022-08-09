<?php

declare(strict_types=1);

require_once getenv('PROJECT_DIR') . '/config.php';
require_once getenv('PROJECT_DIR') . '/functions.php';
require_once getenv('PROJECT_DIR') . '/DataProvider/users.php';

if (isset($_GET['confirmCode'])) {
    $userId = base64_decode($_GET['confirmCode']);
    if (is_numeric($userId)) {
        confirmUser((int)$userId);
        die('success confirmed');
    }

    die('error');
}

die('No userId');
