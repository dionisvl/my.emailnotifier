<?php

declare(strict_types=1);

require_once getenv('PROJECT_DIR') . '/config.php';
require_once getenv('PROJECT_DIR') . '/functions.php';

/**
 * Получить всех пользователей у которых истекает подписка через 3 дня.
 * - Крайний случай когда у миллиона пользователей не валидный e-meil и одновременно истекает подписка не трогаем. Подразумеваем что это маловероятно.
 * - Пользователей, у которых подписка истекла не трогаем, потому что они будут получать уведомления от другого нашего сервиса.
 */
function getExpiresUsersWithGoodValidatedEmail(): array
{
    $DBH = getConnection(false);
    return $DBH
        ->query(
            "
        SELECT * FROM users 
            JOIN emails ON users.email_id = emails.email_id AND emails.check_status = 1
        WHERE validts BETWEEN NOW() AND NOW() + INTERVAL 3 DAY
            AND confirmed = 1
         "
        )
        ->fetchAll(PDO::FETCH_ASSOC);
}

function getExpiresUsersWithNotValidatedEmail(): array
{
    $DBH = getConnection(false);
    return $DBH
        ->query(
            "
        SELECT * FROM users 
            JOIN emails ON users.email_id = emails.email_id AND emails.check_status = 0 
        WHERE validts BETWEEN NOW() AND NOW() + INTERVAL 3 DAY
            AND confirmed = 1
         "
        )
        ->fetchAll(PDO::FETCH_ASSOC);
}

// Пользователь по ссылке из письма подтверждает что это он зарегистрировался этим е-мейлом.
function confirmUser(int $userId): void
{
    $DBH = getConnection();
    $stm = $DBH->prepare("UPDATE users SET confirmed = 1 WHERE id = :id");
    $stm->bindValue(':id', $userId, PDO::PARAM_INT);
    $stm->execute();
}


