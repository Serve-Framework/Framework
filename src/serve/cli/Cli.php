<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\cli;

use serve\cli\input\Input;
use serve\cli\output\Output;

/**
 * CLI utility.
 *
 * @author Joe J. Howard
 */
class Cli
{
	/**
	 * Input instance.
	 *
	 * @var \serve\cli\input\Input
	 */
	private $input;

	/**
	 * Output instance.
	 *
	 * @var \serve\cli\output\Output
	 */
	private $output;

    /**
     * Environment instance.
     *
     * @var \serve\cli\Environment
     */
    private $environment;

    /**
     * Constructor.
     *
     * @param \serve\cli\input\Input   $input       Input instance
     * @param \serve\cli\output\Output $output      Output instance
     * @param \serve\cli\Environment   $environment Environment instance
     */
    public function __construct(Input $input, Output $output, Environment $environment)
    {
        $this->input = $input;

        $this->output = $output;

        $this->environment = $environment;
    }

    /**
     * Returns the input.
     *
     * @return \serve\cli\input\Input
     */
    public function input(): Input
    {
        return $this->input;
    }

    /**
     * Returns the output.
     *
     * @return \serve\cli\output\Output
     */
    public function output(): Output
    {
        return $this->output;
    }

    /**
     * Returns the input.
     *
     * @return \serve\cli\Environment
     */
    public function environment(): Environment
    {
        return $this->environment;
    }
}
