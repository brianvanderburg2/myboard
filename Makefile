# Configuration
override SHELL:=/bin/bash
override SHELLOPTS:=errexit:pipefail
export SHELLOPTS

override NAME:=myboard
override VERSION:=$(shell git describe --always)
override DATE:=$(shell date +%Y%m%d)


# Test target
.PHONY: tests
tests:

# Server target
.PHONY: server
server: 

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

# Cleanup
.PHONY: clean
clean:
	rm -r output

# Test
.PHONY: test
test: CONFIG=test/config.php
test: PORT=8080
test: HOST=localhost
test:
	mkdir -p output/test
	cp test/index.php output/test/index.php
	cp $(CONFIG) output/test/config.php
	php -S $(HOST):$(PORT) -t output/test

