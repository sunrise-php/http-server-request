<?php declare(strict_types=1);

namespace Sunrise\Http\ServerRequest\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
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
     * @var null|StreamInterface
     */
    private $stream;

    /**
     * @return void
     */
    protected function setUp() : void
    {
        $this->stream = (new StreamFactory)->createStreamFromFile('php://memory', 'r+b');

        $this->stream->write('foo');
    }

    /**
     * @return void
     */
    protected function tearDown() : void
    {
        if ($this->stream instanceof StreamInterface) {
            $this->stream->close();
        }
    }

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
        $uploadedFile = (new UploadedFileFactory)->createUploadedFile($this->stream);

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
        $this->assertEquals($this->stream, $uploadedFile->getStream());
        $this->assertEquals($this->stream->getSize(), $uploadedFile->getSize());
        $this->assertEquals(\UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertNull($uploadedFile->getClientFilename());
        $this->assertNull($uploadedFile->getClientMediaType());
    }

    /**
     * @return void
     */
    public function testCreateUploadedFileWithParameters() : void
    {
        $size = \random_int(\PHP_INT_MIN, \PHP_INT_MAX);
        $error = \UPLOAD_ERR_OK;
        $filename = 'photo.jpeg';
        $mediatype = 'image/jpeg';

        $uploadedFile = (new UploadedFileFactory)->createUploadedFile(
            $this->stream,
            $size,
            $error,
            $filename,
            $mediatype
        );

        $this->assertEquals($this->stream, $uploadedFile->getStream());
        $this->assertEquals($size, $uploadedFile->getSize());
        $this->assertEquals($error, $uploadedFile->getError());
        $this->assertEquals($filename, $uploadedFile->getClientFilename());
        $this->assertEquals($mediatype, $uploadedFile->getClientMediaType());
    }
}
