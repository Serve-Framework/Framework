<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\ioc;

use serve\ioc\Container;
use serve\ioc\ContainerAwareTrait;
use serve\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class ContainerAwareCallback
{
	use ContainerAwareTrait;

	private $foobaz = 'foobaz';

	public $foobarz = 'foobarz';

	public function __construct()
    {
    }

    public function getPrivate()
    {
    	return $this->foobaz;
    }
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class ContainerAwareTest extends TestCase
{
	/**
	 *
	 */
	public function testGetFromContainer(): void
	{
		$container = Container::instance();

		$container->set('foo', 'bar');

		$container->set('bar', 'foo');

		$class = new ContainerAwareCallback;

		$this->assertEquals('foo', $class->bar);

		$this->assertEquals('bar', $class->container()->get('foo'));

		$this->assertEquals(null, $class->foobar);

		$container->clear();
	}

	/**
	 *
	 */
	public function testGetPrivate(): void
	{
		$container = Container::instance();

		$container->set('foo', 'bar');

		$class = new ContainerAwareCallback;

		$this->assertEquals('foobaz', $class->getPrivate());

		$container->clear();
	}

	/**
	 *
	 */
	public function testGetPublic(): void
	{
		$container = Container::instance();

		$container->set('foo', 'bar');

		$class = new ContainerAwareCallback;

		$this->assertEquals('foobarz', $class->foobarz);

		$container->clear();
	}
}
