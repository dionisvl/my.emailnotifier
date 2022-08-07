<?php

declare(strict_types=1);

/**
 * Крон команда рассылает уведомления всем пользователям у которых через 3 дня истекает подписка.
 *
 * Запускается позже и реже чем проверка e-мейлов на валидность (но внутри себя все равно проверяет их).
 */

require_once getenv('PROJECT_DIR') . '/config.php';
require_once getenv('PROJECT_DIR') . '/functions.php';
require_once getenv('PROJECT_DIR') . '/DataProvider/emails.php';
require_once getenv('PROJECT_DIR') . '/DataProvider/users.php';
require_once getenv('PROJECT_DIR') . '/DataProvider/notify_queue.php';

/**
 * Обработка всех пользователей с истекающими подписками.
 */
// 1. Ищем пользователей, у которых подписка истекла и е-мейл не прошел проверку на валидность.
$usersAndNotCheckedEmails = getExpiresUsersWithNotValidatedEmail($DBH);
$count = count($usersAndNotCheckedEmails);
if ($count === 0) {
    mylog('No users with expired subscription');
} else {
    mylog('Найдено пользователей с истекшими подписками и непроверенными е-мейлами: ' . $count);
    // 2. запускаем процесс валидации емейлов для данного массива.
    runParallelValidateEmails($usersAndNotCheckedEmails);
}

// 3. Ищем пользователей, у которых подписка истекла и е-мейл прошел проверку на валидность с успехом.
$users = getExpiresUsersWithGoodValidatedEmail($DBH);
mylog('Найдено пользователей с истекшими подписками и валидно проверенными е-мейлами: ' . count($users));
// 4. Тех пользователей у которых подписка истекла делим на пачки фиксированного размера.
$users_batches = array_chunk($users, 100);

try {
    foreach ($users_batches as $key => $users_batch) {
        mylog('стартовала отправка уведомлений пользователям. Пачка №' . $key . '.');
        // 5. Каждую пачку добавляем в очередь на отправку уведомлений.
        addUsersToQueue($DBH, $users_batch);
        // 6. Запускаем процесс отправки уведомлений из очереди.
        runNotifySendQueue($DBH);
        mylog('Пачка отправки уведомлений пользователям ' . $key . ' завершена. Количество пользователей в пачке: ' .
            count($users_batch) . PHP_EOL);
        // 7. переходим к следующей пачке до тех пор, пока пачки пользователей не закончатся.
    }
} catch (Throwable $e) {
    mylog(
        'Ошибка в процессе отправки очереди уведомлений: ' . $e->getMessage() . $e->getTraceAsString()
    );
    return false;
}
// 8. Очищаем таблицу очереди на отправку уведомлений.
truncateQueue($DBH);
