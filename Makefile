.PHONY: test
test: phpunit.xml
	docker-compose run --rm php sh -c "/usr/bin/wait && phpunit --colors"

phpunit.xml:
	cp phpunit.xml.dist phpunit.xml
