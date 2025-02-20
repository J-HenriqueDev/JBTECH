# php-extended/php-simple-cache-filesystem
A psr-16 compliant cache that uses filesystems to store cache data.

![coverage](https://gitlab.com/php-extended/php-simple-cache-filesystem/badges/master/pipeline.svg?style=flat-square)
![build status](https://gitlab.com/php-extended/php-simple-cache-filesystem/badges/master/coverage.svg?style=flat-square)


## Installation

The installation of this library is made via composer and the autoloading of
all classes of this library is made through their autoloader.

- Download `composer.phar` from [their website](https://getcomposer.org/download/).
- Then run the following command to install this library as dependency :
- `php composer.phar php-extended/php-simple-cache-filesystem ^6`


## Basic Usage

This library is to make a man in the middle for http requests and responses
and logs the events when requests passes. It may be used the following way :

```php

use PhpExtended\SimpleCache\SimpleCacheFilesystem;

$cache = new SimpleCacheFilesystem('/path/to/cache/directory');

$itemToStore = '<data>';

$cache->set($key, $itemToStore);

$data = $cache->get($key);	// retrieves the $itemToStore

```

To work, this library must be used only with items that are serializable. If
items that are not serializable are given to this library, they will be 
silently ignored as cache miss.


## License

MIT (See [license file](LICENSE)).
