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

# Cleanup
.PHONY: clean
clean:
	rm -r output

