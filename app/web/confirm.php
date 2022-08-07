<?php

declare(strict_types=1);

require_once '../config.php';
require_once '../functions.php';
require_once '../DataProvider/users.php';

if (isset($_GET['userId'])) {
    $userId = base64_decode($_GET['userId']);
    if (is_numeric($userId)) {
        confirmUser($DBH, (int)$userId);
        die('success confirmed');
    }

    die('error');
}

die('No userId');
