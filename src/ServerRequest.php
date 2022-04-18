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
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Sunrise\Http\Message\Request;

/**
 * Import functions
 */
use function array_key_exists;
use function array_walk_recursive;
use function is_array;
use function is_object;

/**
 * ServerRequest
 *
 * @link https://www.php-fig.org/psr/psr-7/
 */
class ServerRequest extends Request implements ServerRequestInterface
{

    /**
     * The server's parameters
     *
     * @var array
     */
    protected $serverParams;

    /**
     * The request's query parameters
     *
     * @var array
     */
    protected $queryParams;

    /**
     * The request's cookie parameters
     *
     * @var array
     */
    protected $cookieParams;

    /**
     * The request's uploaded files
     *
     * @var array
     */
    protected $uploadedFiles;

    /**
     * The request's parsed body
     *
     * @var array|object|null
     */
    protected $parsedBody;

    /**
     * The request's attributes
     *
     * @var array
     */
    protected $attributes;

    /**
     * Constructor of the class
     *
     * @param string|null $method
     * @param string|UriInterface|null $uri
     * @param array<string, string|string[]>|null $headers
     * @param StreamInterface|null $body
     * @param string|null $requestTarget
     * @param string|null $protocolVersion
     *
     * @param array $serverParams
     * @param array $queryParams
     * @param array $cookieParams
     * @param array $uploadedFiles
     * @param array|object|null $parsedBody
     * @param array $attributes
     */
    public function __construct(
        ?string $method = null,
        $uri = null,
        ?array $headers = null,
        ?StreamInterface $body = null,
        ?string $requestTarget = null,
        ?string $protocolVersion = null,
        array $serverParams = [],
        array $queryParams = [],
        array $cookieParams = [],
        array $uploadedFiles = [],
        $parsedBody = null,
        array $attributes = []
    ) {
        parent::__construct(
            $method,
            $uri,
            $headers,
            $body,
            $requestTarget,
            $protocolVersion
        );

        $this->serverParams = $serverParams;
        $this->queryParams = $queryParams;
        $this->cookieParams = $cookieParams;
        $this->setUploadedFiles($uploadedFiles);
        $this->setParsedBody($parsedBody);
        $this->attributes = $attributes;
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
     *
     * @psalm-suppress ParamNameMismatch
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
     *
     * @psalm-suppress ParamNameMismatch
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
     *
     * @psalm-suppress ParamNameMismatch
     */
    public function withParsedBody($parsedBody) : ServerRequestInterface
    {
        $clone = clone $this;
        $clone->setParsedBody($parsedBody);

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
     */
    protected function setUploadedFiles(array $files) : void
    {
        $this->validateUploadedFiles($files);

        $this->uploadedFiles = $files;
    }

    /**
     * Sets the given parsed body to the request
     *
     * @param array|object|null $data
     *
     * @return void
     */
    protected function setParsedBody($data) : void
    {
        $this->validateParsedBody($data);

        $this->parsedBody = $data;
    }

    /**
     * Validates the given uploaded files
     *
     * @param array $files
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function validateUploadedFiles(array $files) : void
    {
        if ([] === $files) {
            return;
        }

        /**
         * @param mixed $file
         *
         * @return void
         *
         * @psalm-suppress MissingClosureParamType
         */
        array_walk_recursive($files, static function ($file) : void {
            if (! ($file instanceof UploadedFileInterface)) {
                throw new InvalidArgumentException('Invalid uploaded files');
            }
        });
    }

    /**
     * Validates the given parsed body
     *
     * @param mixed $data
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function validateParsedBody($data) : void
    {
        if (null === $data) {
            return;
        }

        if (!is_array($data) && !is_object($data)) {
            throw new InvalidArgumentException('Invalid parsed body');
        }
    }
}
