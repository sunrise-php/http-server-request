<?php

namespace Sunrise\Http\ServerRequest\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\ServerRequest\ServerRequest;
use Sunrise\Http\ServerRequest\UploadedFile;
use Sunrise\Stream\StreamFactory;

class ServerRequestTest extends TestCase
{
	public function testConstructor()
	{
		$req = new ServerRequest();

		$this->assertInstanceOf(ServerRequestInterface::class, $req);
	}

	public function testServerParams()
	{
		$params = ['foo' => 'bar'];

		$req = new ServerRequest();
		$this->assertEquals([], $req->getServerParams());

		$clone = $req->withServerParams($params);
		$this->assertInstanceOf(ServerRequestInterface::class, $clone);
		$this->assertEquals([], $req->getServerParams());
		$this->assertEquals($params, $clone->getServerParams());
	}

	public function testCookieParams()
	{
		$params = ['foo' => 'bar'];

		$req = new ServerRequest();
		$this->assertEquals([], $req->getCookieParams());

		$clone = $req->withCookieParams($params);
		$this->assertInstanceOf(ServerRequestInterface::class, $clone);
		$this->assertEquals([], $req->getCookieParams());
		$this->assertEquals($params, $clone->getCookieParams());
	}

	public function testQueryParams()
	{
		$params = ['foo' => 'bar'];

		$req = new ServerRequest();
		$this->assertEquals([], $req->getQueryParams());

		$clone = $req->withQueryParams($params);
		$this->assertInstanceOf(ServerRequestInterface::class, $clone);
		$this->assertEquals([], $req->getQueryParams());
		$this->assertEquals($params, $clone->getQueryParams());
	}

	public function testUploadedFiles()
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

	public function testInvalidUploadedFilesStructure()
	{
		$req = new ServerRequest();

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid uploaded files structure.');

		$req->withUploadedFiles(['foo' => 'bar']);
	}

	/**
	 * @dataProvider parsedBodyProvider
	 */
	public function testParsedBody($parsedBody)
	{
		$req = new ServerRequest();
		$this->assertEquals(null, $req->getParsedBody());

		$clone = $req->withParsedBody($parsedBody);
		$this->assertInstanceOf(ServerRequestInterface::class, $clone);
		$this->assertEquals(null, $req->getParsedBody());
		$this->assertEquals($parsedBody, $clone->getParsedBody());
	}

	/**
	 * @dataProvider attributesProvider
	 */
	public function testSetAttribute($key, $value)
	{
		$req = new ServerRequest();
		$this->assertEquals([], $req->getAttributes());

		$clone = $req->withAttribute($key, $value);
		$this->assertInstanceOf(ServerRequestInterface::class, $clone);
		$this->assertEquals([], $req->getAttributes());
		$this->assertEquals([$key => $value], $clone->getAttributes());
	}

	public function testGetAttribute()
	{
		$req = (new ServerRequest)
		->withAttribute('foo', 'bar')
		->withAttribute('bar', 'baz');

		$this->assertEquals('bar', $req->getAttribute('foo'));
		$this->assertEquals('baz', $req->getAttribute('bar'));
		$this->assertEquals(null, $req->getAttribute('baz'));
		$this->assertEquals(false, $req->getAttribute('baz', false));
	}

	public function testDeleteAttribute()
	{
		$req = (new ServerRequest)
		->withAttribute('foo', 'bar')
		->withAttribute('bar', 'baz');

		$clone1 = $req->withoutAttribute('foo');
		$this->assertInstanceOf(ServerRequestInterface::class, $clone1);
		$this->assertEquals(['bar' => 'baz'], $clone1->getAttributes());
		$this->assertEquals(null, $clone1->getAttribute('foo'));

		$clone2 = $clone1->withoutAttribute('bar');
		$this->assertInstanceOf(ServerRequestInterface::class, $clone2);
		$this->assertEquals([], $clone2->getAttributes());
		$this->assertEquals(null, $clone2->getAttribute('bar'));
	}

	// Providers...

	public function parsedBodyProvider()
	{
		return [
			[null],
			['foo bar'],
			[['foo' => 'bar']],
		];
	}

	public function attributesProvider()
	{
		return [
			['foo', null],
			['foo', 'bar'],
			['foo', ['bar']],
		];
	}
}
