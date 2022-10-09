<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\exception;

use ErrorException;
use serve\exception\logger\Logger;
use serve\file\Filesystem;
use serve\http\request\Environment;
use serve\tests\TestCase;

use function dirname;

/**
 * @group unit
 */
class ErrorLoggerTest extends TestCase
{
    /**
     *
     */
    protected function getServerData(): array
    {
        return
        [
            'REQUEST_METHOD'  => 'GET',
            'SCRIPT_NAME'     => '/foobar/index.php',
            'SERVER_NAME'     => 'localhost',
            'SERVER_PORT'     => '8888',
            'HTTP_PROTOCOL'   => 'http',
            'DOCUMENT_ROOT'   => '/usr/name/httpdocs',
            'HTTP_HOST'       => 'http://localhost:8888',
            'DOMAIN_NAME'     => 'localhost:8888',
            'REQUEST_URI'     => '/foobar?foo=bar',
            'REMOTE_ADDR'     => '192.168.1.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17',
        ];
    }

    /**
     *
     */
    public function testWebHandler(): void
    {
        $this->expectNotToPerformAssertions();

        $path        = dirname(__FILE__) . '/testlogs';
        $fileSystem  = $this->mock(Filesystem::class);
        $environment = new Environment($this->getServerData());
        $logger      = new Logger($fileSystem, $environment, $path);

        $fileSystem->shouldReceive('appendContents');

        $logger->writeException(new ErrorException('Something bad happened'));
    }
}
