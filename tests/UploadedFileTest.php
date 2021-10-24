<?php declare(strict_types=1);

namespace Sunrise\Http\ServerRequest\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Sunrise\Http\ServerRequest\UploadedFile;
use Sunrise\Stream\StreamFactory;

/**
 * UploadedFileTest
 */
class UploadedFileTest extends TestCase
{

    /**
     * @var null|StreamInterface
     */
    private $stream;

    /**
     * @var string
     */
    private $targetPath = '';

    /**
     * @return void
     */
    protected function setUp() : void
    {
        $this->stream = (new StreamFactory)->createStreamFromFile('php://memory', 'r+b');

        $this->targetPath = \sys_get_temp_dir() . '/' . \bin2hex(\random_bytes(16));
    }

    /**
     * @return void
     */
    protected function tearDown() : void
    {
        if ($this->stream instanceof StreamInterface) {
            $this->stream->close();
        }

        if (\file_exists($this->targetPath)) {
            @\unlink($this->targetPath);
        }
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $uploadedFile = new UploadedFile($this->stream);

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
    }

    /**
     * @return void
     */
    public function testGetStream() : void
    {
        $uploadedFile = new UploadedFile($this->stream);

        $this->assertEquals($this->stream, $uploadedFile->getStream());
    }

    /**
     * @return void
     */
    public function testGetSize() : void
    {
        $size = \random_int(\PHP_INT_MIN, \PHP_INT_MAX);

        $uploadedFile = new UploadedFile($this->stream, $size);

        $this->assertEquals($size, $uploadedFile->getSize());
    }

    /**
     * @return void
     */
    public function testGetError() : void
    {
        $error = \UPLOAD_ERR_NO_FILE;

        $uploadedFile = new UploadedFile($this->stream, null, $error);

        $this->assertEquals($error, $uploadedFile->getError());
    }

    /**
     * @return void
     */
    public function testGetClientFilename() : void
    {
        $filename = 'photo.jpeg';

        $uploadedFile = new UploadedFile($this->stream, null, \UPLOAD_ERR_OK, $filename);

        $this->assertEquals($filename, $uploadedFile->getClientFilename());
    }

    /**
     * @return void
     */
    public function testGetClientMediaType() : void
    {
        $mediatype = 'image/jpeg';

        $uploadedFile = new UploadedFile($this->stream, null, \UPLOAD_ERR_OK, null, $mediatype);

        $this->assertEquals($mediatype, $uploadedFile->getClientMediaType());
    }

    /**
     * @return void
     */
    public function testGetDefaultSize() : void
    {
        $uploadedFile = new UploadedFile($this->stream);

        $this->assertEquals($this->stream->getSize(), $uploadedFile->getSize());
    }

    /**
     * @return void
     */
    public function testGetDefaultError() : void
    {
        $uploadedFile = new UploadedFile($this->stream);

        $this->assertEquals(\UPLOAD_ERR_OK, $uploadedFile->getError());
    }

    /**
     * @return void
     */
    public function testGetDefaultClientFilename() : void
    {
        $uploadedFile = new UploadedFile($this->stream);

        $this->assertNull($uploadedFile->getClientFilename());
    }

    /**
     * @return void
     */
    public function testGetDefaultClientMediaType() : void
    {
        $uploadedFile = new UploadedFile($this->stream);

        $this->assertNull($uploadedFile->getClientMediaType());
    }

    /**
     * @return void
     */
    public function testMoveTo() : void
    {
        $content = 'foo';
        $this->stream->write($content);

        $uploadedFile = new UploadedFile($this->stream);
        $uploadedFile->moveTo($this->targetPath);

        $this->assertFileExists($this->targetPath);
        $this->assertEquals($content, \file_get_contents($this->targetPath));
    }

    /**
     * @return void
     */
    public function testReWrite() : void
    {
        $content = 'qux';
        $this->stream->write($content);

        \file_put_contents($this->targetPath, "foo\nbar\nbaz");

        $uploadedFile = new UploadedFile($this->stream);
        $uploadedFile->moveTo($this->targetPath);

        $this->assertEquals($content, \file_get_contents($this->targetPath));
    }

    /**
     * @return void
     */
    public function testGetStreamAfterMoveTo() : void
    {
        $uploadedFile = new UploadedFile($this->stream);
        $uploadedFile->moveTo($this->targetPath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The uploaded file already moved');

        $uploadedFile->getStream();
    }

    /**
     * @return void
     */
    public function testReMove() : void
    {
        $uploadedFile = new UploadedFile($this->stream);
        $uploadedFile->moveTo($this->targetPath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The uploaded file already moved');

        $uploadedFile->moveTo($this->targetPath);
    }

    /**
     * @dataProvider errorProvider
     *
     * @return void
     */
    public function testMoveToWithError($error) : void
    {
        $uploadedFile = new UploadedFile($this->stream, null, $error);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The uploaded file cannot be moved due to an error');

        $uploadedFile->moveTo($this->targetPath);
    }

    /**
     * @return void
     */
    public function testMoveToNonExistentDirectory() : void
    {
        $targetPath = $this->targetPath . '/d';
        $uploadedFile = new UploadedFile($this->stream);

        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage(
            \sprintf('The uploaded file cannot be moved. The directory "%s" does not exist', $this->targetPath)
        );

        $uploadedFile->moveTo($targetPath);
    }

    // providers...

    /**
     * @return array
     */
    public function errorProvider() : array
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
