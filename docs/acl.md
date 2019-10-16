# Access control

Access control is managed using ACLs (access control list).
Each INODE must grant access explicitly to a user or a role.

To edit permissions of an Inode go to the properties view. If you comply
with admin requirements you´ll see the permission editor panel.
To be considered as an administrator you must set configuration either
`administrators` or `administratorPermissionName`.


## Giving access to INODE.

### By user id
Manually add the user id.

### By role or permission.
Those users with that permission or role will be able to view the inode,
according to the permission mask given.

## Permissions

Permissions are given using a three bit binary mask.
The mask is stored as an integer in the database.


| BITS | 0 | 0 | 0 |
|---|---|---|---|---|
|  |READ|WRITE|DELETE|




