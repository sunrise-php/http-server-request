<?php

declare(strict_types=1);

namespace Sunrise\Http\ServerRequest\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Sunrise\Stream\StreamFactory;

/**
 * AbstractTestCase
 */
abstract class AbstractTestCase extends TestCase
{

    /**
     * @param ?string $contents
     *
     * @return StreamInterface
     */
    protected function createStream(?string $contents = null) : StreamInterface
    {
        return (new StreamFactory)->createStreamFromTemporaryFile($contents);
    }
}
