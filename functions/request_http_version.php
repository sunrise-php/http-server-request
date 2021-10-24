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
use function preg_match;

/**
 * Gets the request HTTP version from the given server environment
 *
 * @param array<string, mixed> $server
 *
 * @return string
 *
 * @link http://php.net/manual/en/reserved.variables.server.php
 */
function request_http_version(array $server) : string
{
    static $regex = '/^HTTP\/(\d(?:\.\d)?)$/';

    if (isset($server['SERVER_PROTOCOL'])) {
        if (preg_match($regex, $server['SERVER_PROTOCOL'], $matches)) {
            return $matches[1];
        }
    }

    return '1.1';
}
