<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\pixl;

use RuntimeException;
use serve\pixl\Image;
use serve\pixl\processor\ProcessorInterface;
use serve\tests\TestCase;

/**
 * @group unit
 */
class PixlTest extends TestCase
{
	/**
	 *
	 */
	public function getProcessor()
	{
		return $this->mock(ProcessorInterface::class);
	}

	/**
	 *
	 */
	public function testConstructor(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$image = new Image($processor, __FILE__);
	}

	/**
	 *
	 */
	public function testConstructorWithNonExistingFile(): void
	{
		$this->expectException(RuntimeException::class);

		$processor = $this->getProcessor();

		$image = new Image($processor, 'foobar.png');
	}

	/**
	 *
	 */
	public function testSave(): void
	{
		$this->expectException(RuntimeException::class);

		$processor = $this->getProcessor();

		$image = new Image($processor, 'foobar.png');

		$image->save();
	}

	/**
	 *
	 */
	public function testResizeToPixelSize(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('resize')->with(300, 300, false)->once();

		$image = new Image($processor, __FILE__);

		$image->resize(300, 300);
	}

	/**
	 *
	 */
	public function testResizeToPixelWithoutRestriction(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('resize')->with(300, 300, true)->once();

		$image = new Image($processor, __FILE__);

		$image->resize(300, 300, true);
	}

	/**
	 *
	 */
	public function testCrop(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('crop')->with(300, 300, false)->once();

		$image = new Image($processor, __FILE__);

		$image->crop(300, 300);
	}

	/**
	 *
	 */
	public function testCropWithEnlarge(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('crop')->with(300, 300, true)->once();

		$image = new Image($processor, __FILE__);

		$image->crop(300, 300, true);
	}

	/**
	 *
	 */
	public function testGetWidth(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('width')->once()->andReturn(10);

		$image = new Image($processor, __FILE__);

		$this->assertSame(10, $image->width());
	}

	/**
	 *
	 */
	public function testGetHeight(): void
	{
		$processor = $this->getProcessor();

		$processor->shouldReceive('load')->with(__FILE__)->once();

		$processor->shouldReceive('height')->once()->andReturn(10);

		$image = new Image($processor, __FILE__);

		$this->assertSame(10, $image->height());
	}
}
