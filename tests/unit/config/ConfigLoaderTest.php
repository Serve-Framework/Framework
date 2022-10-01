<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\tests\unit\framework\config;

use RuntimeException;
use serve\config\Loader;
use serve\file\Filesystem;
use serve\tests\TestCase;

/**
 * @group unit
 */
class ConfigLoaderTest extends TestCase
{
    /**
     *
     */
    public function getFilesystem()
    {
        return $this->mock(Filesystem::class);
    }

    /**
     *
     */
    public function testLoad(): void
    {
        $fileSystem = $this->getFilesystem();

        $fileSystem->shouldReceive('exists')->once()->with('/app/config/settings.php')->andReturn(true);

        $fileSystem->shouldReceive('include')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello']);

        $loader = new Loader($fileSystem, '/app/config');

        $this->assertEquals(['greeting' => 'hello'], $loader->load('settings'));
    }

    /**
     */
    public function testLoadNonExistingFile(): void
    {
        $this->expectException(RuntimeException::class);

        $fileSystem = $this->getFilesystem();

        $fileSystem->shouldReceive('exists')->once()->with('/app/config/settings.php')->andReturn(false);

        $loader = new Loader($fileSystem, '/app/config');

        $loader->load('settings');
    }

    /**
     *
     */
    public function testLoadEvironmentOverride(): void
    {
        $fileSystem = $this->getFilesystem();

        $fileSystem->shouldReceive('exists')->once()->with('/app/config/settings.php')->andReturn(true);

        $fileSystem->shouldReceive('include')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello', 'goodbye' => 'sayonara']);

        $fileSystem->shouldReceive('exists')->once()->with('/app/config/dev/settings.php')->andReturn(true);

        $fileSystem->shouldReceive('include')->once()->with('/app/config/dev/settings.php')->andReturn(['greeting' => 'konnichiwa']);

        $loader = new Loader($fileSystem, '/app/config');

        $this->assertEquals(['greeting' => 'konnichiwa', 'goodbye' => 'sayonara'], $loader->load('settings', 'dev'));
    }

    /**
     *
     */
    public function testSave(): void
    {
        $fileSystem = $this->getFilesystem();

        $fileSystem->shouldReceive('putContents')->once()->with('/app/config/settings.php', "<?php\nreturn\n\n[\n    'greeting' => 'konnichiwa',\n    'goodbye'  => 'sayonara'\n];\n?>\n")->andReturn(true);

        $loader = new Loader($fileSystem, '/app/config');

        $loader->save(['settings' => ['greeting' => 'konnichiwa', 'goodbye' => 'sayonara']]);
    }

    /**
     *
     */
    public function testSaveEnv(): void
    {
        $fileSystem = $this->getFilesystem();

        $fileSystem->shouldReceive('putContents')->once()->with('/app/config/dev/settings.php', "<?php\nreturn\n\n[\n    'greeting' => 'konnichiwa',\n    'goodbye'  => 'sayonara'\n];\n?>\n")->andReturn(true);

        $loader = new Loader($fileSystem, '/app/config');

        $loader->save(['settings' => ['greeting' => 'konnichiwa', 'goodbye' => 'sayonara']], 'dev');
    }
}
