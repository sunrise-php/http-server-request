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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Sunrise\Http\Message\Request;

/**
 * ServerRequest
 *
 * @link https://www.php-fig.org/psr/psr-7/
 */
class ServerRequest extends Request implements ServerRequestInterface
{

    /**
     * The server parameters
     *
     * @var array
     */
    protected $serverParams = [];

    /**
     * The request cookie parameters
     *
     * @var array
     */
    protected $cookieParams = [];

    /**
     * The request query parameters
     *
     * @var array
     */
    protected $queryParams = [];

    /**
     * The request uploaded files
     *
     * @var array
     */
    protected $uploadedFiles = [];

    /**
     * The request parsed body
     *
     * @var mixed
     */
    protected $parsedBody;

    /**
     * The request attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * {@inheritDoc}
     */
    public function getServerParams() : array
    {
        return $this->serverParams;
    }

    /**
     * Gets a new instance of the message with the given server parameters
     *
     * MUST NOT be used outside of this package.
     *
     * @param array $serverParams
     *
     * @return ServerRequestInterface
     */
    public function withServerParams(array $serverParams) : ServerRequestInterface
    {
        $clone = clone $this;
        $clone->serverParams = $serverParams;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function getCookieParams() : array
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritDoc}
     */
    public function withCookieParams(array $cookieParams) : ServerRequestInterface
    {
        $clone = clone $this;
        $clone->cookieParams = $cookieParams;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryParams() : array
    {
        return $this->queryParams;
    }

    /**
     * {@inheritDoc}
     */
    public function withQueryParams(array $queryParams) : ServerRequestInterface
    {
        $clone = clone $this;
        $clone->queryParams = $queryParams;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function getUploadedFiles() : array
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritDoc}
     */
    public function withUploadedFiles(array $uploadedFiles) : ServerRequestInterface
    {
        // Validates the given uploaded files structure
        \array_walk_recursive($uploadedFiles, function ($uploadedFile) {
            if (! ($uploadedFile instanceof UploadedFileInterface)) {
                throw new \InvalidArgumentException('Invalid uploaded files structure');
            }
        });

        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritDoc}
     */
    public function withParsedBody($parsedBody) : ServerRequestInterface
    {
        $clone = clone $this;
        $clone->parsedBody = $parsedBody;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($name, $default = null)
    {
        if (\array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function withAttribute($name, $value) : ServerRequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutAttribute($name) : ServerRequestInterface
    {
        $clone = clone $this;

        unset($clone->attributes[$name]);

        return $clone;
    }
}
