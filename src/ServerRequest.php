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
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Sunrise\Http\Message\Request;
use InvalidArgumentException;

/**
 * Import functions
 */
use function array_key_exists;
use function array_walk_recursive;

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
     * The request query parameters
     *
     * @var array
     */
    protected $queryParams = [];

    /**
     * The request cookie parameters
     *
     * @var array
     */
    protected $cookieParams = [];

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
    protected $parsedBody = null;

    /**
     * The request attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Constructor of the class
     *
     * @param string|null $method
     * @param string|UriInterface|null $uri
     * @param array<string, string|array<string>>|null $headers
     * @param StreamInterface|null $body
     * @param string|null $requestTarget
     * @param string|null $protocolVersion
     * @param array|null $serverParams
     * @param array|null $queryParams
     * @param array|null $cookieParams
     * @param array|null $uploadedFiles
     * @param mixed|null $parsedBody
     * @param array|null $attributes
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ?string $method = null,
        $uri = null,
        ?array $headers = null,
        ?StreamInterface $body = null,
        ?string $requestTarget = null,
        ?string $protocolVersion = null,
        ?array $serverParams = null,
        ?array $queryParams = null,
        ?array $cookieParams = null,
        ?array $uploadedFiles = null,
        $parsedBody = null,
        ?array $attributes = null
    ) {
        parent::__construct(
            $method,
            $uri,
            $headers,
            $body,
            $requestTarget,
            $protocolVersion
        );

        if (isset($serverParams)) {
            $this->serverParams = $serverParams;
        }

        if (isset($queryParams)) {
            $this->queryParams = $queryParams;
        }

        if (isset($cookieParams)) {
            $this->cookieParams = $cookieParams;
        }

        if (isset($uploadedFiles)) {
            $this->setUploadedFiles($uploadedFiles);
        }

        if (isset($parsedBody)) {
            $this->parsedBody = $parsedBody;
        }

        if (isset($attributes)) {
            $this->attributes = $attributes;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams() : array
    {
        return $this->serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams() : array
    {
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $queryParams) : ServerRequestInterface
    {
        $clone = clone $this;
        $clone->queryParams = $queryParams;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams() : array
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookieParams) : ServerRequestInterface
    {
        $clone = clone $this;
        $clone->cookieParams = $cookieParams;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles() : array
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function withUploadedFiles(array $uploadedFiles) : ServerRequestInterface
    {
        $clone = clone $this;
        $clone->setUploadedFiles($uploadedFiles);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($parsedBody) : ServerRequestInterface
    {
        $clone = clone $this;
        $clone->parsedBody = $parsedBody;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute($name, $value) : ServerRequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name) : ServerRequestInterface
    {
        $clone = clone $this;

        unset($clone->attributes[$name]);

        return $clone;
    }

    /**
     * Sets the given uploaded files to the request
     *
     * @param array $files
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function setUploadedFiles(array $files) : void
    {
        array_walk_recursive($files, function ($file) {
            if (! ($file instanceof UploadedFileInterface)) {
                throw new InvalidArgumentException('Invalid uploaded files structure');
            }
        });

        $this->uploadedFiles = $files;
    }
}
