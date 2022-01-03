PANTHER_ENV=panther

.PHONY: fixtures-dev fixtures-panther test-end2end schema-test test-unit-integration test-javascript php-parallel-lint php-code-sniffer phpstan test

fixtures-dev:
	bin/console hautelook:fixtures:load --purge-with-truncate --quiet --env dev

fixtures-panther:
	bin/console doctrine:database:drop --env ${PANTHER_ENV} --force
	bin/console doctrine:database:create --env ${PANTHER_ENV}
	bin/console doctrine:schema:update --quiet --env ${PANTHER_ENV} --force
	bin/console hautelook:fixtures:load --quiet --env ${PANTHER_ENV}

test-end2end: fixtures-panther
	yarn dev
	bin/phpunit --group end2end

schema-test:
	bin/console doctrine:database:drop --env test --force
	bin/console doctrine:database:create --env test
	bin/console doctrine:schema:update --quiet --env test --force

test-unit-integration: schema-test
	XDEBUG_MODE=coverage bin/phpunit --exclude-group end2end --coverage-html var/coverage

test-javascript:
	yarn test

php-parallel-lint:
	vendor/bin/parallel-lint --exclude .git --exclude vendor .

php-code-sniffer:
	vendor/bin/phpcs --standard=PSR12 -n src tests

phpstan:
	vendor/bin/phpstan analyse -l 0 src tests

test: php-parallel-lint php-code-sniffer phpstan test-unit-integration test-javascript test-end2end
	@echo "\n---\nAll tests passed 🎉"


