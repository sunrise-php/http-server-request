<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-message/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-message
 */

namespace Sunrise\Http\ServerRequest;

/**
 * List of upload errors
 *
 * @var array<int, string>
 *
 * @link https://www.php.net/manual/en/features.file-upload.errors.php
 */
const UPLOAD_ERRORS = [
    \UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    \UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    \UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
    \UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
    \UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
    \UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
    \UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension',
];
