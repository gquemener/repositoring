.DEFAULT_GOAL := all
SHELL = /bin/sh

UID := $(shell id -u)
GID := $(shell id -g)

export UID
export GID

.PHONY: test
test: phpunit.xml
	docker-compose run --rm php sh -c "/usr/bin/wait && phpunit --colors --testdox"

.PHONY: install
install: composer.lock

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
all: install test clean
