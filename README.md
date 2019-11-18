# VCR Plugin

[![Latest Version](https://img.shields.io/github/release/php-http/vcr-plugin.svg?style=flat-square)](https://github.com/php-http/vcr-plugin/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/php-http/vcr-plugin.svg?style=flat-square)](https://travis-ci.org/php-http/vcr-plugin)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/php-http/vcr-plugin.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-http/vcr-plugin)
[![Quality Score](https://img.shields.io/scrutinizer/g/php-http/vcr-plugin.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-http/vcr-plugin)
[![Total Downloads](https://img.shields.io/packagist/dt/php-http/vcr-plugin.svg?style=flat-square)](https://packagist.org/packages/php-http/vcr-plugin)

**Record your test suite's HTTP interactions and replay them during future test runs.**

## Install

Via Composer

``` bash
$ composer require --dev php-http/vcr-plugin
```

## Usage

```php
<?php

use Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy;
use Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder;
use Http\Client\Plugin\Vcr\RecordPlugin;
use Http\Client\Plugin\Vcr\ReplayPlugin;

$namingStrategy = new PathNamingStrategy();
$recorder = new FilesystemRecorder('some/dir/in/vcs'); // You can use InMemoryRecorder as well

// To record responses:
$record = new RecordPlugin($namingStrategy, $recorder);

// To replay responses:
$replay = new ReplayPlugin($namingStrategy, $recorder);
```

## Testing

``` bash
$ composer test
```


## Contributing

Please see our [contributing guide](http://docs.php-http.org/en/latest/development/contributing.html).


## Security

If you discover any security related issues, please contact us at [security@php-http.org](mailto:security@php-http.org).


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
