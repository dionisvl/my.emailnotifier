<?php

declare(strict_types=1);

/**
 * Крон команда для регулярной проверки валидности всей нашей базы е-мейлов пользователей
 * Внешний платный сервис делает проверку валидности е-мейла и отвечает нам валиден он или нет.
 * Запускается раньше и чаще чем рассылка уведомлений.
 */

require_once getenv('PROJECT_DIR') . '/config.php';
require_once getenv('PROJECT_DIR') . '/functions.php';
require_once getenv('PROJECT_DIR') . '/DataProvider/emails.php';

$emails = getNotCheckedEmails();

$emails_batches = array_chunk($emails, 100);

foreach ($emails_batches as $key => $emails_batch) {
    echo('стартовала проверка е-мейлов номер ' . $key . '.' . PHP_EOL);
    runParallelValidateEmails($emails_batch);
    echo('Пачка проверок е-мейлов номер ' . $key . ' завершена. ' . 'Количество е-мейлов в пачке: '
        . count($emails_batch) . PHP_EOL);
}

