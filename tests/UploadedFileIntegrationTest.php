<?php

namespace Sunrise\Http\ServerRequest\Tests;

use Http\Psr7Test\UploadedFileIntegrationTest as BaseUploadedFileIntegrationTest;
use Sunrise\Http\ServerRequest\UploadedFile;
use Sunrise\Stream\StreamFactory;

class UploadedFileIntegrationTest extends BaseUploadedFileIntegrationTest
{
	public function createSubject()
	{
		$stream = (new StreamFactory)->createStream('foo');

		return new UploadedFile($stream);
	}
}
