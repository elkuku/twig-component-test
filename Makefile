SHELL := /bin/bash

tests: export APP_ENV=test
tests:
	symfony console doctrine:database:drop --force || true
	symfony console doctrine:database:create
	symfony console doctrine:migrations:migrate -n
	symfony console doctrine:fixtures:load -n
	symfony php bin/phpunit --testdox $@
	vendor/bin/phpstan
	vendor/bin/rector process --dry-run
	#tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --dry-run --diff
.PHONY: tests
citests: export APP_ENV=test
citests:
	bin/console debug:dotenv
	bin/console doctrine:database:drop --force || true
	bin/console doctrine:database:create
	bin/console doctrine:migrations:migrate -n
	bin/console doctrine:fixtures:load -n
	bin/phpunit --testdox
	vendor/bin/phpstan
	#tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --dry-run
