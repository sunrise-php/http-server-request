<?php

namespace Sunrise\Http\ServerRequest\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Sunrise\Http\ServerRequest\UploadedFileFactory;
use Sunrise\Stream\StreamFactory;

class UploadedFileFactoryTest extends TestCase
{
	private $stream;

	public function setUp()
	{
		$this->stream = (new StreamFactory)->createStreamFromFile('php://memory', 'r+b');
	}

	public function tearDown()
	{
		if ($this->stream instanceof StreamInterface)
		{
			$this->stream->close();
		}
	}

	public function testConstructor()
	{
		$factory = new UploadedFileFactory();

		$this->assertInstanceOf(UploadedFileFactoryInterface::class, $factory);
	}

	public function testCreateUploadedFile()
	{
		$uploadedFile = (new UploadedFileFactory)->createUploadedFile($this->stream);

		$this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
		$this->assertEquals($this->stream, $uploadedFile->getStream());
		$this->assertEquals(null, $uploadedFile->getSize());
		$this->assertEquals(\UPLOAD_ERR_OK, $uploadedFile->getError());
		$this->assertEquals(null, $uploadedFile->getClientFilename());
		$this->assertEquals(null, $uploadedFile->getClientMediaType());
	}

	public function testCreateUploadedFileWithParameters()
	{
		$size = \random_int(\PHP_INT_MIN, \PHP_INT_MAX);
		$error = \UPLOAD_ERR_NO_FILE;
		$filename = 'photo.jpeg';
		$mediatype = 'image/jpeg';

		$uploadedFile = (new UploadedFileFactory)->createUploadedFile($this->stream, $size, $error, $filename, $mediatype);

		$this->assertEquals($this->stream, $uploadedFile->getStream());
		$this->assertEquals($size, $uploadedFile->getSize());
		$this->assertEquals($error, $uploadedFile->getError());
		$this->assertEquals($filename, $uploadedFile->getClientFilename());
		$this->assertEquals($mediatype, $uploadedFile->getClientMediaType());
	}
}
