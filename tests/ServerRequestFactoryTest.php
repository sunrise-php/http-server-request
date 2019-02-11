<?php

namespace Sunrise\Http\ServerRequest\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

class ServerRequestFactoryTest extends TestCase
{
	private $tmpfiles = [];

	public function testConstructor()
	{
		$factory = new ServerRequestFactory();

		$this->assertInstanceOf(ServerRequestFactoryInterface::class, $factory);
	}

	public function testCreateServerRequest()
	{
		$method = 'GET';
		$uri = 'http://localhost:3000/';
		$server = $_SERVER;

		$request = (new ServerRequestFactory)->createServerRequest($method, $uri, $server);

		$this->assertInstanceOf(ServerRequestInterface::class, $request);
		$this->assertEquals($method, $request->getMethod());
		$this->assertEquals($uri, (string) $request->getUri());
		$this->assertEquals($server, $request->getServerParams());

		// default body of the request...
		$this->assertInstanceOf(StreamInterface::class, $request->getBody());
		$this->assertTrue($request->getBody()->isSeekable());
		$this->assertTrue($request->getBody()->isWritable());
		$this->assertTrue($request->getBody()->isReadable());
		$this->assertEquals('php://temp', $request->getBody()->getMetadata('uri'));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testCreateServerRequestFromGlobals()
	{
		$file = ['tmp_name' => $this->tmpfile(), 'size' => 0, 'error' => \UPLOAD_ERR_OK, 'name' => '', 'type' => ''];

		$_SERVER = ['foo'  => 'bar'];
		$_GET    = ['bar'  => 'baz'];
		$_POST   = ['baz'  => 'qux'];
		$_COOKIE = ['qux'  => 'quux'];
		$_FILES  = ['quux' => $file];

		$request = ServerRequestFactory::fromGlobals();
		$this->assertInstanceOf(ServerRequestInterface::class, $request);

		$this->assertEquals($_SERVER, $request->getServerParams());
		$this->assertEquals($_GET, $request->getQueryParams());
		$this->assertEquals($_POST, $request->getParsedBody());
		$this->assertEquals($_COOKIE, $request->getCookieParams());
		$this->assertEquals($_FILES['quux']['tmp_name'], $request->getUploadedFiles()['quux']->getStream()->getMetadata('uri'));
	}

	public function testCreateServerRequestFromGlobalsWithServer()
	{
		$server = ['foo' => 'bar'];
		$request = ServerRequestFactory::fromGlobals($server, [], [], [], []);
		$this->assertEquals($server, $request->getServerParams());
	}

	public function testCreateServerRequestFromGlobalsWithQuery()
	{
		$query = ['foo' => 'bar'];
		$request = ServerRequestFactory::fromGlobals([], $query, [], [], []);
		$this->assertEquals($query, $request->getQueryParams());
	}

	public function testCreateServerRequestFromGlobalsWithBody()
	{
		$body = ['foo' => 'bar'];
		$request = ServerRequestFactory::fromGlobals([], [], $body, [], []);
		$this->assertEquals($body, $request->getParsedBody());
	}

	public function testCreateServerRequestFromGlobalsWithCookies()
	{
		$cookies = ['foo' => 'bar'];
		$request = ServerRequestFactory::fromGlobals([], [], [], $cookies, []);
		$this->assertEquals($cookies, $request->getCookieParams());
	}

	public function testCreateServerRequestFromGlobalsWithFiles()
	{
		$files['foo']['tmp_name'] = $this->tmpfile();
		$files['foo']['size'] = 0;
		$files['foo']['error'] = \UPLOAD_ERR_OK;
		$files['foo']['name'] = 'foo.txt';
		$files['foo']['type'] = 'text/plain';

		$files['bar']['tmp_name'][0] = $this->tmpfile();
		$files['bar']['size'][0] = 0;
		$files['bar']['error'][0] = \UPLOAD_ERR_OK;
		$files['bar']['name'][0] = 'bar.txt';
		$files['bar']['type'][0] = 'text/plain';

		$request = ServerRequestFactory::fromGlobals([], [], [], [], $files);
		$uploadedFiles = $request->getUploadedFiles();

		$this->assertEquals($files['foo']['tmp_name'], $uploadedFiles['foo']->getStream()->getMetadata('uri'));
		$this->assertEquals($files['foo']['size'], $uploadedFiles['foo']->getSize());
		$this->assertEquals($files['foo']['error'], $uploadedFiles['foo']->getError());
		$this->assertEquals($files['foo']['name'], $uploadedFiles['foo']->getClientFilename());
		$this->assertEquals($files['foo']['type'], $uploadedFiles['foo']->getClientMediaType());

		$this->assertEquals($files['bar']['tmp_name'][0], $uploadedFiles['bar'][0]->getStream()->getMetadata('uri'));
		$this->assertEquals($files['bar']['size'][0], $uploadedFiles['bar'][0]->getSize());
		$this->assertEquals($files['bar']['error'][0], $uploadedFiles['bar'][0]->getError());
		$this->assertEquals($files['bar']['name'][0], $uploadedFiles['bar'][0]->getClientFilename());
		$this->assertEquals($files['bar']['type'][0], $uploadedFiles['bar'][0]->getClientMediaType());
	}

	public function headersFromGlobalsProvider()
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
		];
	}

	/**
	 * @dataProvider headersFromGlobalsProvider
	 */
	public function testHeadersFromGlobals($header, $expectedValue)
	{
		$request = ServerRequestFactory::fromGlobals($header);
		$this->assertEquals($expectedValue, $request->getHeader('foo'));
	}

	public function protocolVersionFromGlobalsProvider()
	{
		return [
			[
				['SERVER_PROTOCOL' => 'HTTP/2.0'],
				'2.0',
			],
			[
				['SERVER_PROTOCOL' => 'HTTP/3'],
				'3',
			],
		];
	}

	/**
	 * @dataProvider protocolVersionFromGlobalsProvider
	 */
	public function testProtocolVersionFromGlobals($protocolVersion, $expectedValue)
	{
		$request = ServerRequestFactory::fromGlobals($protocolVersion);
		$this->assertEquals($expectedValue, $request->getProtocolVersion());
	}

	public function methodFromGlobalsProvider()
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
	 * @dataProvider methodFromGlobalsProvider
	 */
	public function testMethodFromGlobals($requestMethod, $expectedValue)
	{
		$request = ServerRequestFactory::fromGlobals($requestMethod);
		$this->assertEquals($expectedValue, $request->getMethod());
	}

	public function uriFromGlobalsProvider()
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
	 * @dataProvider uriFromGlobalsProvider
	 */
	public function testUriFromGlobals($uri, $expectedValue)
	{
		$request = ServerRequestFactory::fromGlobals($uri);
		$this->assertEquals($expectedValue, (string) $request->getUri());
	}

	protected function tearDown()
	{
		$tmpfiles = $this->tmpfiles;

		$this->tmpfiles = [];

		foreach ($tmpfiles as $tmpfile)
		{
			@ \unlink($tmpfile);
		}
	}

	private function tmpfile() : string
	{
		$folder = \sys_get_temp_dir();

		$tmpfile = \tempnam($folder, 'sunrise');

		$this->tmpfiles[] = $tmpfile;

		return $tmpfile;
	}
}
