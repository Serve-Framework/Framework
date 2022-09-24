<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\deployment;

use serve\deployment\webhooks\WebhookInterface;

/**
 * Frameowrk deployment interface.
 *
 * @author Joe J. Howard
 */
class Deployment
{
    /**
     * Webhook interface.
     *
     * @var \serve\deployment\webhooks\WebhookInterface
     */
    private $webhook;

    /**
     * Constructor.
     *
     * @param \serve\deployment\webhooks\WebhookInterface $webhook Webhook deployment impelementation
     */
    public function __construct(WebhookInterface $webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * Update the framework.
     *
     * @return \serve\deployment\webhooks\WebhookInterface
     */
    public function webhook(): WebhookInterface
    {
        return $this->webhook;
    }

    /**
     * Update the framework.
     *
     * @return mixed
     */
    public function update()
    {
        return $this->webhook->deploy();
    }
}
