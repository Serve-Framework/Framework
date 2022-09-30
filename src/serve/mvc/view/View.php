<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\mvc\view;

use serve\application\web\Application;

use function array_merge;
use function extract;
use function file_exists;
use function ob_get_clean;
use function ob_start;

/**
 * Default view implementation.
 *
 * @author Joe J. Howard
 */
class View extends ViewBase implements ViewInterface
{
    /**
     * Should the "$serve" variable be made available to all templates ?
     *
     * @var bool
     */
    private $includeServe = true;

    /**
     * Should the "$serve" variable be made available to all templates ?
     *
     * @param bool $toggle Enable/disable local serve instance (optional) (default true)
     */
    public function includeServe(bool $toggle = true): void
    {
        $this->includeServe = $toggle;
    }

	/**
	 * {@inheritDoc}
	 */
	public function include(string $file): void
	{
		$this->includes[$file] = $file;
	}

	/**
	 * {@inheritDoc}
	 */
	public function includes(array $files): void
	{
		foreach ($files as $path)
		{
			$this->include($path);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function display(string $file, array $data = []): string
	{
		return $this->sandbox($file, $data);
	}

    /**
     * Sandbox and output a template.
     *
     * @param  string $file Absolute path to template file
     * @param  array  $data Assoc array of variables (optional) (default [])
     * @return string
     */
    private function sandbox(string $file, array $data): string
    {
        $data = array_merge($this->data, $data);

        if ($this->includeServe === true)
        {
            $serve = Application::instance();
        }

        foreach ($this->includes as $include)
        {
            if (file_exists($include))
            {
                require_once $include;
            }
        }

        extract($data);

        ob_start();

        require $file;

        return ob_get_clean();
    }
}
