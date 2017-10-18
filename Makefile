php=php
perl=perl
composer=$(php) composer.phar
phpcs=$(php) vendor/bin/phpcs
phpunit=$(php) vendor/bin/phpunit
yaml2json=$(perl) -MJSON -MYAML -eprint -e'to_json(YAML::Load(join""=><>),{pretty=>1,canonical=>1})'
getversion=$(perl) -MYAML -eprint -e'YAML::Load(join""=><>)->{version}'
V=`$(getversion) < composer.yaml`

all: | vendor test

info:
	@echo $(php)
	@$(php) -v
	@echo $(perl)
	@$(perl) -v

clean:
	git clean -xdf -e composer.phar -e vendor

vendor: composer.json
	$(composer) --prefer-dist install >composer.out

composer.json: composer.yaml
	$(yaml2json) < $< > $@~
	mv $@~ $@
	-rm composer.lock
	git add $@

test: lint vendor
	$(phpcs) --warning-severity=0 --standard=PSR2 --ignore=src/Locales/Available.php src
	$(phpunit) --verbose tests/

lint:
	for file in `find src tests -name '*.php' | sort`; do $(php) -l $$file || exit 1; done

archive: | clean composer.json
	$(composer) archive

release:
	git push --all
	git tag -m "Release version $V" -s v$V
	git push --tags

.PHONY: all info clean test archive release
