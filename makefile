.PHONY: test
test:
	XDEBUG_MODE=coverage bin/phpunit --coverage-html var/coverage
	yarn test
	@echo "\n---\nAll tests passed 🎉"
