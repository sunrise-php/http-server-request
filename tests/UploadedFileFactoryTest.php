<?php

declare(strict_types=1);

namespace Sunrise\Http\ServerRequest\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Sunrise\Http\ServerRequest\UploadedFileFactory;
use Sunrise\Stream\StreamFactory;

/**
 * UploadedFileFactoryTest
 */
class UploadedFileFactoryTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $factory = new UploadedFileFactory();

        $this->assertInstanceOf(UploadedFileFactoryInterface::class, $factory);
    }

    /**
     * @return void
     */
    public function testCreateUploadedFile() : void
    {
        $stream = (new StreamFactory)->createStreamFromFile('php://temp/maxmemory:1', 'r+');
        $stream->write('1');

        $uploadedFile = (new UploadedFileFactory)->createUploadedFile($stream);

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
        $this->assertSame($stream, $uploadedFile->getStream());
        $this->assertSame($stream->getSize(), $uploadedFile->getSize());
        $this->assertSame(\UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertNull($uploadedFile->getClientFilename());
        $this->assertNull($uploadedFile->getClientMediaType());
    }

    /**
     * @return void
     */
    public function testCreateUploadedFileWithOptionalParameters() : void
    {
        $stream = (new StreamFactory)->createStreamFromFile('php://temp/maxmemory:1', 'r+');
        $stream->write('1');

        $size = 100;
        $error = \UPLOAD_ERR_OK;
        $filename = '47CE46D2-9B62-431E-81E0-DE9064F59CE6';
        $mediatype = 'F769A887-2D5A-4D02-8AFD-0E140D9A6B88';

        $uploadedFile = (new UploadedFileFactory)->createUploadedFile(
            $stream,
            $size,
            $error,
            $filename,
            $mediatype
        );

        $this->assertEquals($stream, $uploadedFile->getStream());
        $this->assertEquals($size, $uploadedFile->getSize());
        $this->assertEquals($error, $uploadedFile->getError());
        $this->assertEquals($filename, $uploadedFile->getClientFilename());
        $this->assertEquals($mediatype, $uploadedFile->getClientMediaType());
    }
}
