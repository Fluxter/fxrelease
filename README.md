# Git Release script

## How to install
Require the package 
```bash
$ composer global require fluxter/fxrelease
```

After that, make sure composer vendor bin is inside your path!
```bash
$ export PATH="$PATH:$HOME/.composer/vendor/bin"
```

Voila!
```bash
$ fxrelease
```
should work now

## How to update
```bash
$ composer global update fluxter/fxrelease
```


## .fxrelease file
### Project ID (required)
```json 
{
    "projectId": 15,
}
```
### URL (required)
```json 
{
    "url": "https://gitlab.com",
}
```
### Single version file
```json
{
    "versionFile": "composer.json",
    "versionPattern": "\"version\": \"FXRELEASE_VERSION_HERE\"", 
}
```
### Mulitple version files
```json
{
    "versionFiles": [
        {
            "file": "composer.json",
            "pattern": "\"version\": \"FXRELEASE_VERSION_HERE\"",
        },
        {
            "file": "second_file.blub",
            "pattern": "my_version_is: FXRELEASE_VERSION_HERE",
        }
    ]
}
```
### Master branch (defaults to master)
```json
{
    "masterBranch": "prod"
}
```