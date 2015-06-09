# Configuration
override SHELL:=/bin/bash
override SHELLOPTS:=errexit:pipefail
export SHELLOPTS

override NAME:=myboard
override VERSION:=$(shell git describe --always)
override DATE:=$(shell date +%Y%m%d)
override ROOTDIR:=$(shell pwd)

# Tarball
.PHONY: tarball-src tarball-doc tarball
tarball-src: PREFIX:=$(NAME)-$(DATE)-$(VERSION)
tarball-src:
	mkdir -p output
	git archive --format=tar --prefix=$(PREFIX)/ HEAD | xz > output/$(PREFIX).tar.xz
	gpg --detach-sign --armour  output/$(PREFIX).tar.xz

tarball-doc: PREFIX:=$(NAME)-$(DATE)-$(VERSION)-doc
tarball-doc: doc
	tar -cJf output/$(PREFIX).tar.xz -C output/doc/html --xform 's#^\.#$(PREFIX)#S' .
	gpg --detach-sign --armour  output/$(PREFIX).tar.xz

tarball: tarball-src tarball-doc

# Documentation
.PHONY: doc
doc:
	mkdir -p output
	test ! -e output/doc/html || rm -rf output/doc/html
	doxygen
	test ! -e output/doc/doxygen_sqlite3.db || rm output/doc/doxygen_sqlite3.db

# Cleanup
.PHONY: clean
clean:
	rm -r output

# Tests (unit tests)
.PHONY: tests
tests: PHPUNIT_OPTS:=--test-suffix _test.php
tests:
	phpunit $(PHPUNIT_OPTS) framework/tests/

# Test servers
# The test servers have simple configurations for Apache, Lighttpd, nginx, and PHP
# They still require an external MySQL server.  The configuration file used for
# the test is specified by CONFIG= and defaults to test/config.php
##################################################################################

.PHONY: config
config: CONFIG=test/config.php
config: PORT=8080
config: SSLPORT=8081
config: HOST=localhost
config:
	mkdir -p output/test
	mkdir -p output/run
	mkdir -p output/config
	mkdir -p output/data
	cp $(CONFIG) output/test/config.php
	cp test/index.php output/test/index.php
	python test/substio.py -i test/config -o output/config \
		ROOTDIR=$(ROOTDIR) \
		PORT=$(PORT) SSLPORT=$(SSLPORT)


# PHP
.PHONY: start-php stop-php
start-php: stop config
	/usr/sbin/php5-fpm --daemonize --fpm-config $(ROOTDIR)/output/config/php/php-fpm.conf \
		--no-php-ini --php-ini $(ROOTDIR)/output/config/php/php.ini

stop-php:
	test ! -f $(ROOTDIR)/output/run/php5-fpm.pid || kill `cat $(ROOTDIR)/output/run/php5-fpm.pid`

# Apache
.PHONY: start-apache stop-apache
start-apache: stop config start-php

stop-apache:

# Lighttpd
.PHONY: start-lighttpd stop-lighttpd
start-lighttpd: stop config start-php
	/usr/sbin/lighttpd -f $(ROOTDIR)/output/config/lighttpd/lighttpd.conf
	
stop-lighttpd:
	test ! -f $(ROOTDIR)/output/run/lighttpd.pid || kill `cat $(ROOTDIR)/output/run/lighttpd.pid`

# nginx
.PHONY: start-nginx stop-nginx
start-nginx: stop config start-php
	/usr/sbin/nginx -p $(ROOTDIR) -c $(ROOTDIR)/output/config/nginx/nginx.conf -g 'error_log stderr;'

stop-nginx:
	test ! -f $(ROOTDIR)/output/run/nginx.pid || kill `cat $(ROOTDIR)/output/run/nginx.pid`

# Stop everything
.PHONY: stop
stop: stop-php stop-apache stop-lighttpd stop-nginx

