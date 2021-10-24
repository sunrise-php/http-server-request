## HTTP server request wrapper for PHP 7.1+ based on PSR-7 and PSR-17

[![Build Status](https://circleci.com/gh/sunrise-php/http-server-request.svg?style=shield)](https://circleci.com/gh/sunrise-php/http-server-request)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sunrise-php/http-server-request/?branch=master)
[![Total Downloads](https://poser.pugx.org/sunrise/http-server-request/downloads?format=flat)](https://packagist.org/packages/sunrise/http-server-request)
[![Latest Stable Version](https://poser.pugx.org/sunrise/http-server-request/v/stable?format=flat)](https://packagist.org/packages/sunrise/http-server-request)
[![License](https://poser.pugx.org/sunrise/http-server-request/license?format=flat)](https://packagist.org/packages/sunrise/http-server-request)

---

## Installation

```bash
composer require 'sunrise/http-server-request:^2.0'
```

## How to use?

```php
use Sunrise\Http\ServerRequest\ServerRequestFactory;

$request = ServerRequestFactory::fromGlobals();

// just use PSR-7 methods...
```

## Test run

```bash
composer test
```

---

## Useful links

* https://www.php-fig.org/psr/psr-7/
* https://www.php-fig.org/psr/psr-17/
