current_dir = $(shell pwd)

all: cs dunit dunit-nightly unit
travis: cs unit-travis
contrib: cs dunit unit

init:
	if [ ! -d vendor ]; then composer install; fi;

cs: init
	$(current_dir)/vendor/bin/phpcs --standard=PSR2 src/

unit: init
	$(current_dir)/vendor/bin/phpunit --coverage-text --coverage-html covHtml

unit-travis: init
	$(current_dir)/vendor/bin/phpunit --coverage-text --coverage-clover ./build/logs/clover.xml

dunit: init
	$(current_dir)/vendor/bin/dunit

dunit-nightly: init
	$(current_dir)/vendor/bin/dunit -c .dunitconfig-nightly

coveralls: init
	if [ -f ./build/logs/clover.xml ]; then ./vendor/bin/coveralls; fi
