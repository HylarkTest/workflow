#! /bin/bash

# Install imagemagick and the PHP imagick extension on Mac, useful for running
# tests on the host machine.
brew install imagemagick

pecl install imagick-3.4.4
