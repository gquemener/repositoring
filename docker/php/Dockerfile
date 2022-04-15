# https://hub.docker.com/_/php?tab=tags
FROM php:8.1.4-cli-alpine

RUN apk add postgresql-dev
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pcntl

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS
RUN pecl install xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/
RUN apk del .build-deps

# https://hub.docker.com/_/composer?tab=tags
COPY --from=composer:2.2.7 /usr/bin/composer /usr/bin/composer

# https://github.com/sebastianbergmann/phpunit/tags
ARG phpunit_version
RUN wget https://phar.phpunit.de/phpunit-$phpunit_version.phar
RUN chmod +x phpunit-$phpunit_version.phar
RUN mv phpunit-$phpunit_version.phar /usr/bin/phpunit

# https://github.com/ufoscout/docker-compose-wait/tags
ARG wait_version
ADD https://github.com/ufoscout/docker-compose-wait/releases/download/$wait_version/wait /usr/bin/wait
RUN chmod +x /usr/bin/wait

# https://github.com/phpstan/phpstan/tags
ARG phpstan_version
RUN wget https://github.com/phpstan/phpstan/releases/download/$phpstan_version/phpstan.phar
RUN chmod +x phpstan.phar
RUN mv phpstan.phar /usr/bin/phpstan

# https://github.com/qossmic/deptrac/tags
ARG deptrac_version
RUN wget https://github.com/qossmic/deptrac/releases/download/$deptrac_version/deptrac.phar
RUN chmod +x deptrac.phar
RUN mv deptrac.phar /usr/bin/deptrac

# https://github.com/FriendsOfPHP/PHP-CS-Fixer/tags
ARG php_cs_fixer_version
RUN wget https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/$php_cs_fixer_version/php-cs-fixer.phar
RUN chmod +x php-cs-fixer.phar
RUN mv php-cs-fixer.phar /usr/bin/php-cs-fixer

CMD ["php", "-v"]
