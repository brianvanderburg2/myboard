#!/usr/bin/env python

"""
This is a simple text substitution script which reads data from stdin and
writes the data back to stdout.  A list of replacements is specified on
the command line in the form of search=replace.  Patterns in the input
file in the form of @key@ will be replaced by the replacement.  Patterns
of @@ will be replaced by a single @.
"""

import sys
import os
import re
import argparse

version = '20141110'


# Message functons
def error(message):
    sys.stderr.write('Error: ' + message + '\n')
    sys.stderr.flush()
    sys.exit(1)


# Parse the command line
parser = argparse.ArgumentParser(description='Simple text file substitution/template script')
parser.add_argument('-i', dest='input', action='store', required=False, help='input file or directory')
parser.add_argument('-o', dest='output', action='store', required=False, help='output file or directory')
parser.add_argument('params', action='store', nargs='*', help='name to value mapping for template substitution')
result = parser.parse_args()

# Get the information
input = result.input
output = result.output
replacements = {}

for i in result.params:
    parts = i.split('=', 1)
    if len(parts) == 2:
        replacements[parts[0]] = parts[1]

# Absolute paths
if input:
    input = os.path.abspath(input)

if output:
    output = os.path.abspath(output)


# Replacement functions
def subfn(mo):
    key = mo.group(1);

    if len(key) == 0:
        return '@'

    if key in replacements:
        return replacements[key]

    error('key not present in subsitution data: ' + key)

_regex = re.compile('@(.*?)@')
def build(inhandle, outhandle):
    for line in inhandle:
        outhandle.write(_regex.sub(subfn, line))


# Handle input and output accordingly
if not input is None:
    # Input is a string
    if os.path.isdir(input):
        # Directory traversal
        if output is None or os.path.isfile(output):
            error('output must be a directory when input is a directory')

        for (dir, dirs, files) in os.walk(input):
            for file in files:
                sourcefile = os.path.join(dir, file)
                targetfile = os.path.join(output, os.path.relpath(sourcefile, input))

                if sourcefile == targetfile:
                    error('source and target can not be the same')

                if not os.path.isdir(os.path.dirname(targetfile)):
                    os.makedirs(os.path.dirname(targetfile))

                with open(sourcefile, 'rU') as inhandle:
                    with open(targetfile, 'wt') as outhandle:
                        build(inhandle, outhandle);

    elif os.path.isfile(input):
        # Input is a file
        if not output is None:
            if os.path.isdir(output):
                error('output must be a file when input is a file')

            if input == output:
                error('source and target can not be the same')

            if not os.path.isdir(os.path.dirname(output)):
                os.makedirs(os.path.dirname(output))

            with open(input, 'rU') as inhandle:
                with open(output, 'wt') as outhandle:
                    build(inhandle, outhandle)
        else:
            with open(input, 'rU') as inhandle:
                build(inhandle, sys.stdout)
    else:
        error('input file does not exist: ' + input)
else:
    # Input is sys.stdin
    if not output is None:
        if os.path.isdir(output):
            error('output must be a file when input is stdin')

        if not os.path.isdir(os.path.dirname(output)):
            os.makedirs(os.path.dirname(output))

        with open(output, 'wt') as outhandle:
            build(sys.stdin, outhandle)

    else:
        build(sys.stdin, sys.stdout)

