# Yii2 Files Catalog - Developer Guidelines

This document provides guidelines and information for developers working on the Yii2 Files Catalog extension.

## Build/Configuration Instructions

### Prerequisites
- PHP 7.0 or higher
- MySQL/MariaDB
- Composer

### Installation

1. **Install via Composer**:
   ```bash
   composer require eseperio/yii2-files-catalog
   ```

2. **Configure the module in your Yii2 application**:
   ```php
   'modules' => [
       'filex' => [
           'class' => \eseperio\filescatalog\FilesCatalogModule::class,
           'identityClass' => 'app\models\User', // Your user identity class
           'salt' => 'your-secret-salt-key'
       ],
   ],
   ```

3. **Configure storage component**:
   ```php
   'components' => [
       'storage' => [
           'class' => 'creocoder\flysystem\LocalFilesystem',
           'path' => '@webroot/uploads'
       ],
       // ... other components
   ]
   ```

4. **Run migrations**:
   ```bash
   php yii migrate --migrationPath=@vendor/eseperio/yii2-files-catalog/src/migrations
   ```

## Testing Information

### Test Environment Setup

1. **Create a database for testing**:
   ```sql
   CREATE DATABASE yii2_files_catalog;
   ```

2. **Configure environment variables**:
   Create a `.env` file in the `tests` directory with the following content:
   ```
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=yii2_files_catalog
   DB_USER=your_db_user
   DB_PASS=your_db_password
   DB_CHARSET=utf8
   ```

3. **Create test configuration**:
   If not already present, create a `test.php` file in `tests/_app/config/` that extends the main configuration with test-specific settings:
   ```php
   <?php
   $config = require __DIR__ . '/main.php';
   
   // Test-specific configuration
   $config['id'] = 'yii2-files-catalog-tests';
   $config['components']['cache'] = ['class' => 'yii\caching\DummyCache'];
   $config['modules']['filex']['enableACL'] = false;
   
   return $config;
   ```

### Running Tests

The project uses Codeception for testing. The following test suites are available:

- **Unit Tests**: Test individual components
- **Functional Tests**: Test application functionality
- **Acceptance Tests**: Test user interactions

To run tests, use the following commands:

```bash
# Run all tests
composer test

# Run specific test suites
composer test:unit
composer test:functional
composer test:acceptance

# Run tests with code coverage
composer test:coverage
```

### Creating New Tests

1. **Create a new test file**:
   - For functional tests, create a file in `tests/functional/` with the suffix `Cest.php`
   - For unit tests, create a file in `tests/unit/` with the suffix `Test.php`

2. **Example of a functional test**:
   ```php
   <?php
   
   class ModuleLoadingCest
   {
       public function _before(FunctionalTester $I)
       {
           // Setup code
       }
   
       public function checkModuleLoading(FunctionalTester $I)
       {
           $I->amOnPage('/');
           $I->expectTo('see that the module is loaded');
           $I->seeElement('.navbar-brand');
           $I->see('Test App');
           $I->see('Home');
       }
   }
   ```

3. **Run your test**:
   ```bash
   php vendor/bin/codecept run functional YourTestCest
   ```

### Test Application

The project includes a test application in `tests/_app/` that can be used for manual testing:

```bash
# Start the test application server
composer serve
```

This will start a web server at http://localhost:8080 with the test application.

## Additional Development Information

### Code Style

The project follows PSR-2 coding standards. Key points:

- Use 4 spaces for indentation
- Class names should be in PascalCase
- Method names should be in camelCase
- Constants should be in UPPER_CASE

### Module Structure

- **actions/**: Action classes
- **assets/**: Asset bundles
- **behaviors/**: Behavior classes
- **controllers/**: Controller classes
- **models/**: Model classes
- **migrations/**: Database migrations
- **views/**: View files
- **widgets/**: Widget classes

### Key Components

- **FilesCatalogModule**: The main module class
- **Inode**: The core model representing files and directories

### Debugging

- Enable debug mode in your application's configuration:
  ```php
  'bootstrap' => ['debug'],
  'modules' => [
      'debug' => [
          'class' => 'yii\debug\Module',
      ],
      // ... other modules
  ]
  ```

- Check logs in `runtime/logs/` for error information

### Working with the Test App

The test app is configured to use a local database and file storage. It's useful for testing changes without setting up a full Yii2 application.

To make changes to the test app configuration, modify the files in `tests/_app/config/`.
