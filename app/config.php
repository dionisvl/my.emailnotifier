<?php

define("MYSQL_USER", getenv('MYSQL_USER'));
define("MYSQL_PASSWORD", getenv('MYSQL_PASSWORD'));
define("DB_NAME", getenv('MYSQL_DATABASE'));
define("DB_HOST", getenv('DB_HOST'));

const DEFAULT_EMAIL = 'myEmail@mail.com';

$DBH = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
    MYSQL_USER,
    MYSQL_PASSWORD,
    [PDO::ATTR_PERSISTENT => true],
);

