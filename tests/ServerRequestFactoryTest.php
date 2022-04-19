<?php

declare(strict_types=1);

namespace Sunrise\Http\ServerRequest\Tests;

/**
 * Import classes
 */
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

/**
 * ServerRequestFactoryTest
 */
class ServerRequestFactoryTest extends AbstractTestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $factory = new ServerRequestFactory();

        $this->assertInstanceOf(ServerRequestFactoryInterface::class, $factory);
    }

    /**
     * @return void
     */
    public function testCreateServerRequest() : void
    {
        $verb = 'GET';
        $uri = 'http://localhost:8000/';

        $serverParams = [];
        $serverParams['HTTP_X_FOO'] = 'bar';
        $serverParams['SERVER_PROTOCOL'] = 'HTTP/1.0';

        $expectedHeaders = ['X-Foo' => ['bar'], 'Host' => ['localhost:8000']];
        $expectedProtocolVersion = '1.0';

        $request = (new ServerRequestFactory)->createServerRequest($verb, $uri, $serverParams);

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertSame($verb, $request->getMethod());
        $this->assertSame($uri, (string) $request->getUri());
        $this->assertSame($serverParams, $request->getServerParams());
        $this->assertSame($expectedHeaders, $request->getHeaders());
        $this->assertSame($expectedProtocolVersion, $request->getProtocolVersion());
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertTrue($request->getBody()->isSeekable());
        $this->assertTrue($request->getBody()->isWritable());
        $this->assertTrue($request->getBody()->isReadable());
        $this->assertSame('php://temp', $request->getBody()->getMetadata('uri'));
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     */
    public function testCreateServerRequestFromGlobals() : void
    {
        $_SERVER = ['foo' => 'bar'];
        $_GET    = ['bar' => 'baz'];
        $_POST   = ['baz' => 'bat'];
        $_COOKIE = ['bat' => 'qux'];

        $_FILES['foo']['tmp_name'] = \sys_get_temp_dir() . '/c3036cb9-3806-47e2-b193-2a1ca505b7dd';
        $_FILES['foo']['size'] = 100;
        $_FILES['foo']['error'] = \UPLOAD_ERR_OK;
        $_FILES['foo']['name'] = '8832d847-fe04-4e86-b933-9e74d109cd9b';
        $_FILES['foo']['type'] = '3a7b7995-2900-4834-b165-1de8ede90587';

        $_FILES['bar']['tmp_name'][0] = \sys_get_temp_dir() . '/bed3c541-b32b-44c7-9abe-1c72d182060e';
        $_FILES['bar']['size'][0] = 200;
        $_FILES['bar']['error'][0] = \UPLOAD_ERR_OK;
        $_FILES['bar']['name'][0] = '4e7d2e48-90f4-477c-8859-5f69d29835c4';
        $_FILES['bar']['type'][0] = 'c53d3703-63d7-409a-822c-6ee1424023b2';

        try {
            \fclose(\fopen($_FILES['foo']['tmp_name'], 'w+'));
            \fclose(\fopen($_FILES['bar']['tmp_name'][0], 'w+'));

            $request = ServerRequestFactory::fromGlobals();
            $this->assertInstanceOf(ServerRequestInterface::class, $request);

            $this->assertSame($_SERVER, $request->getServerParams());
            $this->assertSame($_GET, $request->getQueryParams());
            $this->assertSame($_POST, $request->getParsedBody());
            $this->assertSame($_COOKIE, $request->getCookieParams());

            $uploadedFiles = $request->getUploadedFiles();

            $this->assertSame($_FILES['foo']['tmp_name'], $uploadedFiles['foo']->getStream()->getMetadata('uri'));
            $this->assertSame($_FILES['foo']['size'], $uploadedFiles['foo']->getSize());
            $this->assertSame($_FILES['foo']['error'], $uploadedFiles['foo']->getError());
            $this->assertSame($_FILES['foo']['name'], $uploadedFiles['foo']->getClientFilename());
            $this->assertSame($_FILES['foo']['type'], $uploadedFiles['foo']->getClientMediaType());

            $this->assertSame($_FILES['bar']['tmp_name'][0], $uploadedFiles['bar'][0]->getStream()->getMetadata('uri'));
            $this->assertSame($_FILES['bar']['size'][0], $uploadedFiles['bar'][0]->getSize());
            $this->assertSame($_FILES['bar']['error'][0], $uploadedFiles['bar'][0]->getError());
            $this->assertSame($_FILES['bar']['name'][0], $uploadedFiles['bar'][0]->getClientFilename());
            $this->assertSame($_FILES['bar']['type'][0], $uploadedFiles['bar'][0]->getClientMediaType());
        } finally {
            \file_exists($_FILES['foo']['tmp_name']) and \unlink($_FILES['foo']['tmp_name']);
            \file_exists($_FILES['bar']['tmp_name'][0]) and \unlink($_FILES['bar']['tmp_name'][0]);
        }
    }

    /**
     * @return void
     */
    public function testCreateServerRequestFromGlobalsWithServerParams() : void
    {
        $serverParams = ['foo' => 'bar'];
        $request = ServerRequestFactory::fromGlobals($serverParams, [], [], [], []);
        $this->assertSame($serverParams, $request->getServerParams());
    }

    /**
     * @return void
     */
    public function testCreateServerRequestFromGlobalsWithQueryParams() : void
    {
        $queryParams = ['foo' => 'bar'];
        $request = ServerRequestFactory::fromGlobals([], $queryParams, [], [], []);
        $this->assertSame($queryParams, $request->getQueryParams());
    }

    /**
     * @return void
     */
    public function testCreateServerRequestFromGlobalsWithCookieParams() : void
    {
        $cookieParams = ['foo' => 'bar'];
        $request = ServerRequestFactory::fromGlobals([], [], $cookieParams, [], []);
        $this->assertSame($cookieParams, $request->getCookieParams());
    }

    /**
     * @return void
     */
    public function testCreateServerRequestFromGlobalsWithParsedBody() : void
    {
        $parsedBody = ['foo' => 'bar'];
        $request = ServerRequestFactory::fromGlobals([], [], [], [], $parsedBody);
        $this->assertSame($parsedBody, $request->getParsedBody());
    }

    /**
     * @return void
     */
    public function testCreateServerRequestFromGlobalsWithUploadErrorNoFile() : void
    {
        $files = [
            'foo' => [
                'error' => \UPLOAD_ERR_NO_FILE,
                'size' => 0,
                'tmp_name' => '/d4496a36-6b57-4956-a627-04f1603b57d7',
                'name' => '12e3ee6c-7ba7-432a-88d7-8d15405cfeb8',
                'type' => 'f01cbcd9-9112-4267-8309-b41daf6da57f',
            ],
        ];

        $request = ServerRequestFactory::fromGlobals([], [], [], [], $files);

        $this->assertCount(0, $request->getUploadedFiles());
    }

    /**
     * @dataProvider headersFromGlobalsProvider
     *
     * @param array<string, string> $serverParams
     * @param mixed $expectedValue
     * @param string|null $key
     *
     * @return void
     */
    public function testHeadersFromGlobals($serverParams, $expectedValue, $key = null) : void
    {
        $request = ServerRequestFactory::fromGlobals($serverParams);
        $this->assertSame($expectedValue, $request->getHeader($key ?? 'foo'));
    }

    /**
     * @dataProvider protocolVersionFromGlobalsProvider
     *
     * @param array<string, string> $serverParams
     * @param mixed $expectedValue
     *
     * @return void
     */
    public function testProtocolVersionFromGlobals($serverParams, $expectedValue) : void
    {
        $request = ServerRequestFactory::fromGlobals($serverParams);
        $this->assertSame($expectedValue, $request->getProtocolVersion());
    }

    /**
     * @dataProvider methodFromGlobalsProvider
     *
     * @param array<string, string> $serverParams
     * @param mixed $expectedValue
     *
     * @return void
     */
    public function testMethodFromGlobals($serverParams, $expectedValue) : void
    {
        $request = ServerRequestFactory::fromGlobals($serverParams);
        $this->assertSame($expectedValue, $request->getMethod());
    }

    /**
     * @dataProvider uriFromGlobalsProvider
     *
     * @param array<string, string> $serverParams
     * @param mixed $expectedValue
     *
     * @return void
     */
    public function testUriFromGlobals($serverParams, $expectedValue) : void
    {
        $request = ServerRequestFactory::fromGlobals($serverParams);
        $this->assertSame($expectedValue, (string) $request->getUri());
    }

    /**
     * @return array
     */
    public function headersFromGlobalsProvider() : array
    {
        return [
            [
                ['FOO' => 'bar'],
                [],
            ],
            [
                ['HTTP_FOO' => 'bar'],
                ['bar'],
            ],
            [
                ['CONTENT_LENGTH' => '100'],
                ['100'],
                'Content-Length',
            ],
            [
                ['CONTENT_TYPE' => 'application/json'],
                ['application/json'],
                'Content-Type',
            ],
        ];
    }

    /**
     * @return array
     */
    public function protocolVersionFromGlobalsProvider() : array
    {
        return [
            [
                ['SERVER_PROTOCOL' => 'HTTP/1.0'],
                '1.0',
            ],
            [
                ['SERVER_PROTOCOL' => 'HTTP/1.1'],
                '1.1',
            ],
            [
                ['SERVER_PROTOCOL' => 'HTTP/2'],
                '2',
            ],
            [
                ['SERVER_PROTOCOL' => 'oO'],
                '1.1',
            ],
        ];
    }

    /**
     * @return array
     */
    public function methodFromGlobalsProvider() : array
    {
        return [
            [
                ['REQUEST_METHOD' => 'POST'],
                'POST',
            ],
            [
                ['REQUEST_METHOD' => 'UNKNOWN'],
                'UNKNOWN',
            ],
        ];
    }

    /**
     * @return array
     */
    public function uriFromGlobalsProvider() : array
    {
        return [
            [
                [],
                'http://localhost/',
            ],
            [
                ['HTTPS' => 'off'],
                'http://localhost/',
            ],
            [
                ['HTTPS' => 'on'],
                'https://localhost/',
            ],
            [
                ['HTTP_HOST' => 'example.com'],
                'http://example.com/',
            ],
            [
                ['HTTP_HOST' => 'example.com:3000'],
                'http://example.com:3000/',
            ],
            [
                ['SERVER_NAME' => 'example.com'],
                'http://example.com/',
            ],
            [
                ['SERVER_NAME' => 'example.com', 'SERVER_PORT' => 3000],
                'http://example.com:3000/',
            ],
            [
                ['SERVER_PORT' => 3000],
                'http://localhost/',
            ],
            [
                ['REQUEST_URI' => '/path'],
                'http://localhost/path',
            ],
            [
                ['REQUEST_URI' => '/path?query'],
                'http://localhost/path?query',
            ],
            [
                ['PHP_SELF' => '/path'],
                'http://localhost/path',
            ],
            [
                ['PHP_SELF' => '/path', 'QUERY_STRING' => 'query'],
                'http://localhost/path?query',
            ],
            [
                ['QUERY_STRING' => 'query'],
                'http://localhost/',
            ],
        ];
    }
}
