FROM php:8.0-cli-alpine

RUN apk add postgresql-dev
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pgsql

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS
RUN pecl install xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/
RUN apk del .build-deps

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG phpunit_version
RUN wget https://phar.phpunit.de/phpunit-$phpunit_version.phar
RUN chmod +x phpunit-$phpunit_version.phar
RUN mv phpunit-$phpunit_version.phar /usr/bin/phpunit

ARG wait_version
ADD https://github.com/ufoscout/docker-compose-wait/releases/download/$wait_version/wait /usr/bin/wait
RUN chmod +x /usr/bin/wait

CMD ["php", "-v"]
