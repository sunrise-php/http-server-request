<?php

declare(strict_types=1);

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
        $request = new ServerRequest();

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertInstanceOf(Request::class, $request);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $stream = (new StreamFactory)->createStream();

        $uploadedFile = new UploadedFile($stream);

        $request = new ServerRequest(
            // method
            'POST',
            // URI
            'http://localhost:8000/foo?bar',
            // headers
            ['X-Foo' => 'bar'],
            // body
            $stream,
            // request target
            '/bar?baz',
            // protocol version
            '2.0',
            // server params
            ['foo' => 'bar'],
            // query params
            ['bar' => 'baz'],
            // cookie params
            ['baz' => 'bat'],
            // uploaded files
            ['bat' => $uploadedFile],
            // parsed body
            ['qux' => 'quux'],
            // attributes
            ['quux' => 'quuux']
        );

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('http://localhost:8000/foo?bar', (string) $request->getUri());
        $this->assertSame(['X-Foo' => ['bar'], 'Host' => ['localhost:8000']], $request->getHeaders());
        $this->assertSame($stream, $request->getBody());
        $this->assertSame('/bar?baz', $request->getRequestTarget());
        $this->assertSame('2.0', $request->getProtocolVersion());
        $this->assertSame(['foo' => 'bar'], $request->getServerParams());
        $this->assertSame(['bar' => 'baz'], $request->getQueryParams());
        $this->assertSame(['baz' => 'bat'], $request->getCookieParams());
        $this->assertSame(['bat' => $uploadedFile], $request->getUploadedFiles());
        $this->assertSame(['qux' => 'quux'], $request->getParsedBody());
        $this->assertSame(['quux' => 'quuux'], $request->getAttributes());
    }

    /**
     * @return void
     */
    public function testConstructorWithInvalidUploadedFiles() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid uploaded files');

        new ServerRequest(
            null,
            null,
            null,
            null,
            null,
            null,
            [],
            [],
            [],
            ['foo' => 'bar']
        );
    }

    /**
     * @return void
     */
    public function testSetQueryParams() : void
    {
        $queryParams = ['foo' => 'bar'];

        $request = new ServerRequest();
        $this->assertSame([], $request->getQueryParams());

        $clone = $request->withQueryParams($queryParams);
        $this->assertInstanceOf(ServerRequestInterface::class, $clone);
        $this->assertNotSame($request, $clone);
        $this->assertSame([], $request->getQueryParams());
        $this->assertSame($queryParams, $clone->getQueryParams());
    }

    /**
     * @return void
     */
    public function testSetCookieParams() : void
    {
        $cookieParams = ['foo' => 'bar'];

        $request = new ServerRequest();
        $this->assertSame([], $request->getCookieParams());

        $clone = $request->withCookieParams($cookieParams);
        $this->assertInstanceOf(ServerRequestInterface::class, $clone);
        $this->assertNotSame($request, $clone);
        $this->assertSame([], $request->getCookieParams());
        $this->assertSame($cookieParams, $clone->getCookieParams());
    }

    /**
     * @return void
     */
    public function testSetUploadedFiles() : void
    {
        $stream = (new StreamFactory)->createStreamFromFile('php://memory', 'rb');
        $stream->close();

        $uploadedFiles = ['foo' => new UploadedFile($stream)];

        $request = new ServerRequest();
        $this->assertSame([], $request->getUploadedFiles());

        $clone = $request->withUploadedFiles($uploadedFiles);
        $this->assertInstanceOf(ServerRequestInterface::class, $clone);
        $this->assertNotSame($request, $clone);
        $this->assertSame([], $request->getUploadedFiles());
        $this->assertSame($uploadedFiles, $clone->getUploadedFiles());
    }

    /**
     * @return void
     */
    public function testSetInvalidUploadedFiles() : void
    {
        $request = new ServerRequest();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid uploaded files');

        $request->withUploadedFiles(['foo' => 'bar']);
    }

    /**
     * @return void
     */
    public function testSetParsedBody() : void
    {
        $parsedBody = ['foo' => 'bar'];

        $request = new ServerRequest();
        $this->assertNull($request->getParsedBody());

        $clone = $request->withParsedBody($parsedBody);
        $this->assertInstanceOf(ServerRequestInterface::class, $clone);
        $this->assertNotSame($request, $clone);
        $this->assertNull($request->getParsedBody());
        $this->assertSame($parsedBody, $clone->getParsedBody());
    }

    /**
     * @return void
     */
    public function testSetAttributes() : void
    {
        $request = new ServerRequest();
        $this->assertSame([], $request->getAttributes());
        $this->assertSame(null, $request->getAttribute('foo'));
        $this->assertSame(false, $request->getAttribute('foo', false));

        $clone = $request->withAttribute('foo', 'bar');
        $this->assertInstanceOf(ServerRequestInterface::class, $clone);
        $this->assertNotSame($request, $clone);
        $this->assertSame([], $request->getAttributes());
        $this->assertSame(null, $request->getAttribute('foo'));
        $this->assertSame(['foo' => 'bar'], $clone->getAttributes());
        $this->assertSame('bar', $clone->getAttribute('foo'));
    }

    /**
     * @return void
     */
    public function testRemoveAttributes() : void
    {
        $request = (new ServerRequest)->withAttribute('foo', 'bar');

        $clone = $request->withoutAttribute('foo');
        $this->assertInstanceOf(ServerRequestInterface::class, $clone);
        $this->assertNotSame($request, $clone);
        $this->assertSame(['foo' => 'bar'], $request->getAttributes());
        $this->assertSame('bar', $request->getAttribute('foo'));
        $this->assertSame([], $clone->getAttributes());
        $this->assertSame(null, $clone->getAttribute('foo'));
    }
}
