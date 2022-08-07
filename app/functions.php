<?php

declare(strict_types=1);

// Проверяет емейл на валидность и возвращает 0 или 1. Заглушка. Функция работает от 1 секунды до 1 минуты. Вызов функции платный.
function check_email(string $email): bool
{
    sleep(random_int(1, 60));
    return true;
}

// Отсылает емейл. Заглушка. Работает от 1 секунды до 10 секунд
function send_email(
    string $email,
    string $from,
    string $to,
    string $subj,
    string $body
): void {
    mylog('send email: ' . $email . ' - ' . $from . ' - ' . $to . ' - ' . $subj . ' - ' . $body);
    sleep(random_int(1, 10));
}

// Достает из БД пользователя. Формирует письмо, передает его сервису отправки писем.
function sendEmail(PDO $DBH, int $userId): void
{
    $stm = $DBH->prepare("SELECT * FROM users JOIN emails ON emails.email_id = users.email_id WHERE users.id = :id");
    $stm->bindValue(':id', $userId, PDO::PARAM_INT);
    $stm->execute();
    $user = $stm->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        throw new RuntimeException("userId=$userId not found");
    }
    $userEmail = $user['email'];
    $userName = $user['username'];

    $msg = "{$userName}, your subscription is expiring soon";
    send_email($userEmail, DEFAULT_EMAIL, $userName, $msg, $msg);
}

function mylog(string $msg): void
{
    $logFilePath = 'log.' . date('Y-m-d') . '.log';
    $cronLogFile = fopen($logFilePath, 'ab');
    fwrite($cronLogFile, date('Y-m-d H:i:s') . ' : ' . $msg . PHP_EOL);
    fclose($cronLogFile);
}

/**
 * Команда делает множество параллельных проверок емейлов на валидность.
 */
function runParallelValidateEmails(array $emails)
{
    $children = [];
    foreach ($emails as $emailRow) {
        switch ($pid = pcntl_fork()) {
            case -1:
                throw new RuntimeException("Unable to fork process");
            case 0:
                try {
                    $DBH = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, MYSQL_USER, MYSQL_PASSWORD,);
                    $result = check_email($emailRow['email']);
                    if ($result === true) {
                        updateEmailStatus($DBH, (int)$emailRow['email_id'], 1);
                    } else {
                        updateEmailStatus($DBH, (int)$emailRow['email_id'], 2);
                    }
                } catch (Throwable $e) {
                    mylog(
                        'Ошибка в процессе проверки е-мейла: ' . $emailRow['email'] . ': '
                        . $e->getMessage() . $e->getTraceAsString()
                    );
                }
                exit();
            default:
                $children[] = $pid;
                break;
        }
    }
    // Waiting for children to finish
    foreach ($children as $pid) {
        pcntl_waitpid($pid, $status);
        // На этом этапе дочерний процесс успешно завершил работу
    }
    return true;
}

/**
 * Берем из очереди пользователей и отправляем письма пока пользователи в очереди не закончатся
 */
function runNotifySendQueue(PDO $DBH): void
{
    while (true) {
        $userId = getFirstUserIdFromQueue($DBH);
        if ($userId === null) {
            break;
        }
        sendEmail($DBH, $userId);
        setNotifyAtToUser($DBH, $userId);
    }
}