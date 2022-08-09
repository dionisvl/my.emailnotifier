<?php

declare(strict_types=1);

require_once getenv('PROJECT_DIR').'/config.php';
require_once getenv('PROJECT_DIR').'/functions.php';

// Получить все е-мейлы с непроверенным статусом
function getNotCheckedEmails(): array
{
    $DBH = getConnection();
    return $DBH
        ->query("SELECT * FROM emails WHERE check_status = 0")
        ->fetchAll(PDO::FETCH_ASSOC);
}

// обновить статус валидации е-мейла на указанный
function updateEmailStatus(int $id, int $newStatus): void
{
    $DBH = getConnection(false);
    $stm = $DBH->prepare("UPDATE emails SET check_status = :check_status WHERE email_id = :email_id");
    $stm->bindValue(':check_status', $newStatus, PDO::PARAM_INT);
    $stm->bindValue(':email_id', $id, PDO::PARAM_INT);
    $stm->execute();
}

// Получить е-мейл по его ID
function getEmailById(PDO $DBH, int $id): array
{
    $stm = $DBH->prepare("SELECT * FROM emails WHERE email_id = :email_id");
    $stm->bindValue(':email_id', $id, PDO::PARAM_INT);
    $stm->execute();
    return $stm->fetch(PDO::FETCH_ASSOC);
}

/**
 * Найти все e-mails по переданному списку email_id и указанному статусу
 * @param int[] $emailIds
 */
function getEmailsByIdListAndStatus(PDO $DBH, array $emailIds,int $needStatus): array
{
    $inQueryEmailIds = implode(',', array_fill(0, count($emailIds), '?'));

    $stm = $DBH->prepare('SELECT * FROM emails WHERE email_id IN(' . $inQueryEmailIds . ') AND check_status = :needStatus');
    foreach ($emailIds as $k => $id) {
        $stm->bindValue(($k + 1), $id, PDO::PARAM_INT);
    }
    $stm->bindValue(':needStatus', $needStatus, PDO::PARAM_INT);
    $stm->execute();
    return $stm->fetch(PDO::FETCH_ASSOC);
}

