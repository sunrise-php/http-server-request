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
 * Import classes
 */
use Psr\Http\Message\StreamInterface;
use Sunrise\Stream\StreamFactory;

/**
 * Gets the request body
 *
 * MUST NOT be used outside of this package.
 *
 * @return StreamInterface
 *
 * @link http://php.net/manual/en/wrappers.php.php
 */
function request_body() : StreamInterface
{
	$resource = \fopen('php://temp', 'r+b');

	\stream_copy_to_stream(\fopen('php://input', 'rb'), $resource);

	\rewind($resource);

	return (new StreamFactory)->createStreamFromResource($resource);
}
