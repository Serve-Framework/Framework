<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\ioc;

/**
 * Container aware trait.
 *
 * @property \serve\application\Application               $Application
 * @property \serve\access\Access               $Access
 * @property \serve\cache\Cache                 $Cache
 * @property \serve\config\Config               $Config
 * @property \serve\console\Console             $Console
 * @property \serve\crawler\CrawlerDetect       $UserAgent
 * @property \serve\security\Crypto             $Crypto
 * @property \serve\database\Database           $Database
 * @property \serve\deployment\Deployment       $Deployment
 * @property \serve\event\Events                $Events
 * @property \serve\exception\ErrorHandler      $ErrorHandler
 * @property \serve\file\Filesystem             $Filesystem
 * @property \serve\event\Filters               $Filters
 * @property \serve\auth\Gatekeeper             $Gatekeeper
 * @property \serve\http\request\Request        $Request
 * @property \serve\http\response\Response      $Response
 * @property \serve\http\cookie\Cookie          $Cookie
 * @property \serve\http\session\Session        $Session
 * @property \serve\http\route\Router           $Router
 * @property \serve\mvc\view\View               $View
 * @property \serve\onion\Onion                 $Onion
 * @property \serve\pixl\processor\GD           $Pixl
 * @property \serve\security\spam\SpamProtector $Spam
 * @property \serve\validator\ValidatorFactory  $Validator
 * 
 * @property \serve\cli\Cli                     $Cli
 * @property \serve\cli\input\Input             $Input
 * @property \serve\cli\output\Output           $Output
 */
trait ContainerAwareTrait
{
	/**
	 * IoC container instance.
	 *
	 * @var \serve\ioc\Container
	 */
	protected $container;

	/**
	 * Sets and or gets the container.
	 *
	 * @return \serve\ioc\Container
	 */
	public function container(): Container
	{
		if (!$this->container)
		{
			$this->container = Container::instance();
		}

		return $this->container;
	}

	/**
	 * Resolves item from the container using overloading.
	 *
	 * @param  string $key Key
	 * @return mixed
	 */
	public function __get(string $key)
	{
		return $this->container()->get($key);
	}
}
