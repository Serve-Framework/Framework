<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\useragent;

use serve\crawler\CrawlerDetect;
use serve\crawler\fixtures\Inclusions;
use serve\http\request\Headers;
use serve\tests\TestCase;

/**
 * @group unit
 */
class CrawlerTest extends TestCase
{
	/**
	 *
	 */
	public function testNonBot(): void
	{
		$headers = $this->mock(Headers::class);

		$headers->HTTP_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';

		$userAgent = new CrawlerDetect($headers, new Inclusions);

		$this->assertFalse($userAgent->isCrawler());
	}

	/**
	 *
	 */
	public function testBot(): void
	{
		$headers = $this->mock(Headers::class);

		$headers->HTTP_USER_AGENT = 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)';

		$userAgent = new CrawlerDetect($headers, new Inclusions);

		$this->assertTrue($userAgent->isCrawler());
	}
}
