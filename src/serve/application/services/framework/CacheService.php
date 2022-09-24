<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\framework;

use serve\application\services\Service;
use serve\cache\Cache;
use serve\cache\stores\FileStore;

/**
 * Cache service.
 *
 * @author Joe J. Howard
 */
class CacheService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Cache', function ()
		{
			$cacheConfiguration = $this->container->Config->get('cache.configurations.' . $this->container->Config->get('cache.default'));

			if (!is_numeric($cacheConfiguration['expire']))
			{
				$cacheConfiguration['expire'] = strtotime($cacheConfiguration['expire']);
			}

			return new Cache($cacheConfiguration['expire'], $this->loadCacheStore($cacheConfiguration));
		});
	}

	/**
	 * Get the cache store.
	 *
	 * @param  array $cacheConfiguration Configuration options for the cache
	 * @return mixed
	 */
	private function loadCacheStore(array $cacheConfiguration)
	{
		$type = $cacheConfiguration['type'];

		if ($type === 'file')
		{
			return $this->fileStore($cacheConfiguration['path']);
		}
	}

	/**
	 * Returns the file storage implementation.
	 *
	 * @param  string                        $path Directory to store cached files
	 * @return \serve\cache\stores\FileStore
	 */
	private function fileStore(string $path): FileStore
	{
		return new FileStore($this->container->Filesystem, $path);
	}
}
