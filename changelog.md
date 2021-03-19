# Changelog
## 11-2-2021 1.0.4
- [+] Permissions checking directly instead by role in InodeQuery.
## 11-2-2021 1.0.4
- [+] Change file name min length from 3 to 1.
## 13-1-2020 1.0.3
- [+] Added UniqueFilenameInFolderValidator. In use in rename action. Prevents a file to be renamed with an existing one.
## 9-12-2020 1.0.2
- [-] Removed sluggableBehavior from Inode. Now using getSafeFilename
## 9-12-2020 1.0.1
- [+] Added getSafeFileName for directories
## 30-10-2020 1.0.0
- [+] getSafeFileName is now used on rename action too.
- [+] getSafeFileName accepts mixed value, allowing UploadedFile or string
## 27-10-2020 0.9.9
- [+] Create method getSafeFileName to allow customization of generated filenames by extending InodeClass
## 19-10-2020 0.9.8
- [*] Add missing return on deleteDirInternal
## 26-10-2020 0.9.6
- [*] Fix an issue when deleting a file caused an error.
## 15-10-2020 0.9.5
- [+] Delete method instances every model before delete to trigger all events.
- [*] BulkDelete action and DeleteAction now relies on main inode delete method
- [*] Use beforeDelete instead delete to allow better event management
## 13-10-2020 0.9.4
- [+] Added an event just after file has been inserted into the file system. Useful when you need to work with the content stored
## 1-10-2020 0.9.3
- [*] Replace setColumns with getColumns. setColumns is now deprecated on Gridview
## 24-9-2020 0.9.2
- [*] Fixed bug: select all does not enable bulk actions button.
## 23-9-2020 0.9.1
- [+] Shift+click is now supported for multiple selection.
- [+] Rows selected are highlighted
## 16-9-2020 0.9
- [+] Now permissions can be applied to all descentants
## 7-9-2020 0.8.1
- [+] Remove the need of delete previous permissions. Now a new permission definition override previous if it exists
## 26-8-2020 0.8
- [*] Use file instead of stream for download action, to prevent issues when stream is not seekable
- [*] No longer use the version name. Only for identifying the version original name when uploaded.
## 30-7-2020 0.7
- [+] Display icon when no write permission, in gridview.
- [+] Added download button in InodeActionColumn
- [+] Added page size links in Gridview
## 27-7-2020 0.6
- [+] Add deep search function
- [*] Improve inode query. Now permissions filter makes use of permissions in addition to roles
## 21-7-2020 0.5
- [*] Fixed an issue in bulk download. Now downloaded files are the latest version.
## 16-6-2020 0.4
- [*] Remove no needed rules from Inode model.
## 27-5-2020 0.3
- [+] Added bulk download action. Files are downloaded as a zip.
- [+] Added `getAclPermissions()` and `rbacItems` property to allow displaying options in inode permission editor.
- [*] Role is now a dropdown. No longer a text input.
- [*] Permissions is now a dropdown. No longer a checkboxList.
