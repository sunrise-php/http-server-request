<?php

namespace Sunrise\Http\ServerRequest\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

class ServerRequestFactoryTest extends TestCase
{
	public function testConstructor()
	{
		$factory = new ServerRequestFactory();

		$this->assertInstanceOf(ServerRequestFactoryInterface::class, $factory);
	}

	public function testCreateServerRequest()
	{
		$method = 'GET';
		$uri = 'http://localhost:3000/';
		$serverParams = $_SERVER;

		$request = (new ServerRequestFactory)->createServerRequest($method, $uri, $serverParams);

		$this->assertInstanceOf(ServerRequestInterface::class, $request);
		$this->assertEquals($method, $request->getMethod());
		$this->assertEquals($uri, (string) $request->getUri());
		$this->assertEquals($serverParams, $request->getServerParams());
	}

	public function testFilesFromGlobals()
	{
		$foo = \tempnam(\sys_get_temp_dir(), 'sunrise.php');
		$bar = \tempnam(\sys_get_temp_dir(), 'sunrise.php');

		\file_put_contents($foo, 'foo');
		\file_put_contents($bar, 'bar');

		$files['foo']['tmp_name'] = $foo;
		$files['foo']['size'] = 3;
		$files['foo']['error'] = \UPLOAD_ERR_OK;
		$files['foo']['name'] = 'foo.txt';
		$files['foo']['type'] = 'text/plain';

		$files['bar']['tmp_name'][0] = $bar;
		$files['bar']['size'][0] = 3;
		$files['bar']['error'][0] = \UPLOAD_ERR_OK;
		$files['bar']['name'][0] = 'bar.txt';
		$files['bar']['type'][0] = 'text/plain';

		$request = ServerRequestFactory::fromGlobals([], [], [], [], $files);
		$uploadedFiles = $request->getUploadedFiles();

		$this->assertEquals('foo', (string) $uploadedFiles['foo']->getStream());
		$this->assertEquals('bar', (string) $uploadedFiles['bar'][0]->getStream());

		@ \unlink($foo);
		@ \unlink($bar);
	}

	public function testHeadersFromGlobals()
	{
		$request = ServerRequestFactory::fromGlobals(['FOO' => 'bar']);
		$this->assertEquals([], $request->getHeader('foo'));

		$request = ServerRequestFactory::fromGlobals(['HTTP_FOO' => 'bar']);
		$this->assertEquals(['bar'], $request->getHeader('foo'));
	}

	public function testProtocolVersionFromGlobals()
	{
		$request = ServerRequestFactory::fromGlobals(['SERVER_PROTOCOL' => 'HTTP/2.0']);
		$this->assertEquals('2.0', $request->getProtocolVersion());

		$request = ServerRequestFactory::fromGlobals(['SERVER_PROTOCOL' => 'HTTP/3']);
		$this->assertEquals('3', $request->getProtocolVersion());
	}

	public function testMethodFromGlobals()
	{
		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'POST']);
		$this->assertEquals('POST', $request->getMethod());

		$request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'UNKNOWN']);
		$this->assertEquals('UNKNOWN', $request->getMethod());
	}

	public function testUriFromGlobals()
	{
		$request = ServerRequestFactory::fromGlobals([]);
		$this->assertEquals('http://localhost/', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['HTTPS' => 'off']);
		$this->assertEquals('http://localhost/', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['HTTPS' => 'on']);
		$this->assertEquals('https://localhost/', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['HTTP_HOST' => 'example.com']);
		$this->assertEquals('http://example.com/', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['HTTP_HOST' => 'example.com:3000']);
		$this->assertEquals('http://example.com:3000/', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['SERVER_NAME' => 'example.com']);
		$this->assertEquals('http://example.com/', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['SERVER_NAME' => 'example.com', 'SERVER_PORT' => 3000]);
		$this->assertEquals('http://example.com:3000/', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['SERVER_PORT' => 3000]);
		$this->assertEquals('http://localhost/', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/path']);
		$this->assertEquals('http://localhost/path', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/path?query']);
		$this->assertEquals('http://localhost/path?query', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['PHP_SELF' => '/path']);
		$this->assertEquals('http://localhost/path', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['PHP_SELF' => '/path', 'QUERY_STRING' => 'query']);
		$this->assertEquals('http://localhost/path?query', (string) $request->getUri());

		$request = ServerRequestFactory::fromGlobals(['QUERY_STRING' => 'query']);
		$this->assertEquals('http://localhost/', (string) $request->getUri());
	}
}
