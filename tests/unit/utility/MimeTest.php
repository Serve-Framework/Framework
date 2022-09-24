<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\utility;

use serve\tests\TestCase;
use serve\utility\Mime;

/**
 * @group unit
 */
class MimeTest extends TestCase
{
	/**
	 *
	 */
	public function testFromExt(): void
	{
		foreach (Mime::$mimeMap as $ext => $mime)
		{
			$ext = explode('|', $ext);
			$ext = $ext[0];
			$this->assertEquals($mime, Mime::fromExt($ext));
		}
	}

	/**
	 *
	 */
	public function testToExt(): void
	{
		foreach (Mime::$mimeMap as $ext => $mime)
		{
			$ext = explode('|', $ext);
			$ext = $ext[0];
			$this->assertEquals($ext, Mime::toExt($mime));
		}
	}
}
