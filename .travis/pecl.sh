#!/bin/sh

set +xe

phpenv config-add .travis/php.ini
php -r 'exit(1 - version_compare(PHP_VERSION, "7", "<"));' && export RAPHF_VER=-1.1.2 PROPRO_VER=-1.0.2 HTTP_VER=-2.5.6 || true
pecl install raphf$RAPHF_VER < /dev/null
pecl install propro$PROPRO_VER < /dev/null
pecl install pecl_http$HTTP_VER < /dev/null

