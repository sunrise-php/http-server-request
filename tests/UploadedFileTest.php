<?php

namespace Sunrise\Http\ServerRequest\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Sunrise\Http\ServerRequest\UploadedFile;
use Sunrise\Stream\StreamFactory;

class UploadedFileTest extends TestCase
{
	private $stream;

	private $targetPath = '';

	public function setUp()
	{
		$this->stream = (new StreamFactory)->createStreamFromFile('php://memory', 'r+b');

		$this->targetPath = \sys_get_temp_dir() . '/' . \bin2hex(\random_bytes(16));
	}

	public function tearDown()
	{
		if ($this->stream instanceof StreamInterface)
		{
			$this->stream->close();
		}

		if (\file_exists($this->targetPath))
		{
			@ \unlink($this->targetPath);
		}
	}

	public function testConstructor()
	{
		$uploadedFile = new UploadedFile($this->stream);

		$this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
	}

	public function testGetStream()
	{
		$uploadedFile = new UploadedFile($this->stream);

		$this->assertEquals($this->stream, $uploadedFile->getStream());
	}

	public function testGetSize()
	{
		$size = \random_int(\PHP_INT_MIN, \PHP_INT_MAX);

		$uploadedFile = new UploadedFile($this->stream, $size);

		$this->assertEquals($size, $uploadedFile->getSize());
	}

	public function testGetError()
	{
		$error = \UPLOAD_ERR_NO_FILE;

		$uploadedFile = new UploadedFile($this->stream, null, $error);

		$this->assertEquals($error, $uploadedFile->getError());
	}

	public function testGetClientFilename()
	{
		$filename = 'photo.jpeg';

		$uploadedFile = new UploadedFile($this->stream, null, \UPLOAD_ERR_OK, $filename);

		$this->assertEquals($filename, $uploadedFile->getClientFilename());
	}

	public function testGetClientMediaType()
	{
		$mediatype = 'image/jpeg';

		$uploadedFile = new UploadedFile($this->stream, null, \UPLOAD_ERR_OK, null, $mediatype);

		$this->assertEquals($mediatype, $uploadedFile->getClientMediaType());
	}

	public function testGetDefaultSize()
	{
		$uploadedFile = new UploadedFile($this->stream);

		$this->assertEquals($this->stream->getSize(), $uploadedFile->getSize());
	}

	public function testGetDefaultError()
	{
		$uploadedFile = new UploadedFile($this->stream);

		$this->assertEquals(\UPLOAD_ERR_OK, $uploadedFile->getError());
	}

	public function testGetDefaultClientFilename()
	{
		$uploadedFile = new UploadedFile($this->stream);

		$this->assertEquals(null, $uploadedFile->getClientFilename());
	}

	public function testGetDefaultClientMediaType()
	{
		$uploadedFile = new UploadedFile($this->stream);

		$this->assertEquals(null, $uploadedFile->getClientMediaType());
	}

	public function testMoveTo()
	{
		$content = 'foo';
		$this->stream->write($content);

		$uploadedFile = new UploadedFile($this->stream);
		$uploadedFile->moveTo($this->targetPath);

		$this->assertTrue(\file_exists($this->targetPath));
		$this->assertEquals($content, \file_get_contents($this->targetPath));
	}

	public function testReWrite()
	{
		$content = 'qux';
		$this->stream->write($content);

		\file_put_contents($this->targetPath, "foo\nbar\nbaz");

		$uploadedFile = new UploadedFile($this->stream);
		$uploadedFile->moveTo($this->targetPath);

		$this->assertEquals($content, \file_get_contents($this->targetPath));
	}

	public function testGetStreamAfterMoveTo()
	{
		$uploadedFile = new UploadedFile($this->stream);
		$uploadedFile->moveTo($this->targetPath);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('The uploaded file already moved');

		$uploadedFile->getStream();
	}

	public function testReMove()
	{
		$uploadedFile = new UploadedFile($this->stream);
		$uploadedFile->moveTo($this->targetPath);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('The uploaded file already moved');

		$uploadedFile->moveTo($this->targetPath);
	}

	/**
	 * @dataProvider errorProvider
	 */
	public function testMoveToWithError($error)
	{
		$uploadedFile = new UploadedFile($this->stream, null, $error);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('The uploaded file cannot be moved due to an error');

		$uploadedFile->moveTo($this->targetPath);
	}

	public function errorProvider()
	{
		return [
			[\UPLOAD_ERR_INI_SIZE],
			[\UPLOAD_ERR_FORM_SIZE],
			[\UPLOAD_ERR_PARTIAL],
			[\UPLOAD_ERR_NO_FILE],
			[\UPLOAD_ERR_NO_TMP_DIR],
			[\UPLOAD_ERR_CANT_WRITE],
			[\UPLOAD_ERR_EXTENSION],
		];
	}

	public function testMoveToNonExistentDirectory()
	{
		$targetPath = $this->targetPath . '/d';
		$uploadedFile = new UploadedFile($this->stream);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(\sprintf('The uploaded file cannot be moved. The directory "%s" does not exist', $this->targetPath));

		$uploadedFile->moveTo($targetPath);
	}
}
