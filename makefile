.PHONY: test
test:
	bin/phpunit
	yarn test
	@echo "\n---\nAll tests passed 🎉"
