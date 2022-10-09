<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\common;

use serve\common\MagicObjectArray;
use serve\tests\TestCase;

use function is_array;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TestMagicObjectArray extends MagicObjectArray
{

}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class MagicArrayAccessTest extends TestCase
{
    /**
     *
     */
    public function testInstantiate(): void
    {
        $arrayAccess = new TestMagicObjectArray(['foo' => 'bar']);

        $this->assertEquals('bar', $arrayAccess->foo);
    }

    /**
     *
     */
    public function testInstantiateDotNotation(): void
    {
        $arrayAccess = new TestMagicObjectArray(['foo.bar' => 'bar']);

        $this->assertEquals(['bar' => 'bar'], $arrayAccess->foo);

        $arrayAccess = new TestMagicObjectArray(['foo.bar.baz' => 'bar']);

        $this->assertEquals(['bar' => ['baz' => 'bar']], $arrayAccess->foo);
    }

    /**
     *
     */
    public function testSetGetMagic(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->foo = 'foo';

        $arrayAccess->{'fooz.bar'} = 'foozbar';

        $this->assertEquals('foo', $arrayAccess->foo);

        $this->assertTrue(is_array($arrayAccess->fooz));

        $this->assertEquals('foozbar', $arrayAccess->{'fooz.bar'});
    }

    /**
     *
     */
    public function testSetGetNestedMagic(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->foo = ['bar' => 'baz'];

        $this->assertEquals(['bar' => 'baz'], $arrayAccess->foo);

        $this->assertEquals('baz', $arrayAccess->{'foo.bar'});
    }

    /**
     *
     */
    public function testHasMagic(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->foo = 'baz';

        $arrayAccess->{'fooz.bar'} = 'foozbar';

        $this->assertTrue(isset($arrayAccess->foo));

        $this->assertFalse(isset($arrayAccess->baz));

        $this->assertTrue(isset($arrayAccess->{'fooz.bar'}));
    }

    /**
     *
     */
    public function testHasNestedMagic(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->foo = ['bar' => 'baz'];

        $this->assertTrue(isset($arrayAccess->foo));

        $this->assertTrue(isset($arrayAccess->foo['bar']));

        $this->assertFalse(isset($arrayAccess->baz));

        $this->assertFalse(isset($arrayAccess->foo['baz']));
    }

    /**
     *
     */
    public function testRemoveMagic(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->foo = ['bar' => 'baz'];

        unset($arrayAccess->foo);

        $this->assertEquals(null, $arrayAccess->foo);

        $arrayAccess->{'fooz.bar'} = 'foozbar';

        unset($arrayAccess->{'fooz'});

        $this->assertEquals(null, $arrayAccess->fooz);
    }

    /**
     *
     */
    public function testAsArray(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->foo = ['bar' => 'baz'];

        $this->assertEquals(['foo' => ['bar' => 'baz']], $arrayAccess->asArray());

        $this->assertEquals(['foo' => ['bar' => 'baz']], $arrayAccess->get());
    }

    /**
     *
     */
    public function testItator(): void
    {
        $arrayAccess = new TestMagicObjectArray(['foo.bar' => 'foobar', 'foo.baz' => 'foobaz', 'bar.foo' => 'barfoo']);

        $count = 0;

        foreach ($arrayAccess as $key => $value)
        {
            $count++;
        }

        $this->assertEquals(2, $count);
    }

    /**
     *
     */
    public function testSetGet(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->set('foo', 'baz');

        $this->assertEquals('baz', $arrayAccess->get('foo'));

        $arrayAccess->set('fooz.bar', 'foozbar');

        $this->assertEquals('foozbar', $arrayAccess->get('fooz.bar'));
    }

    /**
     *
     */
    public function testSetGetNested(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->set('foo', ['bar' => 'baz']);

        $this->assertEquals(['bar' => 'baz'], $arrayAccess->get('foo'));

        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->set('foo.bar', 'baz');

        $this->assertEquals('baz', $arrayAccess->get('foo.bar'));
    }

    /**
     *
     */
    public function testSetGetMultiple(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->setMultiple(['foo' => ['bar' => 'baz']]);

        $this->assertEquals(['bar' => 'baz'], $arrayAccess->get('foo'));

        $arrayAccess->set('foo.bar', 'foobaz');

        $this->assertEquals('foobaz', $arrayAccess->get('foo.bar'));
    }

    /**
     *
     */
    public function testHas(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->set('foo', 'baz');

        $arrayAccess->set('fooz.bar', 'foozbar');

        $this->assertTrue($arrayAccess->has('foo'));

        $this->assertFalse($arrayAccess->has('baz'));

        $this->assertTrue($arrayAccess->has('fooz.bar'));

        $this->assertFalse($arrayAccess->has('fooz.baz'));
    }

    /**
     *
     */
    public function testHasNested(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->set('foo', ['bar' => 'baz']);

        $this->assertTrue($arrayAccess->has('foo'));

        $this->assertTrue($arrayAccess->has('foo.bar'));

        $this->assertFalse($arrayAccess->has('baz'));

        $this->assertFalse($arrayAccess->has('foo.baz'));
    }

    /**
     *
     */
    public function testRemove(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->set('foo', ['bar' => 'baz']);

        $arrayAccess->remove('foo.bar');

        $arrayAccess->remove('foo');

        $this->assertEquals(null, $arrayAccess->get('foo'));
    }

    /**
     *
     */
    public function testClear(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->set('foo', ['bar' => 'baz']);

        $arrayAccess->clear();

        $this->assertFalse($arrayAccess->has('foo'));
    }

    /**
     *
     */
    public function testOverwrite(): void
    {
        $arrayAccess = new TestMagicObjectArray;

        $arrayAccess->set('foo', ['bar' => 'baz']);

        $arrayAccess->overwrite(['foo' => ['bar' => 'bazz']]);

        $this->assertEquals('bazz', $arrayAccess->get('foo.bar'));
    }
}
