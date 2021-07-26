.DEFAULT_GOAL := all
SHELL = /bin/sh

UID := $(shell id -u)
GID := $(shell id -g)

export UID
export GID

.PHONY: test
test: phpunit.xml composer.lock
	docker-compose run --rm php sh -c "/usr/bin/wait && phpunit --colors=always --testdox"

.PHONY: check
check: composer.lock
	docker-compose run --rm php phpstan --no-progress --ansi
	docker-compose run --rm php deptrac --ansi

composer.lock:
	docker-compose run --rm php composer install

phpunit.xml:
	cp phpunit.xml.dist phpunit.xml

.PHONY: clean
clean:
	-rm phpunit.xml
	-rm composer.lock
	docker-compose down

.PHONY: all
all: check test clean
