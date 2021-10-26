<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-server-request/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-server-request
 */

namespace Sunrise\Http\ServerRequest;

/**
 * Import functions
 */
use function strncmp;
use function strtolower;
use function strtr;
use function substr;
use function ucwords;

/**
 * Gets the request headers from the given server parameters
 *
 * @param array<string, mixed> $server
 *
 * @return array<string, string>
 *
 * @link http://php.net/manual/en/reserved.variables.server.php
 * @link https://datatracker.ietf.org/doc/html/rfc3875#section-4.1.18
 */
function request_headers(array $server) : array
{
    // https://datatracker.ietf.org/doc/html/rfc3875#section-4.1.2
    if (!isset($server['HTTP_CONTENT_LENGTH']) && isset($server['CONTENT_LENGTH'])) {
        $server['HTTP_CONTENT_LENGTH'] = $server['CONTENT_LENGTH'];
    }

    // https://datatracker.ietf.org/doc/html/rfc3875#section-4.1.3
    if (!isset($server['HTTP_CONTENT_TYPE']) && isset($server['CONTENT_TYPE'])) {
        $server['HTTP_CONTENT_TYPE'] = $server['CONTENT_TYPE'];
    }

    $result = [];
    foreach ($server as $key => $value) {
        if (0 <> strncmp('HTTP_', $key, 5)) {
            continue;
        }

        $name = strtr(substr($key, 5), '_', '-');
        $name = ucwords(strtolower($name), '-');

        $result[$name] = $value;
    }

    return $result;
}
