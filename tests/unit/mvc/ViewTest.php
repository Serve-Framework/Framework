<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\mvc;

use serve\mvc\view\View;
use serve\tests\TestCase;

use function fclose;
use function file_put_contents;
use function rename;
use function stream_get_meta_data;
use function tmpfile;

/**
 * @group unit
 */
class ViewTest extends TestCase
{
    /**
     *
     */
    private function getInclude()
    {
        $handle = tmpfile();

        $path = stream_get_meta_data($handle)['uri'];

        fclose($handle);

        file_put_contents($path, '<?php $foo = "bar"; ?>');

        rename($path, $path . '.php');

        return $path . '.php';
    }

    /**
     *
     */
    private function getTemplate()
    {
        $handle = tmpfile();

        $path = stream_get_meta_data($handle)['uri'];

        fclose($handle);

        file_put_contents($path, '<?php echo $foo; ?>');

        rename($path, $path . '.php');

        return $path . '.php';
    }

    /**
     *
     */
    public function testDisplay(): void
    {
        $view = new View;

        $view->includeServe(false);

        $this->assertEquals('bar', $view->display($this->getTemplate(), ['foo' => 'bar']));
    }

    /**
     *
     */
    public function testInclude(): void
    {
        $view = new View;

        $view->includeServe(false);

        $view->include($this->getInclude());

        $this->assertEquals('bar', $view->display($this->getTemplate()));
    }

    /**
     *
     */
    public function testArrayAccess(): void
    {
        $view = new View;

        $view->includeServe(false);

        $view->set('foo', 'foobaz');

        $this->assertEquals('foobaz', $view->display($this->getTemplate()));
    }
}
