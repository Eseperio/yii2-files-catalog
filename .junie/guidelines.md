# Yii2 Files Catalog - Developer Guidelines

This document provides guidelines and information for developers working on the **Yii2 Files Catalog** extension.

## Overview

Yii2 Files Catalog is a **Yii2 library module** that implements a **virtual filesystem** in the database. It allows managing files, directories, and metadata as structured entities. Physical file storage is handled through the **Flysystem** abstraction layer, which enables using a variety of storage backends (local, S3, FTP, etc.).

At its core, the system is built around an entity called `Inode`, which dynamically adopts different roles:

* **Directory**: Represents a virtual folder
* **File**: Represents a stored file
* **Version**: Tracks file versions
* **Symlink**: Acts as a virtual shortcut to another inode

Access to items is governed by a **granular Access Control List (ACL)** system. This system supports:

* **User-specific permissions**
* **Permission-based access** by role or group

## Build/Configuration Instructions

### Prerequisites

* PHP 7.0 or higher
* MySQL/MariaDB
* Composer

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

3. **Configure the storage component using Flysystem**:

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
   If not already present, create a `test.php` file in `tests/_app/config/`:

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

The project uses Codeception. The following test suites are available:

* **Unit Tests**: Test individual components
* **Functional Tests**: Test application functionality
* **Acceptance Tests**: Test user interactions

Run tests using:

```bash
composer test              # Run all tests
composer test:unit         # Run unit tests
composer test:functional   # Run functional tests
composer test:acceptance   # Run acceptance tests
composer test:coverage     # Run tests with code coverage
```

### Creating New Tests

1. **Create a new test file**:

   * Functional tests: `tests/functional/YourTestCest.php`
   * Unit tests: `tests/unit/YourTestTest.php`

2. **Example of a functional test**:

   ```php
   <?php
   class ModuleLoadingCest
   {
       public function _before(FunctionalTester $I) {}

       public function checkModuleLoading(FunctionalTester $I)
       {
           $I->amOnPage('/');
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

A test application is included in `tests/_app/`. You can use it for manual testing:

```bash
composer serve
```

This will start a web server at [http://localhost:8080](http://localhost:8080).

## Additional Development Information

### Code Style

The project adheres to PSR-2:

* 4 spaces for indentation
* PascalCase for class names
* camelCase for method names
* UPPER\_CASE for constants

### Module Structure

* **actions/**: Action classes
* **assets/**: Asset bundles
* **behaviors/**: Behavior classes
* **controllers/**: Controller classes
* **models/**: Model classes
* **migrations/**: Database migrations
* **views/**: View templates
* **widgets/**: UI components

### Key Components

* **FilesCatalogModule**: Main module entry point
* **Inode**: Core model that represents filesystem nodes

### Debugging

Enable debugging with:

```php
'bootstrap' => ['debug'],
'modules' => [
    'debug' => [
        'class' => 'yii\debug\Module',
    ],
],
```

Logs are available in `runtime/logs/`.

### ACL System

The ACL system is hierarchical and supports inheritance and specificity. You can:

* Grant access to individual users
* Grant access based on roles or named permissions
* Inherit permissions from parent nodes

Each inode's access rights are checked dynamically according to this logic.

---

For more advanced topics, refer to the inline documentation and comments throughout the codebase.
