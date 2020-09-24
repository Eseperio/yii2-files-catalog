# Changelog

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
