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
use Psr\Http\Message\UriInterface;
use Sunrise\Uri\UriFactory;

/**
 * Gets the request URI
 *
 * MUST NOT be used outside of this package.
 *
 * @param array $server
 *
 * @return UriInterface
 *
 * @link http://php.net/manual/en/reserved.variables.server.php
 */
function request_uri(array $server) : UriInterface
{
	if (\array_key_exists('HTTPS', $server))
	{
		if (! ('off' === $server['HTTPS']))
		{
			$scheme = 'https://';
		}
	}

	if (\array_key_exists('HTTP_HOST', $server))
	{
		$domain = $server['HTTP_HOST'];
	}
	else if (\array_key_exists('SERVER_NAME', $server))
	{
		$domain = $server['SERVER_NAME'];

		if (\array_key_exists('SERVER_PORT', $server))
		{
			$domain .= ':' . $server['SERVER_PORT'];
		}
	}

	if (\array_key_exists('REQUEST_URI', $server))
	{
		$target = $server['REQUEST_URI'];
	}
	else if (\array_key_exists('PHP_SELF', $server))
	{
		$target = $server['PHP_SELF'];

		if (\array_key_exists('QUERY_STRING', $server))
		{
			$target .= '?' . $server['QUERY_STRING'];
		}
	}

	return (new UriFactory)->createUri(
		($scheme ?? 'http://') .
		($domain ?? 'localhost') .
		($target ?? '/')
	);
}
