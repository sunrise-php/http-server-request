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
 * Gets the request method
 *
 * MUST NOT be used outside of this package.
 *
 * @param array $server
 *
 * @return string
 *
 * @link http://php.net/manual/en/reserved.variables.server.php
 */
function request_method(array $server) : string
{
	return $server['REQUEST_METHOD'] ?? 'GET';
}
