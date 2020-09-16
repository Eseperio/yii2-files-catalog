# Access control lists

This module allow files access restriction using access control list.

Each item have three actions controlled.

|Action|Description|
|------|-----------|
|Read| Allows reading the file and accessing its own information (properties, download, etc)|
|Write| Any operation involving modification of the item (add versions, subdirectories...)|
|Delete| Allow deletion of the item|

Permissions **are not inherited**. Each item has its own acl.

When granting access to an item, it can be done in three ways

- **By user id**: A user id is specified and that user will get granted the access.
- **By role**: All users with that role will have access to the file. **Permissions are ignored. Only roles**
- **Wildcard**: Instead writing a role name, you can use a wildcard permission. Currently there are two accepted.
    - `*` Means everyone.
    - `@` Means everyone logged in




### How access control crud mask works

Access control is stored in a different table. Each inode must have its own records defining who or which role
will be able to view, edit or append files.
That permissions are managed via a crud_mask. It is a 3 bit binary mask, in its integer representation



| |Read|Write|Delete|
|---|----|-----|------|
|Bit|0|0|0|
|Value|4|2|1|

So if we want to give only read access to a file, the crud binary mask must be `0100', or its integer representation, which is what we store in database: 4
Otherwise, if we want all permissions, then all bits are on and the result is 7.
