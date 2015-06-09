# Configuration
override SHELL:=/bin/bash
override SHELLOPTS:=errexit:pipefail
export SHELLOPTS

override NAME:=myboard
override VERSION:=$(shell git describe --always)
override DATE:=$(shell date +%Y%m%d)
override ROOTDIR:=$(shell pwd)

# Tarball
.PHONY: tarball
tarball: PREFIX:=$(NAME)-$(DATE)-$(VERSION)
tarball:
	mkdir -p output
	git archive --format=tar --prefix=$(PREFIX)/ HEAD | xz > output/$(PREFIX).tar.xz
	gpg --detach-sign --armour  output/$(PREFIX).tar.xz

# Documentation
.PHONY: docs
docs:
	mkdir -p output
	doxygen
	-rm output/doc/doxygen_sqlite3.db

# Cleanup
.PHONY: clean
clean:
	rm -r output

# Tests (unit tests)
.PHONY: tests
tests: PHPUNIT_OPTS:=--test-suffix _test.php
tests:
	phpunit $(PHPUNIT_OPTS) framework/tests/

# We use supervisor (a python process management app) to
# start the needed services
.PHONY: config
config: CONFIG=test/config.php
config: PORT=8080
config: SSLPORT=8081
config: HOST=localhost
config:
	mkdir -p output/test
	mkdir -p output/run
	mkdir -p output/config
	cp $(CONFIG) output/test/config.php
	cp test/index.php output/test/index.php
	python test/substio.py -i test/config -o output/config \
		ROOTDIR=$(ROOTDIR) \
		PORT=$(PORT) SSLPORT=$(SSLPORT)

.PHONY: start
start: config
	mkdir -p output/run/supervisor
	supervisord -c $(ROOTDIR)/output/config/supervisor/supervisord.conf

.PHONY: stop
stop:
	supervisorctl -c $(ROOTDIR)/output/config/supervisor/supervisord.conf shutdown

.PHONY: control
control:
	supervisorctl -c $(ROOTDIR)/output/config/supervisor/supervisord.conf
