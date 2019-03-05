## HTTP Server Request wrapper for PHP 7.1+ based on PSR-7 & PSR-17

[![Gitter](https://badges.gitter.im/sunrise-php/support.png)](https://gitter.im/sunrise-php/support)
[![Build Status](https://api.travis-ci.com/sunrise-php/http-server-request.svg?branch=master)](https://travis-ci.com/sunrise-php/http-server-request)
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

## Api documentation

https://phpdoc.fenric.ru/

## Useful links

* https://www.php-fig.org/psr/psr-7/
* https://www.php-fig.org/psr/psr-17/

## Team

<table>
    <tbody>
        <tr>
            <td>
                <img src="https://avatars2.githubusercontent.com/u/9021747?s=72&v=4">
                <br>
                <a href="https://github.com/peter279k">@peter279k</a>
            </td>
            <td>
                <img src="https://avatars1.githubusercontent.com/u/2872934?s=72&v=4">
                <br>
                <a href="https://github.com/fenric">@fenric</a>
            </td>
        </tr>
    </tbody>
</table>
