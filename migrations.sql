USE emailnotifier;

DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS emails;
DROP TABLE IF EXISTS notify_queue;

################################### EMAILS
CREATE TABLE emails
(
    email_id     INT AUTO_INCREMENT PRIMARY KEY,
    email        CHAR(254)  NOT NULL,
    check_status TINYINT(2) NOT NULL DEFAULT 0 COMMENT '0 - не проверен, 1 - валидный, 2 - не валидный',
    INDEX check_status_index (check_status) USING BTREE
)
    COMMENT ='данные проверок емейлов на валидность';
drop procedure IF EXISTS seedEmails;
CREATE PROCEDURE seedEmails(countRows INT)
BEGIN
    DECLARE i int DEFAULT 1;
    DECLARE dynamicEmail CHAR(255) DEFAULT 'default@email.com';
    DECLARE dynamicStatus int DEFAULT 0;
    WHILE i <= countRows
        DO
            SET dynamicEmail = CONCAT(SUBSTR(MD5(RAND()), 1, 15), '@gmail.com');
            SET dynamicStatus = (RAND() * (3 - 1) + 1) - 1;
            insert into emailnotifier.emails(email, check_status) values (dynamicEmail, dynamicStatus);
            SET i = i + 1;
        END WHILE;
END;
CALL seedEmails(1000);
################################### USERS
CREATE TABLE users
(
    id        INT AUTO_INCREMENT PRIMARY KEY,
    username  CHAR(255)  NOT NULL COMMENT 'имя',
    email_id  INT,
    validts   TIMESTAMP  NOT NULL DEFAULT (NOW() + INTERVAL 14 DAY) COMMENT 'unix ts до которого действует ежемесячная подписка',
    confirmed TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'подтвержден ли пользователем по ссылке из письма',
    FOREIGN KEY (email_id) REFERENCES emails (email_id),
    INDEX validts_confirmed_index (validts, confirmed) USING BTREE
)
    COMMENT ='Табл. с пользователями';
drop procedure IF EXISTS seedUsers;
CREATE PROCEDURE seedUsers(countRows INT)
BEGIN
    DECLARE i int DEFAULT 1;
    DECLARE dynamicUserName CHAR(255) DEFAULT 'default_username';
    WHILE i <= countRows
        DO
            SET dynamicUserName = CONCAT('name_', SUBSTR(MD5(RAND()), 1, 8));
            insert into emailnotifier.users(username, email_id, validts, confirmed)
            values (dynamicUserName, RAND() * (500 - 1) + 1, (NOW() + INTERVAL 2 DAY), RAND());
            SET i = i + 1;
        END WHILE;
END;
CALL seedUsers(1000);

################################### notify_queue
CREATE TABLE notify_queue
(
    id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT NOT NULL COMMENT 'пользователь которому надо отправить уведомление',
    notify_at TIMESTAMP DEFAULT NULL COMMENT 'Дата-время когда было отправлено'
)
    COMMENT ='Табл. с очередью для отправки уведомлений пользователям';