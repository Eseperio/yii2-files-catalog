# Testing

## Running test app

Copy .env.dist to .env and set params. 
Install composer dependencies, then run composer run serve. This starts a local server. If not done, run composer
init-db.


## Running tests

Install needed libraries using composer:

```bash
composer install --dev
```

## Configure codeception:

Copy files codeception.dist.yml to `codeception.yml`.
This file has been configured to use environment variables. You can set them in your system or create a file
named `.env` in the root directory of the project.

```bash
cp codeception.dist.yml codeception.yml
```

## Connect to database

Create a database named yii2_files_catalog and import file coopify_tests.sql. You can found it in test data directory


