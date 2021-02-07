# set all to phony
SHELL=bash

.PHONY: *

DOCKER_CGROUP:=$(shell cat /proc/1/cgroup | grep docker | wc -l)

ifneq ("$(wildcard /.dockerenv)","")
    IN_DOCKER=TRUE
else ifneq ("$(DOCKER_CGROUP)","0")
	IN_DOCKER=TRUE
else
    IN_DOCKER=FALSe
endif

ifeq ("$(IN_DOCKER)","TRUE")
	DOCKER_RUN=
else
	DOCKER_RUN=docker run --rm -it \
		-v "`pwd`:`pwd`" \
		-w "`pwd`" \
		"wyrihaximusnet/php:7.4-nts-alpine3.12-dev"
endif

all: lint cs-fix cs stan psalm unit infection composer-require-checker composer-unused backward-compatibility-check

lint:
	$(DOCKER_RUN) vendor/bin/parallel-lint --exclude vendor .

cs:
	$(DOCKER_RUN) vendor/bin/phpcs --parallel=$(shell nproc)

cs-fix:
	$(DOCKER_RUN) vendor/bin/phpcbf --parallel=$(shell nproc)

stan:
	$(DOCKER_RUN) vendor/bin/phpstan analyse src tests --level max --ansi -c phpstan.neon

psalm:
	$(DOCKER_RUN) vendor/bin/psalm --threads=$(shell nproc) --shepherd --stats

unit:
	$(DOCKER_RUN) vendor/bin/phpunit --colors=always -c phpunit.xml.dist --coverage-text --coverage-html covHtml --coverage-clover ./build/logs/clover.xml --debug

unit-ci: unit
	if [ -f ./build/logs/clover.xml ]; then wget https://scrutinizer-ci.com/ocular.phar && sleep 3 && php ocular.phar code-coverage:upload --format=php-clover ./build/logs/clover.xml; fi

infection:
	$(DOCKER_RUN) vendor/bin/infection --ansi --min-msi=100 --min-covered-msi=100 --threads=$(shell nproc)

composer-require-checker:
	$(DOCKER_RUN) vendor/bin/composer-require-checker --ignore-parse-errors --ansi -vvv --config-file=composer-require-checker.json

composer-unused:
	$(DOCKER_RUN) composer unused --ansi

backward-compatibility-check:
	$(DOCKER_RUN) vendor/bin/roave-backward-compatibility-check || true
