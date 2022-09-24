<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\framework;

use RuntimeException;
use serve\application\services\Service;
use serve\deployment\Deployment;
use serve\deployment\webhooks\Github;
use serve\deployment\webhooks\WebhookInterface;
use serve\shell\Shell;

/**
 * Framework deployment service.
 *
 * @author Joe J. Howard
 */
class DeploymentService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Deployment', function()
		{
			return new Deployment($this->webhookInterface());
		});
	}

	/**
	 * Returns the deployment implementation.
	 *
	 * @return \serve\deployment\webhooks\WebhookInterface
	 */
	private function webhookInterface(): WebhookInterface
	{
		$service = $this->container->Config->get('application.deployment.implementation');

		if ($service === 'github')
		{
			return new Github($this->container->Request, $this->container->Response, new Shell, $this->container->Config->get('application.deployment.token'));
		}
		else
		{
			throw new RuntimeException('Deployment service "' . $service . '" unrecognized.');
		}
	}
}
