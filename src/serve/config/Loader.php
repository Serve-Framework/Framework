<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\config;

use serve\file\Filesystem;
use \RuntimeException;
use function vsprintf;

/**
 * Cascading file loader.
 *
 * @author Joe J. Howard
 */
class Loader
{
	/**
	 * File system instance.
	 *
	 * @var \serve\file\Filesystem
	 */
	protected $filesystem;

	/**
	 * Path to config
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Constructor.
	 *
	 * @param \serve\file\Filesystem $filesystem File system instance
	 * @param string                           $path       Default path
	 */
	public function __construct(Filesystem $filesystem, string $path)
	{
		$this->filesystem = $filesystem;

		$this->path = $path;
	}

	/**
	 * Loads the configuration file.
	 *
	 * @param  string      $file        File name
	 * @param  null|string $environment Environment
	 * @return array
	 */
	public function load(string $file, string $environment = null): array
	{
		// Load default config
		$path = $this->getFilePath($file, null);

		if($this->filesystem->exists($path))
		{
			$config = $this->filesystem->include($path);
		}

		// Validate
		if (!isset($config))
		{
			throw new RuntimeException(vsprintf('%s(): The [ %s ] config file does not exist.', [__METHOD__, $file]));
		}

		// Merge environment specific configuration
		if($environment !== null)
		{
			$envpath = $this->getFilePath($file, $environment);

			if($this->filesystem->exists($envpath))
			{
				$config = array_replace_recursive($config, $this->filesystem->include($envpath));
			}
		}

		return $config;
	}

	/**
	 * Saves the configuration data.
	 *
	 * @param  array       $data        Data to save
	 * @param  null|string $environment Environment
	 */
	public function save(array $data, string $environment = null)
	{
		foreach ($data as $file => $fileData)
		{
			$path = $this->getFilePath($file, $environment);

			$this->filesystem->putContents($path, "<?php\nreturn\n" . $this->var_export($fileData) . ";\n?>\n");
		}

		return true;
	}

	/**
	 * Returns the path for a config file
	 *
	 * @param  string      $file        File name
	 * @param  null|string $environment Environment
	 * @return string
	 */
	protected function getFilePath(string $file, string $environment = null): string
	{
		return !$environment ? $this->path . DIRECTORY_SEPARATOR . $file . '.php' : $this->path . DIRECTORY_SEPARATOR . $environment . DIRECTORY_SEPARATOR . $file . '.php';
	}

	/**
	 * Pretty Print "var_export".
	 *
	 * @param  mixed  $data Data to save
	 * @param  array  $opts Print options (optional) (default [])
	 * @return string
	 */
	protected function var_export($data, array $opts = []): string
	{
		$defaults = [
			'indent'      => '',
			'tab'         => '    ',
			'array-align' => true,
		];

		$opts = array_merge($defaults, $opts);

	    switch (gettype($data))
	    {
	        case 'array':
	            $r         = [];
	            $indexed   = array_keys($data) === range(0, count($data) - 1);
	            $maxLength = $opts['array-align'] && !empty($data) ? max(array_map('strlen', array_map('trim', array_keys($data)))) + 2 : 0;

	            foreach ($data as $key => $value)
	            {
	                $key = str_replace("'' . \"\\0\" . '*' . \"\\0\" . ", '', $this->var_export($key));

	                $r[] = $opts['indent'] . $opts['tab']
	                    . ($indexed ? '' : str_pad($key, $maxLength) . ' => ')
	                    . $this->var_export($value, array_merge($opts, ['indent' => $opts['indent'] . $opts['tab']]));
	            }

	            return "\n" . str_repeat(' ', strlen($opts['indent'])) . "[\n" . implode(",\n", $r) . "\n" . $opts['indent'] . ']';

	        case 'boolean':

	            return $data ? 'true' : 'false';

	        case 'NULL':

	            return 'null';

	        default:

	            return var_export($data, true);
	    }
	}
}
