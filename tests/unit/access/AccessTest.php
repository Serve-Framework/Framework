<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\access;

use serve\access\Access;
use serve\http\response\exceptions\ForbiddenException;
use serve\tests\TestCase;

/**
 * @group unit
 */
class AccessTest extends TestCase
{
	/**
	 *
	 */
	public function testEnabled(): void
	{
		$request    = $this->mock('\serve\http\request\Request');
		$response   = $this->mock('\serve\http\response\Response');
		$env        = $this->mock('\serve\http\request\Environment');
		$filesystem = $this->mock('\serve\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$request->shouldReceive('environment')->andReturn($env);

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$this->assertTrue($access->ipBlockEnabled());
	}

	/**
	 *
	 */
	public function testDisabled(): void
	{
		$request    = $this->mock('\serve\http\request\Request');
		$response   = $this->mock('\serve\http\response\Response');
		$env        = $this->mock('\serve\http\request\Environment');
		$filesystem = $this->mock('\serve\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$request->shouldReceive('environment')->andReturn($env);

		$access = new Access($request, $response, $filesystem, false, $whiteList);

		$this->assertFalse($access->ipBlockEnabled());
	}

	/**
	 *
	 */
	public function testIpAllowed(): void
	{
		$request    = $this->mock('\serve\http\request\Request');
		$response   = $this->mock('\serve\http\response\Response');
		$env        = $this->mock('\serve\http\request\Environment');
		$filesystem = $this->mock('\serve\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$env->REMOTE_ADDR = '192.168.1.1';

		$request->shouldReceive('environment')->andReturn($env);

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$this->assertTrue($access->isIpAllowed());
	}

	/**
	 *
	 */
	public function testIpNotAllowed(): void
	{		
		$request    = $this->mock('\serve\http\request\Request');
		$response   = $this->mock('\serve\http\response\Response');
		$env        = $this->mock('\serve\http\request\Environment');
		$filesystem = $this->mock('\serve\file\Filesystem');
		$whiteList  = ['192.168.1.2'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$env->REMOTE_ADDR = '192.168.1.1';

		$request->shouldReceive('environment')->andReturn($env);

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$this->assertFalse($access->isIpAllowed());
	}

	/**
	 * 
	 */
	public function testBlock(): void
	{
		$this->expectException(ForbiddenException::class);

		$request    = $this->mock('\serve\http\request\Request');
		$response   = $this->mock('\serve\http\response\Response');
		$env        = $this->mock('\serve\http\request\Environment');
		$filesystem = $this->mock('\serve\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$env->REMOTE_ADDR = '192.168.1.1';

		$request->shouldReceive('environment')->andReturn($env);

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$access->block();
	}

	/**
	 *
	 */
	public function testSaveRobots(): void
	{
		$this->expectNotToPerformAssertions();

		$request    = $this->mock('\serve\http\request\Request');
		$response   = $this->mock('\serve\http\response\Response');
		$env        = $this->mock('\serve\http\request\Environment');
		$filesystem = $this->mock('\serve\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$env->REMOTE_ADDR = '192.168.1.1';

		$request->shouldReceive('environment')->andReturn($env);

		$filesystem->shouldReceive('putContents')->with('/foo/bar/robots.txt', "User-agent: *\nDisallow:");

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$access->saveRobots($access->defaultRobotsText());
	}

	/**
	 *
	 */
	public function testDeleteRobots(): void
	{
		$this->expectNotToPerformAssertions();
		
		$request    = $this->mock('\serve\http\request\Request');
		$response   = $this->mock('\serve\http\response\Response');
		$env        = $this->mock('\serve\http\request\Environment');
		$filesystem = $this->mock('\serve\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$env->REMOTE_ADDR = '192.168.1.1';

		$request->shouldReceive('environment')->andReturn($env);

		$filesystem->shouldReceive('exists')->with('/foo/bar/robots.txt')->andReturn(true);

		$filesystem->shouldReceive('delete')->with('/foo/bar/robots.txt');

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$access->deleteRobots();
	}
}
