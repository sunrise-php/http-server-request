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
use Sunrise\Stream\StreamFactory;

/**
 * Normalizes the given uploaded files
 *
 * MUST NOT be used outside of this package.
 *
 * @param array $files
 *
 * @return array
 *
 * @link http://php.net/manual/en/reserved.variables.files.php
 */
function request_files(array $files) : array
{
	$walker = function($path, $size, $error, $name, $type) use(& $walker)
	{
		if (! \is_array($path))
		{
			$stream = (new StreamFactory)->createStreamFromFile($path, 'rb');

			return (new UploadedFileFactory)->createUploadedFile($stream, $size, $error, $name, $type);
		}

		$result = [];

		foreach ($path as $key => $value)
		{
			$result[$key] = $walker($path[$key], $size[$key], $error[$key], $name[$key], $type[$key]);
		}

		return $result;
	};

	$result = [];

	foreach ($files as $key => $file)
	{
		$result[$key] = $walker($file['tmp_name'], $file['size'], $file['error'], $file['name'], $file['type']);
	}

	return $result;
}
