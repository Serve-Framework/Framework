<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\mvc\model;

use serve\ioc\ContainerAwareTrait;

/**
 * Base Model.
 *
 * @author Joe J. Howard
 */
abstract class Model
{
	use ContainerAwareTrait;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }
}
