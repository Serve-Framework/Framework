<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\application;

use serve\config\Config;
use serve\config\Loader;
use serve\file\Filesystem;
use serve\ioc\Container;
use serve\Serve;
use function array_keys;
use function date_default_timezone_set;
use function getenv;
use function ini_set;
use function mb_internal_encoding;
use function mb_language;
use function mb_regex_encoding;
use function microtime;

/**
 * Serve framework main class file.
 *
 * @author Joe J. Howard
 */
abstract class Application extends Serve
{
    /**
     * Singleton instance of self.
     *
     * @var \serve\application\Application
     */
    protected static $instance;

    /**
     * Application start time.
     *
     * @var float
     */
    protected $startTime;

    /**
     * IoC container instance.
     *
     * @var \serve\ioc\Container
     */
    protected $container;

    /**
     * Application path.
     *
     * @var string
     */
    protected $applicationPath;

    /**
     * Booted packages.
     *
     * @var array
     */
    protected $packages = [];

    /**
     * Constructor.
     *
     * @param string $applicationPath Application path
     */
    final protected function __construct(string $applicationPath)
    {
        $this->startTime = microtime(true);

        $this->applicationPath = $applicationPath;
    }

    /**
     * Starts the application and returns a singleton instance of the application.
     *
     *
     * @param  string $applicationPath Application path
     * @return static
     */
    public static function start(string $applicationPath): static
    {
        if(!empty(static::$instance))
        {
            throw new ApplicationException('The application has already been started.');
        }

        static::$instance = new static($applicationPath);

        return static::$instance->boot();
    }

    /**
     * Returns the IOC container.
     *
     * @return \serve\ioc\Container
     */
    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Returns the Serve environment.
     *
     * @return string|null
     */
    public function environment(): ?string
    {
        return getenv('SERVE_ENV') ?: null;
    }

    /**
     * Boot the application dependencies.
     *
     * @return $this
     */
    protected function boot()
    {
        $this->initialize();

        $this->configure();

        $this->registerServices();

        $this->bootstrap();

        return $this;
    }

    /**
     * Sets up the framework core.
     */
    protected function initialize(): void
    {
        $this->registerContainer();

        $this->registerConfig();

        $this->registerFilesystem();
    }

    /**
     * Loads the application bootstrap file.
     */
    protected function bootstrap(): void
    {
        (function ($serve, $container): void
        {
            include "{$this->applicationPath}/bootstrap.php";

        })($this, $this->container);
    }

    /**
     * Register the IOC container.
     */
    protected function registerContainer(): void
    {
        $this->container = Container::instance();

        $this->container->setInstance('Application', $this);
    }

    /**
     * Register the Filesystem.
     */
    protected function registerFilesystem(): void
    {
        $this->container->singleton('Filesystem', function ()
        {
            return new Filesystem;
        });
    }

    /**
     * Register the config.
     */
    protected function registerConfig(): void
    {
        $this->container->singleton('Config', function ()
        {
            return $this->configFactory();
        });
    }

    /**
     * Configure application basics.
     */
    protected function configure(): void
    {
        mb_language('uni');

        mb_regex_encoding($this->container->Config->get('application.charset'));

        mb_internal_encoding($this->container->Config->get('application.charset'));

        date_default_timezone_set($this->container->Config->get('application.timezone'));

        ini_set('date.timezone', $this->container->Config->get('application.timezone'));
    }

    /**
     * Builds a configuration instance.
     *
     * @return \serve\config\Config
     */
    protected function configFactory(): Config
    {
        return new Config(new Loader(new Filesystem, $this->configurationPath()), $this->environment());
    }

    /**
     * Returns the configuration path.
     *
     * @return string
     */
    protected function configurationPath(): string
    {
        return "{$this->applicationPath}/configurations";
    }

    /**
     * Is the application running in the CLI?
     *
     * @return bool
     */
    public function isCommandLine(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * Register required services.
     */
    protected function registerServices(): void
    {
        if ($this->isCommandLine())
        {
            $this->registerClisServices();
        }
        else
        {
            $this->registerWebServices();
        }
    }

    /**
     * Register default services.
     */
    protected function registerWebServices(): void
    {
        foreach (array_keys($this->container->Config->get('application.services')) as $package)
        {
            if ($package === 'cli')
            {
                continue;
            }

            $this->registerPackage($package);
        }
    }

    /**
     * Register cli services.
     */
    protected function registerClisServices(): void
    {
        foreach (array_keys($this->container->Config->get('application.services')) as $package)
        {
            if ($package === 'web')
            {
                continue;
            }

            $this->registerPackage($package);
        }
    }

    /**
     * Registers services in the IoC container.
     *
     * @param string $name Service name
     */
    protected function registerPackage(string $name): void
    {
        foreach ($this->container->Config->get('application.services.' . $name) as $service)
        {
            $this->registerService($service);
        }
    }

    /**
     * Registers services in the IoC container.
     *
     * @param string $service Service name
     */
    protected function registerService(string $service): void
    {
        (new $service($this->container))->register();
    }

    /**
     * Runs the application.
     */
    abstract public function run();
}
