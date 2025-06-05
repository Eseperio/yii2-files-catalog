# Testing

## Running test app

Copy `.env.dist` to `.env` and set the required parameters.  
Install composer dependencies, then run `composer run serve` to start a local server.  
If not done, run `composer run init-db`.

## Composer scripts for testing

The `composer.json` file includes several scripts to facilitate testing and development tasks.  
Some useful scripts are:

- `composer run test` — Runs all Codeception tests.
- `composer run test:unit` — Runs unit tests.
- `composer run test:functional` — Runs functional tests.
- `composer run test:acceptance` — Runs acceptance tests.
- `composer run test:coverage` — Runs tests with code coverage report.
- `composer run serve` — Starts the test application server.
- `composer run migrate` — Runs database migrations for the test environment.
- `composer run load-fixtures` — Loads test fixtures.

You can see all available scripts by running:

```bash
composer run
```

## Running tests

Install the required libraries using composer:

```bash
composer install --dev
```

## Configure Codeception

Copy the configuration file:

```bash
cp codeception.dist.yml codeception.yml
```

This file is configured to use environment variables. You can set them in your system or create a `.env` file in the root directory of the project.

## Connect to the database and run migrations

Create a database for tests and run Yii2 migrations before running the tests:

```bash
composer run migrate
```

You may also need to load fixtures:

```bash
composer run load-fixtures
```

Now you can run the tests using the composer scripts described above.

