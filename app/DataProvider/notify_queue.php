<?php

declare(strict_types=1);


function addUsersToQueue(array $users): void
{
    $DBH = getConnection();
    $sql = 'INSERT INTO notify_queue (user_id) VALUES ';
    $values = [];
    foreach ($users as $user) {
        $values[] = '(' . $user['id'] . ')';
    }
    $sql .= implode(',', $values);
    $STH = $DBH->query($sql);
    $STH->execute();
}

// Получим первый ID пользователя, что найдем в таблице очереди.
function getFirstUserIdFromQueue(): ?int
{
    $DBH = getConnection();
    $STH = $DBH->query('SELECT user_id FROM notify_queue WHERE notify_at IS NULL ORDER BY id LIMIT 1');
    $STH->setFetchMode(PDO::FETCH_ASSOC);
    $row = $STH->fetch();
    if ($row === false) {
        return null;
    }
    return (int)$row['user_id'];
}

function setNotifyAtToUser(int $userId): void
{
    $DBH = getConnection();
    $stm = $DBH->prepare('UPDATE notify_queue SET notify_at = :time WHERE user_id = :user_id');
    $stm->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stm->bindValue(':time', date('Y-m-d H:i:s'));
    $stm->execute();
}


function truncateQueue(): void
{
    $DBH = getConnection();
    $stm = $DBH->prepare('TRUNCATE table notify_queue');
    $stm->execute();
}