# Developing #

```
#!shell

vagrant up

vagrant ssh

composer install

composer test

```


# Installation

Through Composer: 

```
#!shell

composer require Roberth91/filesystem
```

OR

add to relevant composer.json blocks

```
#!json


"repositories": [
        {
            "type": "git",
            "url": "git@github.com:Roberth91/filesystem"
        }
    ]
 
"require": {
        "Roberth91/filesystem": "^0.0.1"
    }
```

# Usage #


```
#!php

use Filesystem\FileUpload\Configuration\Configuration;
use Filesystem\FileUpload\UploaderFactory;

//Grab the uploader factory
$uploaderFactory = new UploaderFactory();

//Set up config to pass through to driver, ssh key optional. For S3Driver pass hostname in format bucket-name:s3-region.
$config = new Configuration('host:port', 'username', 'password', 'remoteDir');

// Optionally set the timeout for SFTP (90 second default)
$config->setTimeout(30);

//Choose driver type: ftp, ftps, sftp, s3, (local - unused) and pass through config
$driver = $uploaderFactory->getUploader('ftp', $config);

$driver->connect();

$driver->upload(new \SplFileInfo('/path/to/file'), '/path/to/file');

$driver->download('remoteFileName', new \SplFileInfo('/path/to/file'));

$driver->write('content', '/path/to/file');

$driver->read('/path/to/file');

$driver->listDirectory('/path/to/directory');

$driver->makeDirectory('/path/to/directory');

$driver->fileSize('/path/to/file');

$driver->disconnect();
```