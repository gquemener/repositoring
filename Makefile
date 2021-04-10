.PHONY: test
test: phpunit.xml
	docker-compose run --rm php phpunit --colors

phpunit.xml:
	cp phpunit.xml.dist phpunit.xml
