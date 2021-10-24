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
use Psr\Http\Message\UploadedFileInterface;
use Sunrise\Stream\StreamFactory;
use InvalidArgumentException;
use RuntimeException;

/**
 * Import functions
 */
use function dirname;
use function is_dir;
use function is_writeable;
use function sprintf;

/**
 * Import constants
 */
use const UPLOAD_ERR_OK;

/**
 * UploadedFile
 *
 * @link https://www.php-fig.org/psr/psr-7/
 */
class UploadedFile implements UploadedFileInterface
{

    /**
     * The file stream
     *
     * @var StreamInterface|null
     */
    protected $stream;

    /**
     * The file size
     *
     * @var int|null
     */
    protected $size;

    /**
     * The file error
     *
     * @var int
     */
    protected $error;

    /**
     * The file name
     *
     * @var string|null
     */
    protected $clientFilename;

    /**
     * The file type
     *
     * @var string|null
     */
    protected $clientMediaType;

    /**
     * Constructor of the class
     *
     * @param StreamInterface $stream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct(
        StreamInterface $stream,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ) {
        $this->stream = $stream;
        $this->size = $size ?? $stream->getSize();
        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function getStream() : StreamInterface
    {
        if (! ($this->stream instanceof StreamInterface)) {
            throw new RuntimeException('The uploaded file already moved');
        }

        return $this->stream;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function moveTo($targetPath) : void
    {
        if (! ($this->stream instanceof StreamInterface)) {
            throw new RuntimeException('The uploaded file already moved');
        }

        if (UPLOAD_ERR_OK <> $this->error) {
            throw new RuntimeException('The uploaded file cannot be moved due to an error');
        }

        $folder = dirname($targetPath);
        if (!is_dir($folder)) {
            throw new InvalidArgumentException(sprintf(
                'The uploaded file cannot be moved. The directory "%s" does not exist',
                $folder
            ));
        }

        if (!is_writeable($folder)) {
            throw new InvalidArgumentException(sprintf(
                'The uploaded file cannot be moved. The directory "%s" is not writeable',
                $folder
            ));
        }

        $target = (new StreamFactory)->createStreamFromFile($targetPath, 'wb');

        $this->stream->rewind();
        while (!$this->stream->eof()) {
            $target->write($this->stream->read(4096));
        }

        $this->stream->close();
        $this->stream = null;

        $target->close();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize() : ?int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getError() : int
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename() : ?string
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType() : ?string
    {
        return $this->clientMediaType;
    }
}
