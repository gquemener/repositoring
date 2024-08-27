.DEFAULT_GOAL := all
export SHELL = /bin/bash
export SHELLOPTS:=$(if $(SHELLOPTS),$(SHELLOPTS):)pipefail:errexit

.ONESHELL:

UID := $(shell id -u)
GID := $(shell id -g)

export UID
export GID

.PHONY: test
test: phpunit.xml vendor
	docker compose run --rm php sh -c "/usr/bin/wait && phpunit"

.PHONY: check
check: vendor
	docker compose run --rm --no-deps php phpstan --no-progress --ansi
	docker compose run --rm --no-deps php deptrac --ansi
	docker compose run --rm --no-deps -e PHP_CS_FIXER_IGNORE_ENV=1 php php-cs-fixer fix --dry-run --ansi --show-progress=none --diff
	docker compose run --rm --no-deps php composer audit
	docker compose run --rm --no-deps php composer outdated --strict

.PHONY: update-deps
update-deps:
	docker compose run --rm --no-deps php composer update

vendor:
	docker compose run --rm --no-deps php composer install

phpunit.xml:
	cp phpunit.xml.dist phpunit.xml

.PHONY: clean
clean:
	rm -f phpunit.xml
	rm -rf vendor
	docker compose down

.PHONY: all
all:
	function tearDown {
		$(MAKE) clean
	}
	trap tearDown EXIT
	$(MAKE) -k check test
