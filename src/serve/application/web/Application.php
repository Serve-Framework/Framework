<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\web;

use serve\application\Application as BaseApplication;

/**
 * Web application.
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
        $this->precheckAccess();

        $this->container->Router->dispatch();

        $this->container->Onion->peel();

        if ($this->container->Config->get('application.send_response') === true)
        {
            $this->container->Response->send();
        }

        $this->container->ErrorHandler->restore();
    }

    /**
     * Validate the incoming request with the access conditions.
     */
    protected function precheckAccess(): void
    {
        if ($this->container->Access->ipBlockEnabled() && !$this->container->Access->isIpAllowed())
        {
            $this->container->Access->block();
        }
    }
}
