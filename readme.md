
# Yii2 files catalog
## yii2-files-catalog


**Developer: waizabu.com**

It is a virtual file system with access control lists.

I have replicated the main principles of *nix filesystem where any object is represented
by an inode in the disk, and each inode have a different identity (directory, file, symlink).
What you get is a virtual file system that can rely on any existing file system, thanks to the usage of
[FlySystem](https://flysystem.thephpleague.com/docs/usage/filesystem-api/) in a deep layer,


## License
Free for NonCommercial use. Otherwise contact us for a commercial license.
## Installation

This extension is distributed as a composer library. Run
```
composer require eseperio/yii2-files-catalog
```

Then run migration
```
php yii migrate/up --migrationPath=@vendor/eseperio/yii2-files-catalog/src/migrations
```

Add the module to your modules configuration
```
'modules' => [
     'filex' => [
            'class' => \eseperio\filescatalog\FilesCatalogModule::class,
            'salt' => 'yourrandomstringhere'
            'identityClass' => 'youridentity/classname',
            'administrators' => ['adminusername']
            // 'administratorPermissionName' => 'permissionname'
        ]
      ]

```


To manage access control list, add administrators to module configuration.
## Versioning.

This module supports file versioning. You can set how much files must be kept. File versioning can be disabled via configuration

## Access control

Inodes access control is performed by ACLs. Any inode must have a rule associated in order to give access to it.
Access can be granted to a user id or a role.

To know more about access control, see [access control docs](docs/acl.md)

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
|Rename| Allows renaming an inode|
|BulkDelete| Bulk deletion|
|BulkDownload|Download many files at the same time|
|RemoveAcl|Remove permissions from an Inode|

### Configuration

|Property|Description|Default|
|--------|-----------|-------|
|`maxFileSize`|  int the maximum number of bytes required for the uploaded file. Defaults to null, meaning no limit. Note, the size limit is also affected by `upload_max_filesize` and `post_max_size` INI setting and the 'MAX_FILE_SIZE' hidden field value. See [[FileValidator::getSizeLimit()]] for details. @see https://secure.php.net/manual/en/ini.core.php#ini.upload-max-filesize @see https://secure.php.net/post-max-size @see FileValidator::getSizeLimit|null|
|`directory`|  string This will be used as default directory where all files will be created. Set to false to use your  default storage component|'filex'|
|`maxVersions`|  int number of maximun versions of a files that can be kept.|4|
|`storage`|  string name of the component responsible of handling files. Must comply with flysystem.|'storage'|
|`user`|  string The user component. This is used on blameable behavior|'user'|
|`userIdAttribute`|  string attribute of the user component|'id'|
|`userNameAttribute`|  string user attribute that returns the name. Can be a anything valid for [[ArrayHelper::getValue()]]|'username'|
|`db`|  string Name of the db component to use on data handling|'db'|
|`usePjax`|  bool whether use pjax on main view|true|
|`inodeRealPathCallback`|**  null|array|\Closure Callable used to bypass current inodeRealPath calculation|null|
|`allowOverwrite`|  bool whether overwrite existing files. Remember this setting can be overrided in calls tu save|false| 
|`prefix`|  string the prefix to be used on urlGroup|'filex'|
|`urlRules`| array the url rules (routes)|'<controller:[\w\-]+>|<action:[\w\-]+>' => '<controller>|<action>'|
|`maxTreeDepthDisplay`|  int the max amount of elements to display when using a tree view. Set to false to disable|4|
|`groupFilesByExt`|  bool whether show icons grouped by extension|false|
|`displayAuthorNames`|  bool whether display author names on views|true|
|`routePrefix`|  string the prefix for the route part of every rule declared in [[rules]]. The prefix and the route will be separated with a slash. If this property is not set, it will take the value of [[prefix]].|"filesCatalog"|
|`realFileNamesSystem`|  string which kind of name use on saving files. Defaults to FILENAMES_BY_ID. Files will be stored using its own id, so an attacker can not find a file based on their public uuid. If you want to preserve an easy way to find physical FILENAMES_BY_ID: File 1979 will become prefix|1|9|7|9|1979 FILENAMES_BY_UUID: File 146d8c31-ca60-411f-b112-7dd1bc5e8e46 will become prefix|14|6d|8c|31|ca|60|41|1f|b1|12|7d|d1|bc|5e|8e|46|146d8c31-ca60-411f-b112-7dd1bc5e8e46 FILENAMES_REAL will create parent directories with the name of the parent virtual directories.|self::FILENAMES_BY_ID|
|`browserInlineMimeTypes`|  array list of the mimetypes that can be represented directly in browser with their corresponding tag||
|`enableACL`|  bool whether enable access control list|true|
|`administrators`| List of roles or usernames that can manage acl|\['admin'\]|
|`aclException`| Classname of the exception to be thrown when user can access an inode|`eseperio\filescatalog\exceptions\FilexAccessDeniedException`|
|`defaultACLmask`|Default value for access control crud mask when no one has been defined|4|
|`maxInlineFileSize`| Since this module relies on Flysystem, you can not have a direct link to the file, so in order to preview images or mp4 videos they are converted to base64. This number limits the maximun size allowed for a file to be embedded.  int max inline file size in bytes. Defaults to 10Mb|10000000|
|`checkFilesIntegrity`|  bool whether save file hashes in database and check integrity everytime a file is required.   In large filesystems it can make the database grow significantly.|true|
|`allowVersioning`|  bool whether allow multiple versions of a file.|true|
|`identityClass`| string the class name of the [[identity]] object.|null|
|`salt`|String to be used as hash salt on sensitive operations, like delete|null|
|`defaultInodePermissions`|list with default permissions for inodes|\[AccessControl::ACTION_READ\]
|`secureHashParamName`|name of the parameter to be used when sending and receiving secure hash|fxsh|
|`secureHashAlgorithm`| which algorithm use for secure hash generation| SHA3-256|
|`newFolderIconclass`|css classname for the new folder icon |'glyphicon glyphicon-folder-open';
|`propertiesIconClass`|css classname for the properties icon |glyphicon glyphicon-list-alt
|`addFilesIconClass`|css classname for the new add files icon |glyphicon glyphicon-cloud-upload|
|`showBreadcrumbButtonLabels`|whether display labels in breadcrumb buttons|false|
|`itemsPerPage`|number of items per page|10|
|`rbacItems`|array|callable Array with the available permissions or roles available while managing inode permissions|[]|
|`readOnlyMessage`|string|message to display in gridview as a name suffix when user has no write permissions.|🔒|






### Other
In order to improve privacy inodes use the uuid as public pk, but an integer as internal pk. Keep simple id private. When using data providers, remember use the one included within this module.
This module use adjacency models concept to manage nesting. That requires extra queries to get parents or childrens, but is way more efficient than nested-set pattern on system that require a lot of nodes and writes


#### To do:
- [ ] Check whether new version is of the same type of previous file. Allow disable this via module config.
- [ ] Improve how different InodeTypes are handled. Currently only File type allows versioning, making it so much different from the other types.
