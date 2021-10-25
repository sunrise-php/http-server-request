<?php

declare(strict_types=1);

namespace Sunrise\Http\ServerRequest\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

/**
 * ServerRequestFactoryTest
 */
class ServerRequestFactoryTest extends TestCase
{

    /**
     * @var array
     */
    private $tmpfiles = [];

    /**
     * @return void
     */
    public function testConstructor() : void
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
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testCreateServerRequestFromGlobals() : void
    {
        $file = [
            'tmp_name' => $this->tmpfile(),
            'size' => 1,
            'error' => \UPLOAD_ERR_OK,
            'name' => '10482301-2DB0-473A-8881-C6288945AD0B',
            'type' => '1408315D-36BF-4201-952C-CB07C616313C',
        ];

        $_SERVER = ['foo'  => 'bar'];
        $_GET    = ['bar'  => 'baz'];
        $_COOKIE = ['baz'  => 'bat'];
        $_FILES  = ['bat' => $file];
        $_POST   = ['qux'  => 'quux'];

        $request = ServerRequestFactory::fromGlobals();
        $this->assertInstanceOf(ServerRequestInterface::class, $request);

        $this->assertSame($_SERVER, $request->getServerParams());
        $this->assertSame($_GET, $request->getQueryParams());
        $this->assertSame($_COOKIE, $request->getCookieParams());
        $this->assertArrayHasKey('bat', $request->getUploadedFiles());
        $this->assertSame($_POST, $request->getParsedBody());

        $uploadedFile = $request->getUploadedFiles()['bat'];
        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
        $this->assertSame($_FILES['bat']['tmp_name'], $uploadedFile->getStream()->getMetadata('uri'));
        $this->assertSame($_FILES['bat']['size'], $uploadedFile->getSize());
        $this->assertSame($_FILES['bat']['error'], $uploadedFile->getError());
        $this->assertSame($_FILES['bat']['name'], $uploadedFile->getClientFilename());
        $this->assertSame($_FILES['bat']['type'], $uploadedFile->getClientMediaType());
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
    public function testCreateServerRequestFromGlobalsWithUploadedFiles() : void
    {
        $files['foo']['tmp_name'] = $this->tmpfile();
        $files['foo']['size'] = 100;
        $files['foo']['error'] = \UPLOAD_ERR_OK;
        $files['foo']['name'] = '8832D847-FE04-4E86-B933-9E74D109CD9B';
        $files['foo']['type'] = '3A7B7995-2900-4834-B165-1DE8EDE90587';

        $files['bar']['tmp_name'][0] = $this->tmpfile();
        $files['bar']['size'][0] = 200;
        $files['bar']['error'][0] = \UPLOAD_ERR_OK;
        $files['bar']['name'][0] = '4E7D2E48-90F4-477C-8859-5F69D29835C4';
        $files['bar']['type'][0] = 'C53D3703-63D7-409A-822C-6EE1424023B2';

        $request = ServerRequestFactory::fromGlobals([], [], [], $files, []);
        $uploadedFiles = $request->getUploadedFiles();

        $this->assertSame($files['foo']['tmp_name'], $uploadedFiles['foo']->getStream()->getMetadata('uri'));
        $this->assertSame($files['foo']['size'], $uploadedFiles['foo']->getSize());
        $this->assertSame($files['foo']['error'], $uploadedFiles['foo']->getError());
        $this->assertSame($files['foo']['name'], $uploadedFiles['foo']->getClientFilename());
        $this->assertSame($files['foo']['type'], $uploadedFiles['foo']->getClientMediaType());

        $this->assertSame($files['bar']['tmp_name'][0], $uploadedFiles['bar'][0]->getStream()->getMetadata('uri'));
        $this->assertSame($files['bar']['size'][0], $uploadedFiles['bar'][0]->getSize());
        $this->assertSame($files['bar']['error'][0], $uploadedFiles['bar'][0]->getError());
        $this->assertSame($files['bar']['name'][0], $uploadedFiles['bar'][0]->getClientFilename());
        $this->assertSame($files['bar']['type'][0], $uploadedFiles['bar'][0]->getClientMediaType());
    }

    /**
     * @return void
     */
    public function testCreateServerRequestFromGlobalsWithUploadErrorNoFile() : void
    {
        $files = [
            'foo' => [
                'error' => \UPLOAD_ERR_NO_FILE,
                'size' => 100,
                'tmp_name' => $this->tmpfile(),
                'name' => '12E3EE6C-7BA7-432A-88D7-8D15405CFEB8',
                'type' => 'F01CBCD9-9112-4267-8309-B41DAF6DA57F',
            ],
        ];

        $request = ServerRequestFactory::fromGlobals([], [], [], [], $files);

        $this->assertCount(0, $request->getUploadedFiles());
    }

    /**
     * @param array<string, string> $serverParams
     * @param mixed $expectedValue
     * @param string|null $key
     *
     * @return void
     *
     * @dataProvider headersFromGlobalsProvider
     */
    public function testHeadersFromGlobals($serverParams, $expectedValue, $key = null) : void
    {
        $request = ServerRequestFactory::fromGlobals($serverParams);
        $this->assertSame($expectedValue, $request->getHeader($key ?? 'foo'));
    }

    /**
     * @param array<string, string> $serverParams
     * @param mixed $expectedValue
     *
     * @return void
     *
     * @dataProvider protocolVersionFromGlobalsProvider
     */
    public function testProtocolVersionFromGlobals($serverParams, $expectedValue) : void
    {
        $request = ServerRequestFactory::fromGlobals($serverParams);
        $this->assertSame($expectedValue, $request->getProtocolVersion());
    }

    /**
     * @param array<string, string> $serverParams
     * @param mixed $expectedValue
     *
     * @return void
     *
     * @dataProvider methodFromGlobalsProvider
     */
    public function testMethodFromGlobals($serverParams, $expectedValue) : void
    {
        $request = ServerRequestFactory::fromGlobals($serverParams);
        $this->assertSame($expectedValue, $request->getMethod());
    }

    /**
     * @param array<string, string> $serverParams
     * @param mixed $expectedValue
     *
     * @return void
     *
     * @dataProvider uriFromGlobalsProvider
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

    /**
     * @return void
     */
    protected function tearDown() : void
    {
        $tmpfiles = $this->tmpfiles;
        $this->tmpfiles = [];

        foreach ($tmpfiles as $tmpfile) {
            @\unlink($tmpfile);
        }
    }

    /**
     * @return string
     */
    private function tmpfile() : string
    {
        $folder = \sys_get_temp_dir();
        $tmpfile = \tempnam($folder, 'sunrise');

        $this->tmpfiles[] = $tmpfile;

        return $tmpfile;
    }
}
