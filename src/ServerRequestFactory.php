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
use Psr\Http\Message\UriInterface;
use Sunrise\Stream\StreamFactory;
use Sunrise\Uri\UriFactory;

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
     * @param null|array $server
     * @param null|array $query
     * @param null|array $body
     * @param null|array $cookies
     * @param null|array $files
     *
     * @return ServerRequestInterface
     *
     * @link http://php.net/manual/en/language.variables.superglobals.php
     * @link https://www.php-fig.org/psr/psr-15/meta/
     */
    public static function fromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null
    ) : ServerRequestInterface {
        $server  = $server  ?? $_SERVER ?? [];
        $query   = $query   ?? $_GET    ?? [];
        $body    = $body    ?? $_POST   ?? [];
        $cookies = $cookies ?? $_COOKIE ?? [];
        $files   = $files   ?? $_FILES  ?? [];

        $request = (new ServerRequest)
            ->withProtocolVersion(request_http_version($server))
            ->withBody(request_body())
            ->withMethod(request_method($server))
            ->withUri(request_uri($server))
            ->withServerParams($server)
            ->withCookieParams($cookies)
            ->withQueryParams($query)
            ->withUploadedFiles(request_files($files))
            ->withParsedBody($body);

        foreach (request_headers($server) as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    /**
     * {@inheritDoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []) : ServerRequestInterface
    {
        if (! ($uri instanceof UriInterface)) {
            $uri = (new UriFactory)->createUri($uri);
        }

        $body = (new StreamFactory)->createStream();

        return (new ServerRequest)
            ->withMethod($method)
            ->withUri($uri)
            ->withServerParams($serverParams)
            ->withBody($body);
    }
}
