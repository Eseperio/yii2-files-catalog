
**UNDER DEVELOPMENT**
This extension is not yet stable

# yii2-files-catalog

It is a virtual file system with access control lists.

I have replicated the main principles of *nix filesystem where any object is represented
by an inode in the disk, and each inode have a different identity (directory, file, symlink).
What you get is a virtual file system that can rely on any existing file system, thanks to the usage of
[FlySystem](https://flysystem.thephpleague.com/docs/usage/filesystem-api/) in a deep layer,


## Installation

This extension is distributed as a composer library. Run
```
composer require eseperio/yii2-files-catalog
```

Then run migration
```
php yii migrate/up --migrationPath=@vendor/eseperio/yii2-files-catalog/src/migrations
```

## Versioning.

This module supports file versioning. You can set how much files must be kept.

## Access control

Inodes access control is performed by ACLs. Any inode must have a rule associated in order to give access to it.
Access can be granted to a user id or a role.

## Customization
You can customize any element of the module by overriding the classes in container definitions.
Gridview uses column classes, controller uses actions, and so on.


## Usage

### Actions available
There is a default controller with the following actions.


| Action | Description |
|---|---|
|Index| Displays the contents of a given dir. Accepts param `uuid` to select which directory to show|
|View| Only for inodes of type `file`. If files is image or pdf, it displays on screen, otherwise downloads the file |
|Properties| Displays properties of the file or directory selected|
|Upload| Action to handle file uploads|
|NewFolder| Displays the "create directory" form|



