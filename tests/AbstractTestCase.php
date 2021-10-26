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
     * @var array<string>
     */
    private $tmpfiles = [];

    /**
     * @return void
     */
    protected function tearDown() : void
    {
        $tmpfiles = $this->tmpfiles;
        $this->tmpfiles = [];

        foreach ($tmpfiles as $tmpfile) {
            if (\is_file($tmpfile)) {
                @\unlink($tmpfile);
            }
        }
    }

    /**
     * @param string $contents
     *
     * @return string
     */
    protected function createFile(string $contents = '') : string
    {
        $tmpfile = \tempnam(\sys_get_temp_dir(), '9ed09358-bb9c-4e7c-b5dc-789ccd37123f');
        $this->tmpfiles[] = $tmpfile;

        \file_put_contents($tmpfile, $contents);

        return $tmpfile;
    }

    /**
     * @param string $contents
     *
     * @return StreamInterface
     */
    protected function createStream(string $contents = '') : StreamInterface
    {
        $tmpfile = $this->createFile($contents);

        return (new StreamFactory)->createStreamFromFile($tmpfile, 'r+b');
    }
}
