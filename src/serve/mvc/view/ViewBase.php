<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\mvc\view;

use serve\common\MagicObjectArray;

/**
 * View abstract.
 *
 * @author Joe J. Howard
 */
abstract class ViewBase extends MagicObjectArray
{
	/**
	 * Array of files to include when rendering.
	 *
	 * @var array
	 */
	protected $includes = [];
}
