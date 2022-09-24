<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\cli;

use serve\application\Application as BaseApplication;

/**
 * Serve CLI application.
 *
 * @author Joe J. Howard
 */
class Application extends BaseApplication
{

    /**
     * Run the application.
     */
    public function run(): void
    {
        // Run the reactor
        $this->container->Console->run();
    }
}
