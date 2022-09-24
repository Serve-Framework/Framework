<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\mvc\view;

use serve\Serve;

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
	 * {@inheritdoc}
	 */
	public function include(string $file): void
	{
		$this->includes[$file] = $file;
	}

	/**
	 * {@inheritdoc}
	 */
	public function includes(array $files): void
	{
		foreach ($files as $path)
		{
			$this->include($path);
		}
	}

	/**
	 * {@inheritdoc}
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
            $serve = Serve::instance();
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
