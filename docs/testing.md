# Testing

## Running test app

Copy `.env.dist` to `.env` and set the required parameters.  
Install composer dependencies, then run `composer run serve` to start a local server.  
If not done, run `composer run init-db`.

## Composer scripts for testing

The `composer.json` file includes several scripts to facilitate testing and development tasks.  
Some useful scripts are:

- `composer run test` — Runs the unit and functional suites.
- `composer run test:unit` — Runs unit tests.
- `composer run test:functional` — Runs functional tests.
- `composer run test:acceptance` — Runs acceptance tests.
- `composer run test:coverage` — Runs unit and functional tests with coverage output.
- `composer run test-docker` — Runs the unit and functional suites inside Docker and emits coverage.
- `composer run serve` — Starts the test application server.
- `composer run migrate` — Runs database migrations for the test environment.
- `composer run load-fixtures` — Loads test fixtures.

You can see all available scripts by running:

```bash
composer run
```

## Coverage and CI

The repository now includes a GitHub Actions workflow at `.github/workflows/tests.yml`.
It runs the unit and functional suites against MySQL and enables Xdebug coverage on the main branch.
The unit suite uses the Yii2 and Db modules, so it can exercise model and helper logic with fixtures.
Docker-based test runs use `tests/.env.docker` and the compose file at `docker/docker-compose.test.yml`.

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
