#!/usr/bin/env bash

cd "$(dirname "$0")/../"

echo "Tests complete ($TRAVIS_TEST_RESULT). Sending code coverage reports."

echo -en "travis_fold:start:scrutinizer_report\r"
wget https://scrutinizer-ci.com/ocular.phar
php ocular.phar code-coverage:upload --format=php-clover clover.xml
echo -en "travis_fold:end:scrutinizer_report\r"
