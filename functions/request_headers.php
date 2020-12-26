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
 * Gets the request headers from the given server environment
 *
 * MUST NOT be used outside of this package.
 *
 * @param array $server
 *
 * @return array
 *
 * @link http://php.net/manual/en/reserved.variables.server.php
 */
function request_headers(array $server) : array
{
    $result = [];
    foreach ($server as $key => $value) {
        if (! (0 === \strncmp('HTTP_', $key, 5))) {
            continue;
        }

        $name = \substr($key, 5);
        $name = \strtolower($name);
        $name = \strtr($name, '_', ' ');
        $name = \ucwords($name);
        $name = \strtr($name, ' ', '-');

        $result[$name] = $value;
    }

    return $result;
}
