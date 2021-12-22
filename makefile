PANTHER_ENV=panther

.PHONY: fixtures-dev
fixtures-dev:
	bin/console hautelook:fixtures:load --purge-with-truncate --quiet --env dev

.PHONY: fixtures-panther
fixtures-panther:
	bin/console doctrine:database:drop --env ${PANTHER_ENV} --force
	bin/console doctrine:database:create --env ${PANTHER_ENV}
	bin/console doctrine:schema:update --env ${PANTHER_ENV} --force
	bin/console hautelook:fixtures:load --quiet --env ${PANTHER_ENV}

.PHONY: test
test: fixtures-panther
	XDEBUG_MODE=coverage bin/phpunit --coverage-html var/coverage
	yarn test
	@echo "\n---\nAll tests passed 🎉"

test-panther: fixtures-panther
	bin/phpunit tests/EndToEnd
