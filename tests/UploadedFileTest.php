<?php

declare(strict_types=1);

namespace Sunrise\Http\ServerRequest\Tests;

/**
 * Import classes
 */
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Sunrise\Http\ServerRequest\UploadedFile;

/**
 * UploadedFileTest
 */
class UploadedFileTest extends AbstractTestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $stream = $this->createStream('blah');
        $uploadedFile = new UploadedFile($stream);

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
        $this->assertSame($stream, $uploadedFile->getStream());
        $this->assertNull($uploadedFile->getSize());
        $this->assertSame(\UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertNull($uploadedFile->getClientFilename());
        $this->assertNull($uploadedFile->getClientMediaType());
    }

    /**
     * @return void
     */
    public function testConstructorWithOptionalParameters() : void
    {
        $stream = $this->createStream('blah');
        $uploadedFile = new UploadedFile($stream, 100, \UPLOAD_ERR_OK, 'foo', 'bar');

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
        $this->assertSame($stream, $uploadedFile->getStream());
        $this->assertSame(100, $uploadedFile->getSize());
        $this->assertSame(\UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertSame('foo', $uploadedFile->getClientFilename());
        $this->assertSame('bar', $uploadedFile->getClientMediaType());
    }

    /**
     * @return void
     */
    public function testConstructorWithInvalidFile() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid uploaded file');

        new UploadedFile(null);
    }

    /**
     * @return void
     */
    public function testMove() : void
    {
        $stream = $this->createStream('foo');
        $uploadedFile = new UploadedFile($stream);

        $targetPath = $this->createFile();
        $uploadedFile->moveTo($targetPath);

        $this->assertStringEqualsFile($targetPath, 'foo');
    }

    /**
     * @return void
     */
    public function testReWrite() : void
    {
        $stream = $this->createStream('foo');
        $uploadedFile = new UploadedFile($stream);

        $targetPath = $this->createFile('bar');
        $uploadedFile->moveTo($targetPath);

        $this->assertStringEqualsFile($targetPath, 'foo');
    }

    /**
     * @dataProvider errorCodeProvider
     *
     * @return void
     */
    public function testMoveWithError($error) : void
    {
        $stream = $this->createStream();
        $uploadedFile = new UploadedFile($stream, null, $error);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The uploaded file cannot be moved due to the error #' . $error);

        $uploadedFile->moveTo('/');
    }

    /**
     * @return void
     */
    public function testReMove() : void
    {
        $stream = $this->createStream();
        $targetPath = $this->createFile();
        $uploadedFile = new UploadedFile($stream);
        $uploadedFile->moveTo($targetPath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The uploaded file already moved');

        $uploadedFile->moveTo('/');
    }

    /**
     * @return void
     */
    public function testMoveToNowhere() : void
    {
        $stream = $this->createStream();
        $uploadedFile = new UploadedFile($stream);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The uploaded file cannot be moved because the directory "/" is not available');

        $uploadedFile->moveTo('/');
    }

    /**
     * @return void
     */
    public function testGetMovedStream() : void
    {
        $stream = $this->createStream();
        $uploadedFile = new UploadedFile($stream);

        $targetPath = $this->createFile();
        $uploadedFile->moveTo($targetPath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The uploaded file already moved');

        $uploadedFile->getStream();
    }

    /**
     * @dataProvider errorCodeProvider
     *
     * @return void
     */
    public function testGetStreamWithError($error) : void
    {
        $stream = $this->createStream();
        $uploadedFile = new UploadedFile($stream, null, $error);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf(
            'The uploaded file does not have a stream due to the error #%d',
            $error
        ));

        $uploadedFile->getStream();
    }

    /**
     * @return array
     */
    public function errorCodeProvider() : array
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
}
