<?php

$userName = 'testUserName';
$msg = "{$userName}, your subscription is expiring soon";
echo $msg;


// работает от 1 секунды до 1 минуты
function check_email(): void
{
	sleep(1);
}	

// работает от 1 секунды до 10 секунд
function send_email(): void
{
	sleep(2);
}	