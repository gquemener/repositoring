.DEFAULT_GOAL := all

.PHONY: test
test: phpunit.xml
	docker-compose run --rm php sh -c "/usr/bin/wait && phpunit --colors --testdox"

phpunit.xml:
	cp phpunit.xml.dist phpunit.xml

.PHONY: clean
clean:
	-rm phpunit.xml
	docker-compose down

.PHONY: all
all: test clean
