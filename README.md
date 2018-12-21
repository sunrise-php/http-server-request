## HTTP Server Request wrapper for PHP 7.1+ based on PSR-7 & PSR-17

[![Gitter](https://badges.gitter.im/sunrise-php/support.png)](https://gitter.im/sunrise-php/support)
[![Build Status](https://api.travis-ci.com/sunrise-php/http-server-request.svg?branch=master)](https://travis-ci.com/sunrise-php/http-server-request)
[![CodeFactor](https://www.codefactor.io/repository/github/sunrise-php/http-server-request/badge)](https://www.codefactor.io/repository/github/sunrise-php/http-server-request)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-server-request/v/stable)](https://packagist.org/packages/sunrise/http-server-request)
[![Total Downloads](https://poser.pugx.org/sunrise/http-server-request/downloads)](https://packagist.org/packages/sunrise/http-server-request)
[![License](https://poser.pugx.org/sunrise/http-server-request/license)](https://packagist.org/packages/sunrise/http-server-request)

## Installation

```
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

## Api documentation

https://phpdoc.fenric.ru/

## Useful links

https://www.php-fig.org/psr/psr-7/<br>
https://www.php-fig.org/psr/psr-17/
