FROM php:8.0.21-fpm-alpine3.16

# Setup some env
ENV \
	# Setup same composer home directory for all users
	COMPOSER_HOME="/usr/local/composer" \
	PHP_VERSION=php8 \
	PROJECT_DIR="/var/www/emailnotifier"

ARG PHP_EXTENSIONS="mysqli pdo_mysql pcntl"
# Install dependencies
RUN \
    docker-php-ext-install $PHP_EXTENSIONS

# pm.max_children
ADD ./www.conf /usr/local/etc/php-fpm.d/www.conf

# tweak custom php-fpm config
ADD ./php-my.ini /usr/local/etc/php/conf.d/05-php.ini

# Build dependencies.
ENV BUILD_DEPS \
    autoconf \
    file \
    cmake

# helper packages
RUN apk add --update \
        nano \
        wget \
        g++ \
        gcc \
        make

RUN \
    # system temp packages
    cd /tmp \
    && apk add --virtual .build-deps $BUILD_DEPS \
    #
    # Xdebug
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    #
    && apk del .build-deps

# Composer
RUN wget https://getcomposer.org/installer -O - -q \
    | php -- --install-dir=/bin --filename=composer --quiet

WORKDIR ${PROJECT_DIR}

COPY conf.d /usr/local/etc/php/conf.d
