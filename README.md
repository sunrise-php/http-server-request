## HTTP server request wrapper for PHP 7.1+ (incl. PHP 8) based on PSR-7 & PSR-17

[![Gitter](https://badges.gitter.im/sunrise-php/support.png)](https://gitter.im/sunrise-php/support)
[![Build Status](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/badges/build.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-server-request/v/stable)](https://packagist.org/packages/sunrise/http-server-request)
[![Total Downloads](https://poser.pugx.org/sunrise/http-server-request/downloads)](https://packagist.org/packages/sunrise/http-server-request)
[![License](https://poser.pugx.org/sunrise/http-server-request/license)](https://packagist.org/packages/sunrise/http-server-request)

## Installation

```bash
composer require sunrise/http-server-request
```

## How to use?

```php
use Sunrise\Http\ServerRequest\ServerRequestFactory;

$request = ServerRequestFactory::fromGlobals();

// just use PSR-7 methods...
```

## Test run

```bash
php vendor/bin/phpunit
```

## Useful links

* https://www.php-fig.org/psr/psr-7/
* https://www.php-fig.org/psr/psr-17/
