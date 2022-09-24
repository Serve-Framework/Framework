<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\cli\commands;

use serve\console\Command;
use serve\utility\Pluralize;

/**
 * Generate application secret.
 *
 * @author Joe J. Howard
 */
class RoutesList extends Command
{
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Lists available HTTP routes';

	/**
	 * {@inheritDoc}
	 */
	protected $commandInformation =
	[
		['--method', 'Filter by HTTP request method [GET|POST|HEAD|PUT]', 'Yes'],
	];

	/**
	 * {@inheritDoc}
	 */
	public function execute(): void
	{
		$routes   = $this->container->Router->getRoutes();
		$filter   = $this->input->parameter('method');
		$cols     = ['<green>Route</green>', '<green>Method</green>', '<green>Callback</green>', '<green>Args</green>'];
		$rows     = [];

		usort($routes, function ($a, $b)
		{
			return strcmp($a['uri'], $b['uri']);
		});

		foreach ($routes as $i => $route)
		{
			if ($filter && strtoupper($filter) !== $route['method'])
			{
				continue;
			}

			$route['args'] = is_array($route['args']) ? implode(', ', $route['args']) : $route['args'];

			$rows[] = array_values($route);
		}

		$this->write(count($rows) . ' available ' . Pluralize::convert('route', count($rows)) . ':');

		$this->table($cols, $rows);
	}
}
