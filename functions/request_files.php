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
use function is_array;

/**
 * Import constants
 */
use const UPLOAD_ERR_NO_FILE;

/**
 * Normalizes the given uploaded files
 *
 * Note that not sent files will not be handled.
 *
 * @param array $files
 *
 * @return array
 *
 * @link http://php.net/manual/en/reserved.variables.files.php
 * @link https://www.php.net/manual/ru/features.file-upload.post-method.php
 * @link https://www.php.net/manual/ru/features.file-upload.multiple.php
 * @link https://github.com/php/php-src/blob/8c5b41cefb88b753c630b731956ede8d9da30c5d/main/rfc1867.c
 */
function request_files(array $files) : array
{
    $walker = function ($path, $size, $error, $name, $type) use (&$walker) {
        if (! is_array($path)) {
            return new UploadedFile(
                $path,
                $size,
                $error,
                $name,
                $type
            );
        }

        $result = [];
        foreach ($path as $key => $_) {
            if (UPLOAD_ERR_NO_FILE <> $error[$key]) {
                $result[$key] = $walker(
                    $path[$key],
                    $size[$key],
                    $error[$key],
                    $name[$key],
                    $type[$key]
                );
            }
        }

        return $result;
    };

    $result = [];
    foreach ($files as $key => $file) {
        if (UPLOAD_ERR_NO_FILE <> $file['error']) {
            $result[$key] = $walker(
                $file['tmp_name'],
                $file['size'],
                $file['error'],
                $file['name'],
                $file['type']
            );
        }
    }

    return $result;
}
