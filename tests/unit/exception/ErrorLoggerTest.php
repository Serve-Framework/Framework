<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\exception;

use ErrorException;
use serve\exception\ErrorLogger;
use serve\tests\TestCase;

/**
 * @group unit
 */
class ErrorLoggerTest extends TestCase
{
    /**
     *
     */
    public function testWebHandler(): void
    {
        $this->expectNotToPerformAssertions();
        
        $path        = dirname(__FILE__);
        $environment = $this->mock('\serve\http\request\Environment');
        $fileSystem  = $this->mock('\serve\file\Filesystem');
        $logger      = new ErrorLogger(new ErrorException, $fileSystem, $environment, $path);

        $environment->shouldReceive('__get')->withArgs(['REQUEST_URL'])->andReturn('http:/foo.com/bar');
        $environment->shouldReceive('__get')->withArgs(['REMOTE_ADDR'])->andReturn('1.0.0.0');
        $environment->shouldReceive('__get')->withArgs(['HTTP_USER_AGENT'])->andReturn('mozilla');
        $fileSystem->shouldReceive('appendContents');

        $logger->write();
    }
}
