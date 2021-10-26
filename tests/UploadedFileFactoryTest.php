<?php

declare(strict_types=1);

namespace Sunrise\Http\ServerRequest\Tests;

/**
 * Import classes
 */
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Sunrise\Http\ServerRequest\UploadedFileFactory;

/**
 * UploadedFileFactoryTest
 */
class UploadedFileFactoryTest extends AbstractTestCase
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
        $stream = $this->createStream('foo');

        $uploadedFile = (new UploadedFileFactory)->createUploadedFile($stream);

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
    public function testCreateUploadedFileWithOptionalParameters() : void
    {
        $stream = $this->createStream('foo');

        $size = 100;
        $error = \UPLOAD_ERR_OK;
        $filename = '47ce46d2-9b62-431e-81e0-de9064f59ce6';
        $mediatype = 'f769a887-2d5a-4d02-8afd-0e140d9a6b88';

        $uploadedFile = (new UploadedFileFactory)->createUploadedFile(
            $stream,
            $size,
            $error,
            $filename,
            $mediatype
        );

        $this->assertSame($stream, $uploadedFile->getStream());
        $this->assertSame($size, $uploadedFile->getSize());
        $this->assertSame($error, $uploadedFile->getError());
        $this->assertSame($filename, $uploadedFile->getClientFilename());
        $this->assertSame($mediatype, $uploadedFile->getClientMediaType());
    }
}
