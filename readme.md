# Сервис для рассылки уведомлений об истекающих подписках

- для валидации емейлов используется сторонний сервис, который отвечает от 1 до 60 секунд. Для асинхронной обработки
  базы емейлов тут используется pcntl_fork().
    - По крону происходит регулярная валидация всей БД емейлов.
- для отправки емейлов используется очередь на основе базы данных (MySQL)
    - по крону каждый день проверяется вся БД пользователей на наличие истекающих подписок и отправка уведомлений им.
    - Если внешний сервис отправки емейлов поддерживает динамическое изменение одновременного кол-ва отправляемых
      писем,  
      тогда мы тоже сможем реализовать параллельную отправку писем в сервис. По примеру того как сделано с валидацией
      е-мейлов.
- есть роут `http://emailnotifier.local/confirm.php?confirmCode=base64_encode($userId)` принимающий confirm_url из писем
  пользователей для подтверждения того что этот email соотв. его аккаунту.  
  Пример для userId=123: http://emailnotifier.local/confirm.php?confirmCode=MTIz

### Ньюансы
1. Вызов функции check_email платный, но для бизнеса важно чтобы БД емейлов была актуальна и поэтому мы поддерживаем её
   актуальность по крону.
   В противном случае если данная база не актуальна, тогда мы можем переделать алгоритм так, чтобы валидация происходила
   только по требованию в тот момент когда нужно.

2. Представим ситуацию когда одновременно заканчивается подписка у всех пользователей (1млн. строк).
   И все е-мейлы всех пользователей не проверены.
   В таком случае нам надо успеть разослать уведомления всем примерно за 3 дня до окончания подписки.

Один пользователь обрабатывается максимум 1 минуту 10 секунуд.
- Сколько времени нужно для тысячи воркеров? 
- 1000000/1000 = 1000 строк для работы каждого воркера.
- каждая строка = 70 секунд. 1000*70 = 70000 секунд.
- 70000/60 = 1166 минут / 60 = 19.4 часов   
Одна тысяча воркеров может обработать всех пользователей за 19 часов.

#### MySQL
При необходимости подредактируйте `max_connections` в my.cnf.

#### Тестовое окружение
- `make up`
- В файле C:\Windows\System32\drivers\etc\hosts добавить:
```
127.0.0.1 emailnotifier.local
```
- go to http://emailnotifier.local
- Запустить все миграции из файла `migrations.sql`
- при необходимости подредактировать параметры кол-ва нужных тестовых строк в процедурах `seedEmails` и `seedUsers`

### Сценарий тестирования
- Для того чтобы все тесты проходили в ускоренном режиме, тогда в методах-заглушках `send_email` и  `check_email` надо
  убрать sleep().
- запуск крона для валидации емейлов вручную:
  - `make bash`
  - `php cron/validateEmails.php`
  - смотрим табл emails, видим что у них нигде нет статуса `0`
- запуск крона для проверки подписок и отправки уведомлений вручную:
  - `make bash`
  - `php cron/notifyUsers.php`
  - Если в методе-заглушке `send_email` поставить sleep(1), тогда можно видеть как таблица notify_queue плавно
    заполняется.
  - смотрим в логи видим что уведомления отправились
- при каждом новом запуске тестов надо перезапускать миграции.
