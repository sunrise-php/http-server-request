<?php

namespace Sunrise\Http\ServerRequest\Tests;

use Http\Psr7Test\ServerRequestIntegrationTest as BaseServerRequestIntegrationTest;
use Sunrise\Http\ServerRequest\ServerRequest;

class ServerRequestIntegrationTest extends BaseServerRequestIntegrationTest
{
	protected $skippedTests = [
		'testGetServerParams' => true,
		'testGetParsedBodyInvalid' => true,
	];

	public function createSubject()
	{
		return new ServerRequest();
	}
}
