<?php declare(strict_types=1);

namespace Sunrise\Http\ServerRequest\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Message\Request;
use Sunrise\Http\ServerRequest\ServerRequest;
use Sunrise\Http\ServerRequest\UploadedFile;
use Sunrise\Stream\StreamFactory;

/**
 * ServerRequestTest
 */
class ServerRequestTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $req = new ServerRequest();

        $this->assertInstanceOf(ServerRequestInterface::class, $req);
        $this->assertInstanceOf(Request::class, $req);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $body = (new StreamFactory)->createStream();
        $file = new UploadedFile($body);

        $request = new ServerRequest(
            'POST',
            'http://localhost:8000/foo?bar',
            ['X-Foo' => 'bar'],
            $body,
            '/bar?baz',
            '2.0',
            ['foo' => 'bar'],
            ['bar' => 'baz'],
            ['baz' => 'bat'],
            ['bat' => $file],
            ['qux' => 'quux'],
            ['quux' => 'quuux']
        );

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('http://localhost:8000/foo?bar', (string) $request->getUri());
        $this->assertSame(['X-Foo' => ['bar'], 'Host' => ['localhost:8000']], $request->getHeaders());
        $this->assertSame($body, $request->getBody());
        $this->assertSame('/bar?baz', $request->getRequestTarget());
        $this->assertSame('2.0', $request->getProtocolVersion());
        $this->assertSame(['foo' => 'bar'], $request->getServerParams());
        $this->assertSame(['bar' => 'baz'], $request->getQueryParams());
        $this->assertSame(['baz' => 'bat'], $request->getCookieParams());
        $this->assertSame(['bat' => $file], $request->getUploadedFiles());
        $this->assertSame(['qux' => 'quux'], $request->getParsedBody());
        $this->assertSame(['quux' => 'quuux'], $request->getAttributes());
    }

    /**
     * @return void
     */
    public function testQueryParams() : void
    {
        $params = ['foo' => 'bar'];

        $req = new ServerRequest();
        $this->assertEquals([], $req->getQueryParams());

        $clone = $req->withQueryParams($params);
        $this->assertInstanceOf(ServerRequestInterface::class, $clone);
        $this->assertEquals([], $req->getQueryParams());
        $this->assertEquals($params, $clone->getQueryParams());
    }

    /**
     * @return void
     */
    public function testCookieParams() : void
    {
        $params = ['foo' => 'bar'];

        $req = new ServerRequest();
        $this->assertEquals([], $req->getCookieParams());

        $clone = $req->withCookieParams($params);
        $this->assertInstanceOf(ServerRequestInterface::class, $clone);
        $this->assertEquals([], $req->getCookieParams());
        $this->assertEquals($params, $clone->getCookieParams());
    }

    /**
     * @return void
     */
    public function testUploadedFiles() : void
    {
        $stream = (new StreamFactory)->createStreamFromFile('php://memory', 'rb');

        $uploadedFiles = [
            'foo' => new UploadedFile($stream),
            'bar' => [
                'baz' => new UploadedFile($stream),
            ],
        ];

        $req = new ServerRequest();
        $this->assertEquals([], $req->getUploadedFiles());

        $clone = $req->withUploadedFiles($uploadedFiles);
        $this->assertInstanceOf(ServerRequestInterface::class, $clone);
        $this->assertEquals([], $req->getUploadedFiles());
        $this->assertEquals($uploadedFiles, $clone->getUploadedFiles());

        $stream->close();
    }

    /**
     * @return void
     */
    public function testInvalidUploadedFilesStructure() : void
    {
        $req = new ServerRequest();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid uploaded files structure');

        $req->withUploadedFiles(['foo' => 'bar']);
    }

    /**
     * @dataProvider parsedBodyProvider
     *
     * @return void
     */
    public function testParsedBody($parsedBody) : void
    {
        $req = new ServerRequest();
        $this->assertNull($req->getParsedBody());

        $clone = $req->withParsedBody($parsedBody);
        $this->assertInstanceOf(ServerRequestInterface::class, $clone);
        $this->assertNull($req->getParsedBody());
        $this->assertEquals($parsedBody, $clone->getParsedBody());
    }

    /**
     * @dataProvider attributesProvider
     *
     * @return void
     */
    public function testSetAttribute($key, $value) : void
    {
        $req = new ServerRequest();
        $this->assertEquals([], $req->getAttributes());

        $clone = $req->withAttribute($key, $value);
        $this->assertInstanceOf(ServerRequestInterface::class, $clone);
        $this->assertEquals([], $req->getAttributes());
        $this->assertEquals([$key => $value], $clone->getAttributes());
    }

    /**
     * @return void
     */
    public function testGetAttribute() : void
    {
        $req = (new ServerRequest)
            ->withAttribute('foo', 'bar')
            ->withAttribute('bar', 'baz');

        $this->assertEquals('bar', $req->getAttribute('foo'));
        $this->assertEquals('baz', $req->getAttribute('bar'));
        $this->assertNull($req->getAttribute('baz'));
        $this->assertFalse($req->getAttribute('baz', false));
    }

    /**
     * @return void
     */
    public function testDeleteAttribute() : void
    {
        $req = (new ServerRequest)
            ->withAttribute('foo', 'bar')
            ->withAttribute('bar', 'baz');

        $clone1 = $req->withoutAttribute('foo');
        $this->assertInstanceOf(ServerRequestInterface::class, $clone1);
        $this->assertEquals(['bar' => 'baz'], $clone1->getAttributes());
        $this->assertNull($clone1->getAttribute('foo'));

        $clone2 = $clone1->withoutAttribute('bar');
        $this->assertInstanceOf(ServerRequestInterface::class, $clone2);
        $this->assertEquals([], $clone2->getAttributes());
        $this->assertNull($clone2->getAttribute('bar'));
    }

    // Providers...

    /**
     * @return array
     */
    public function parsedBodyProvider() : array
    {
        return [
            [null],
            ['foo bar'],
            [['foo' => 'bar']],
        ];
    }

    /**
     * @return array
     */
    public function attributesProvider() : array
    {
        return [
            ['foo', null],
            ['foo', 'bar'],
            ['foo', ['bar']],
        ];
    }
}
