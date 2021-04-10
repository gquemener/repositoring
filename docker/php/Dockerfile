FROM php:8.0-cli-alpine

RUN apk add postgresql-dev
RUN docker-php-ext-install pdo_pgsql

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG phpunit_version
RUN wget https://phar.phpunit.de/phpunit-$phpunit_version.phar
RUN chmod +x phpunit-$phpunit_version.phar
RUN mv phpunit-$phpunit_version.phar /usr/bin/phpunit

CMD ["php", "-v"]
