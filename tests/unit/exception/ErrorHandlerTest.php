<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\exception;

use ErrorException;
use InvalidArgumentException;
use serve\exception\ErrorHandler;
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
    public function testWebHandler(): void
    {
        $this->expectNotToPerformAssertions();

        $handler    = new ErrorHandler(true, E_ALL | E_STRICT, 0);
        $logger     = $this->mock('\serve\exception\ErrorLogger');
        $webHandler = $this->mock('\serve\exception\handlers\WebHandler');

        $handler->handle(ErrorException::class, function($exception) use ($handler, $logger, $webHandler)
        {            
            // Logger
            $handler->setLogger($logger);

            // Handle
            return $webHandler->handle($handler->display_errors());
        });

        $logger->shouldReceive('write');

        $webHandler->shouldReceive('handle')->withArgs([true])->andReturn(false);

        $handler->handler(new ErrorException);
    }

    /**
     * 
     */
    public function testDifferentError(): void
    {
        $this->expectNotToPerformAssertions();

        $handler    = new ErrorHandler(true, E_ALL | E_STRICT, 0);
        $logger     = $this->mock('\serve\exception\ErrorLogger');
        $webHandler = $this->mock('\serve\exception\handlers\WebHandler');

        $handler->handle(Throwable::class, function($exception) use ($handler, $logger, $webHandler)
        {
            // Logger
            $handler->setLogger($logger);

            // Handle
            return $webHandler->handle($handler->display_errors());
        });

        $logger->shouldReceive('write');

        $webHandler->shouldReceive('handle')->withArgs([true])->andReturn(false);

        $handler->handler(new InvalidArgumentException);
    }

    /**
     * 
     */
    public function testDisableLogging(): void
    {
        $this->expectNotToPerformAssertions();

        $handler    = new ErrorHandler(true, E_ALL | E_STRICT, 0);
        $logger     = $this->mock('\serve\exception\ErrorLogger');
        $webHandler = $this->mock('\serve\exception\handlers\WebHandler');

        $handler->handle(Throwable::class, function($exception) use ($handler, $logger, $webHandler)
        {
            return $webHandler->handle($handler->display_errors());
        });

        $handler->setLogger($logger);

        $handler->disableLoggingFor(ErrorException::class);

        $webHandler->shouldReceive('handle')->withArgs([true])->andReturn(false);

        $handler->handler(new ErrorException);
    }

    /**
     * 
     */
    public function testDisplayErrors(): void
    {
        $this->expectNotToPerformAssertions();

        $handler    = new ErrorHandler(false, E_ALL | E_STRICT);
        $logger     = $this->mock('\serve\exception\ErrorLogger');
        $webHandler = $this->mock('\serve\exception\handlers\WebHandler');

        $handler->handle(Throwable::class, function($exception) use ($handler, $logger, $webHandler)
        {
            return $webHandler->handle($handler->display_errors());
        });

        $handler->setLogger($logger);

        $handler->disableLoggingFor(ErrorException::class);

        $webHandler->shouldReceive('handle')->withArgs([false])->andReturn(false);

        $handler->handler(new ErrorException);
    }
}
