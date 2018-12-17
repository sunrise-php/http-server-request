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
	 * @var null|StreamInterface
	 */
	protected $stream;

	/**
	 * The file size
	 *
	 * @var null|int
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
	 * @var null|string
	 */
	protected $clientFilename;

	/**
	 * The file type
	 *
	 * @var null|string
	 */
	protected $clientMediaType;

	/**
	 * Constructor of the class
	 *
	 * @param StreamInterface $stream
	 * @param null|int $size
	 * @param int $error
	 * @param null|string $clientFilename
	 * @param null|string $clientMediaType
	 */
	public function __construct(StreamInterface $stream, int $size = null, int $error = \UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null)
	{
		$this->stream = $stream;

		$this->size = $size ?? $stream->getSize();

		$this->error = $error;

		$this->clientFilename = $clientFilename;

		$this->clientMediaType = $clientMediaType;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStream() : StreamInterface
	{
		if (! ($this->stream instanceof StreamInterface))
		{
			throw new \RuntimeException('The uploaded file already moved.');
		}

		return $this->stream;
	}

	/**
	 * {@inheritDoc}
	 */
	public function moveTo($targetPath) : void
	{
		if (! ($this->stream instanceof StreamInterface))
		{
			throw new \RuntimeException('The uploaded file already moved.');
		}

		if (! (\UPLOAD_ERR_OK === $this->error))
		{
			throw new \RuntimeException('The uploaded file cannot be moved due to an error.');
		}

		$folder = \dirname($targetPath);

		if (! \is_dir($folder))
		{
			throw new \RuntimeException(\sprintf('The uploaded file cannot be moved. The directory "%s" does not exist.', $folder));
		}

		if (! \is_writeable($folder))
		{
			throw new \RuntimeException(\sprintf('The uploaded file cannot be moved. The directory "%s" is not writeable.', $folder));
		}

		$target = (new StreamFactory)->createStreamFromFile($targetPath, 'wb');

		$this->stream->rewind();

		while (! $this->stream->eof())
		{
			$target->write($this->stream->read(4096));
		}

		$this->stream->close();
		$this->stream = null;

		$target->close();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSize() : ?int
	{
		return $this->size;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getError() : int
	{
		return $this->error;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getClientFilename() : ?string
	{
		return $this->clientFilename;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getClientMediaType() : ?string
	{
		return $this->clientMediaType;
	}
}
