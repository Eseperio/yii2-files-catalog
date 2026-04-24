#!/bin/sh
set -eu

cd /app

cp tests/.env.docker tests/.env
composer install --no-interaction --no-progress --prefer-dist
php tests/_app/yii migrate --interactive=0
mkdir -p build/coverage
vendor/bin/codecept run unit --coverage-text --coverage-xml build/coverage/unit --coverage-html build/coverage/unit-html
cp tests/_output/coverage.txt build/coverage/unit.coverage.txt
vendor/bin/codecept run functional --coverage-text --coverage-xml build/coverage/functional --coverage-html build/coverage/functional-html
cp tests/_output/coverage.txt build/coverage/functional.coverage.txt
echo 'UNIT COVERAGE'
cat build/coverage/unit.coverage.txt
echo 'FUNCTIONAL COVERAGE'
cat build/coverage/functional.coverage.txt
