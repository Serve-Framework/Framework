<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\utility;

use serve\tests\TestCase;
use serve\utility\Markdown;

/**
 * @group unit
 */
class MarkdownUtilityTest extends TestCase
{
	/**
	 *
	 */
	public function testMarkdown(): void
	{
		$this->assertEquals('<h1>Hello World!</h1>', Markdown::convert('# Hello World!'));
		$this->assertEquals('<h1>Hello World!</h1>', Markdown::convert('# Hello World!', false));
		$this->assertEquals('Hello World!', Markdown::plainText('# Hello World!'));
	}
}
