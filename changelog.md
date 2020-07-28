# Changelog
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
