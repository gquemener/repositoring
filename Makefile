.DEFAULT_GOAL := all

.PHONY: test
test: phpunit.xml
	docker-compose run --rm --user "$(id -u):$(id -g)" php sh -c "/usr/bin/wait && phpunit --colors --testdox"

.PHONY: install
install: composer.lock

composer.lock:
	docker-compose run --rm --user="$(id -u):$(id -g)" php composer install

phpunit.xml:
	cp phpunit.xml.dist phpunit.xml

.PHONY: clean
clean:
	-rm phpunit.xml
	-rm composer.lock
	docker-compose down

.PHONY: all
all: install test clean
