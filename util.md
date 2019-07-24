
# Internal utils

### Regex to extract params from filex module
```
\/\**([\s.\/\*\@\w\;\`\'\,\(\)-:\]\[\#]+\n)[\s]+public\s\$([\w]+)\s=\s(.*);
```

**Replacement **
```
|`$2`|$1|$3|
```

### After clean
```
\n\s+\*
\n\|
```
