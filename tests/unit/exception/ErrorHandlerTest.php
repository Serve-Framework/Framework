<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\exception;

use ErrorException;
use InvalidArgumentException;
use serve\exception\ErrorHandler;
use serve\exception\logger\Logger;
use serve\tests\TestCase;
use Throwable;

/**
 * @group unit
 */
class ErrorHandlerTest extends TestCase
{
    /**
     *
     */
    public function testdefaults(): void
    {
        $logger        = $this->mock(Logger::class);
        $handler       = new ErrorHandler($logger, true, true);
        $error         = new ErrorException('Unit testing error message');
        $madeCallback  = false;

        $handler->handle(Throwable::class, function ($exception) use (&$madeCallback)
        {
            $madeCallback = true;

            return true;
        });

        $logger->shouldReceive('writeException');
        $handler->handler($error);

        $this->assertTrue($handler->displayErrors());
        $this->assertTrue($handler->logErrors());
        $this->assertTrue($madeCallback);
    }

    /**
     *
     */
    public function testNoLogging(): void
    {
        $logger        = $this->mock(Logger::class);
        $handler       = new ErrorHandler($logger, true, false);
        $error         = new ErrorException('Unit testing error message');
        $madeCallback  = false;

        $handler->handle(Throwable::class, function ($exception) use (&$madeCallback)
        {
            $madeCallback = true;

            return true;
        });

        $handler->handler($error);

        $this->assertTrue($handler->displayErrors());
        $this->assertFalse($handler->logErrors());
        $this->assertTrue($madeCallback);
    }

    /**
     *
     */
    public function testNoDisplay(): void
    {
        $logger        = $this->mock(Logger::class);
        $handler       = new ErrorHandler($logger, false, false);
        $error         = new ErrorException('Unit testing error message');
        $madeCallback  = false;

        $handler->handle(Throwable::class, function ($exception) use (&$madeCallback)
        {
            $madeCallback = true;

            return true;
        });

        $handler->handler($error);

        $this->assertFalse($handler->displayErrors());
        $this->assertFalse($handler->logErrors());
        $this->assertTrue($madeCallback);
    }

    /**
     * @runInSeparateProcess
     */
    public function testFallbackHandler(): void
    {
        $this->expectOutputRegex('/\[ErrorException\] Unit testing error message on line/');

        $logger        = $this->mock(Logger::class);
        $handler       = new ErrorHandler($logger, true, true);
        $error         = new ErrorException('Unit testing error message');

        $logger->shouldReceive('writeException');
        $handler->handler($error);

        $this->assertTrue($handler->logErrors());
    }

    /**
     *
     */
    public function testFallbackHandlerNoOutput(): void
    {
        $logger        = $this->mock(Logger::class);
        $handler       = new ErrorHandler($logger, false, true);
        $error         = new ErrorException('Unit testing error message');

        $logger->shouldReceive('writeException');
        $handler->handler($error);

        $this->assertFalse($handler->displayErrors());
        $this->assertTrue($handler->logErrors());
    }

    /**
     *
     */
    public function testDisableLoggingFor(): void
    {
        $logger        = $this->mock(Logger::class);
        $handler       = new ErrorHandler($logger, true, true);
        $error         = new ErrorException('Unit testing error message');
        $madeCallback  = false;

        $handler->handle(ErrorException::class, function ($exception) use (&$madeCallback)
        {
            $madeCallback = true;

            return true;
        });

        $handler->disableLoggingFor(ErrorException::class);
        $handler->handler($error);

        $this->assertTrue($madeCallback);
    }

    /**
     *
     */
    public function testDifferentError(): void
    {
        $this->expectOutputRegex('/\[ErrorException\] Unit testing error message on line/');

        $logger        = $this->mock(Logger::class);
        $handler       = new ErrorHandler($logger, true, true);
        $error         = new ErrorException('Unit testing error message');
        $madeCallback  = false;

        $handler->handle(InvalidArgumentException::class, function ($exception) use (&$madeCallback)
        {
            $madeCallback = true;

            return true;
        });

        $logger->shouldReceive('writeException');
        $handler->handler($error);

        $this->assertFalse($madeCallback);
    }

    /**
     *
     */
    public function testSameError(): void
    {
        $logger        = $this->mock(Logger::class);
        $handler       = new ErrorHandler($logger, true, true);
        $error         = new InvalidArgumentException('Unit testing error message');
        $madeCallback  = false;

        $handler->handle(InvalidArgumentException::class, function ($exception) use (&$madeCallback)
        {
            $madeCallback = true;

            return true;
        });

        $logger->shouldReceive('writeException');
        $handler->handler($error);

        $this->assertTrue($madeCallback);
    }

    /**
     *
     */
    public function testClearHandlers(): void
    {
        $this->expectOutputRegex('/\[InvalidArgumentException\] Unit testing error message on line/');

        $logger        = $this->mock(Logger::class);
        $handler       = new ErrorHandler($logger, true, true);
        $error         = new InvalidArgumentException('Unit testing error message');

        $logger->shouldReceive('writeException');
        $handler->clearHandlers(InvalidArgumentException::class);
        $handler->handler($error);

        $this->assertTrue($handler->logErrors());
    }

    /**
     *
     */
    public function testReplaceHandlers(): void
    {
        $logger        = $this->mock(Logger::class);
        $handler       = new ErrorHandler($logger, true, true);
        $error         = new ErrorException('Unit testing error message');
        $madeCallback  = false;

        $handler->replaceHandlers(Throwable::class, function ($exception) use (&$madeCallback)
        {
            $madeCallback = true;

            return true;
        });

        $logger->shouldReceive('writeException');
        $handler->handler($error);

        $this->assertTrue($madeCallback);
    }
}
