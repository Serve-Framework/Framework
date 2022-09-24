<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application\services\framework;

use serve\application\services\Service;
use serve\http\cookie\Cookie;
use serve\http\cookie\storage\NativeCookieStorage;
use serve\http\request\Environment;
use serve\http\request\Files;
use serve\http\request\Headers as RequestHeaders;
use serve\http\request\Request;
use serve\http\response\Body;
use serve\http\response\CDN;
use serve\http\response\Format;
use serve\http\response\Headers as ResponseHeaders;
use serve\http\response\Protocol;
use serve\http\response\Response;
use serve\http\response\Status;
use serve\http\route\Router;
use serve\http\session\Flash;
use serve\http\session\Session;
use serve\http\session\storage\FileSessionStorage;
use serve\http\session\storage\NativeSessionStorage;
use serve\http\session\Token;
use function is_numeric;
use function strtotime;

/**
 * HTTP services.
 *
 * @author Joe J. Howard
 */
class HttpService extends Service
{
	/**
	 * {@inheritDoc}
	 */
	public function register(): void
	{
		$this->registerRequest();

		$this->registerCookie();

		$this->registerSession();

		$this->registerResponse();

		$this->registerRouter();
	}

	/**
	 * Registers the Request object.
	 */
	private function registerRequest(): void
	{
		$this->container->singleton('Request', function ()
		{
			return new Request(new Environment, new RequestHeaders, new Files);
		});
	}

	/**
	 * Registers the cookie object.
	 */
	private function registerCookie(): void
	{
		$this->container->singleton('Cookie', function ()
		{
			$cookieConfiguration = $this->container->Config->get('cookie.configurations.' . $this->container->Config->get('cookie.configuration'));

			if (!is_numeric($cookieConfiguration['expire']))
			{
				$cookieConfiguration['expire'] = strtotime($cookieConfiguration['expire']);
			}

			$store = $this->loadCookieStore($cookieConfiguration);

			return new Cookie($store, $cookieConfiguration['name'], $cookieConfiguration['expire']);
		});
	}

	/**
	 * Loads the cookie storage implementation.
	 *
	 * @param  array $cookieConfiguration Cookie configuration to use
	 * @return mixed
	 */
	private function loadCookieStore(array $cookieConfiguration)
	{
		$storeConfig = $cookieConfiguration['storage'];

		if ($storeConfig['type'] === 'native')
		{
			return $this->nativeCookieStore($storeConfig, $cookieConfiguration);
		}
	}

	/**
	 * Loads the cookie storage implementation.
	 *
	 * @param  array                                          $storeConfig         Configuration for the storage
	 * @param  array                                          $cookieConfiguration Configuration for cookie sending/reading
	 * @return \serve\http\cookie\storage\NativeCookieStorage
	 */
	private function nativeCookieStore(array $storeConfig, array $cookieConfiguration): NativeCookieStorage
	{
		return new NativeCookieStorage($this->container->Crypto, $cookieConfiguration);
	}

	/**
	 * Registers the session object.
	 */
	private function registerSession(): void
	{
		$this->container->singleton('Session', function ()
		{
			$sessionConfiguration = $this->container->Config->get('session.configurations.' . $this->container->Config->get('session.default'));

			if (!is_numeric($sessionConfiguration['expire']))
			{
				$sessionConfiguration['expire'] = strtotime($sessionConfiguration['expire']);
			}

			$store = $this->loadSessionStore($sessionConfiguration);

			return new Session(new Token, new Flash, $store, $sessionConfiguration);
		});
	}

	/**
	 * Loads the session storage implementation.
	 *
	 * @param  array $cookieConfiguration Cookie configuration to use
	 * @return mixed
	 */
	private function loadSessionStore(array $cookieConfiguration)
	{
		$storeConfig = $cookieConfiguration['storage'];

		if ($storeConfig['type'] === 'native')
		{
			return $this->nativeSessionStore($storeConfig, $cookieConfiguration);
		}
		elseif ($storeConfig['type'] === 'file')
		{
			return $this->fileSessionStore($storeConfig, $cookieConfiguration);
		}
	}

	/**
	 * Loads the native session storage implementation.
	 *
	 * @param  array                                            $storeConfig         Configuration for the storage
	 * @param  array                                            $cookieConfiguration Configuration for session sending/reading
	 * @return \serve\http\session\storage\NativeSessionStorage
	 */
	private function nativeSessionStore(array $storeConfig, array $cookieConfiguration): NativeSessionStorage
	{
		return new NativeSessionStorage($cookieConfiguration, $cookieConfiguration['storage']['path']);
	}

	/**
	 * Loads the file session storage implementation.
	 *
	 * @param  array                                          $storeConfig         Configuration for the storage
	 * @param  array                                          $cookieConfiguration Configuration for session sending/reading
	 * @return \serve\http\session\storage\FileSessionStorage
	 */
	private function fileSessionStore(array $storeConfig, array $cookieConfiguration): FileSessionStorage
	{
		return new FileSessionStorage($this->container->Crypto, $this->container->Filesystem, $cookieConfiguration, $cookieConfiguration['storage']['path']);
	}

	/**
	 * Get the HTTP response CDN.
	 *
	 * @return \serve\http\response\CDN
	 */
	private function getCDN(): CDN
	{
		return new CDN($this->container->Request->environment()->HTTP_HOST, $this->container->Config->get('cdn.host'), $this->container->Config->get('cdn.enabled'));
	}

	/**
	 * Get the HTTP response protocol.
	 *
	 * @return \serve\http\response\Protocol
	 */
	private function getProtocol(): Protocol
	{
		return new Protocol($this->container->Request->environment()->HTTP_PROTOCOL);
	}

	/**
	 * Registers the response object.
	 */
	private function registerResponse(): void
	{
		$this->container->singleton('Response', function ()
		{
			return new Response($this->getProtocol(), new Format, new Body, new Status, new ResponseHeaders, $this->container->Cookie, $this->container->Session, $this->getCDN(), $this->container->View, $this->container->Request, $this->container->Config->get('cache.http_cache_enabled'), $this->container->Config->get('cache.http_max_age'));
		});
	}

	/**
	 * Registers the router object.
	 */
	private function registerRouter(): void
	{
		$this->container->singleton('Router', function ($container)
		{
			return new Router($container->Request, $container->Onion, $container->Config->get('application.send_response'));
		});
	}
}
