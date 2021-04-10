FROM php:8.0-cli-alpine

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG phpunit_version
RUN wget https://phar.phpunit.de/phpunit-$phpunit_version.phar
RUN chmod +x phpunit-$phpunit_version.phar
RUN mv phpunit-$phpunit_version.phar /usr/bin/phpunit

CMD ["php", "-v"]
