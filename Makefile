.DEFAULT_GOAL := all
export SHELL = /bin/sh
export SHELLOPTS:=$(if $(SHELLOPTS),$(SHELLOPTS):)pipefail:errexit

.ONESHELL:

UID := $(shell id -u)
GID := $(shell id -g)

export UID
export GID

.PHONY: test
test: phpunit.xml composer.lock
	docker-compose run --rm php sh -c "/usr/bin/wait && phpunit"

.PHONY: check
check: composer.lock
	docker-compose run --rm --no-deps php phpstan --no-progress --ansi
	docker-compose run --rm --no-deps php deptrac --ansi
	docker-compose run --rm --no-deps php php-cs-fixer fix --dry-run --ansi --show-progress=none --diff

composer.lock:
	docker-compose run --rm --no-deps php composer install

phpunit.xml:
	cp phpunit.xml.dist phpunit.xml

.PHONY: clean
clean:
	-rm phpunit.xml
	-rm composer.lock
	docker-compose down

.PHONY: all
all:
	function tearDown {
		$(MAKE) clean
	}
	trap tearDown EXIT
	$(MAKE) -k check test
