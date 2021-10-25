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
use function sprintf;
use function sscanf;

/**
 * Gets the request protocol version from the given server parameters
 *
 * @param array<string, mixed> $server
 *
 * @return string
 *
 * @link http://php.net/manual/en/reserved.variables.server.php
 * @link https://datatracker.ietf.org/doc/html/rfc3875#section-4.1.16
 */
function request_protocol(array $server) : string
{
    if (!isset($server['SERVER_PROTOCOL'])) {
        return '1.1';
    }

    // "HTTP" "/" 1*digit "." 1*digit
    sscanf($server['SERVER_PROTOCOL'], 'HTTP/%d.%d', $major, $minor);

    // e.g.: HTTP/1.1
    if (isset($minor)) {
        return sprintf('%d.%d', $major, $minor);
    }

    // e.g.: HTTP/2
    if (isset($major)) {
        return sprintf('%d', $major);
    }

    return '1.1';
}
