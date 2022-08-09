init: docker-down-clear \
	docker-up

up: docker-up
down: docker-down

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans

bash:
	docker-compose run --rm php-fpm /bin/sh

test-notify:
	docker-compose run --rm php-fpm php app/cron/notifyUsers.php

test-email-validate:
	docker-compose run --rm php-fpm php app/cron/validateEmails.php