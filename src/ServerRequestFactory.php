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
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ServerRequestFactory
 *
 * @link https://www.php-fig.org/psr/psr-17/
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{

    /**
     * Creates the server request instance from superglobals variables
     *
     * @param array|null $serverParams
     * @param array|null $queryParams
     * @param array|null $cookieParams
     * @param array|null $uploadedFiles
     * @param array|null $parsedBody
     *
     * @return ServerRequestInterface
     *
     * @link http://php.net/manual/en/language.variables.superglobals.php
     * @link https://www.php-fig.org/psr/psr-15/meta/
     */
    public static function fromGlobals(
        ?array $serverParams = null,
        ?array $queryParams = null,
        ?array $cookieParams = null,
        ?array $uploadedFiles = null,
        ?array $parsedBody = null
    ) : ServerRequestInterface {
        $serverParams  = $serverParams  ?? $_SERVER ?? [];
        $queryParams   = $queryParams   ?? $_GET    ?? [];
        $cookieParams  = $cookieParams  ?? $_COOKIE ?? [];
        $uploadedFiles = $uploadedFiles ?? $_FILES  ?? [];
        $parsedBody    = $parsedBody    ?? $_POST   ?? [];

        return new ServerRequest(
            request_method($serverParams),
            request_uri($serverParams),
            request_headers($serverParams),
            request_body(),
            null,
            request_http_version($serverParams),
            $serverParams,
            $queryParams,
            $cookieParams,
            request_files($uploadedFiles),
            $parsedBody
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []) : ServerRequestInterface
    {
        // TODO: query from URI and cookies from the server environment (HTTP_COOKIE)...

        return new ServerRequest(
            $method,
            $uri,
            request_headers($serverParams),
            null,
            null,
            request_http_version($serverParams),
            $serverParams
        );
    }
}
