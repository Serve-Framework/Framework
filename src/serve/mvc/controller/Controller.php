<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\mvc\controller;

use \Closure;
use serve\http\request\Request;
use serve\http\response\Response;
use serve\mvc\model\Model;
use serve\utility\Callback;

/**
 * Base controller.
 *
 * @author Joe J. Howard
 */
abstract class Controller
{
	use ControllerHelperTrait;

	/**
	 * Next middleware closure.
	 *
	 * @var \Closure
	 */
	protected $nextMiddleware;

	/**
	 * Model.
	 *
	 * @var \serve\mvc\model\Model
	 */
	protected $model;

    /**
     * Constructor.
     *
     * @param \serve\http\request\Request   $request    Request instance
     * @param \serve\http\response\Response $response   Response instance
     * @param \Closure                                $next       Next middleware closure
     * @param string                                  $modelClass Full namespaced class name of the model
     */
    public function __construct(Request $request, Response $response, Closure $next, string $modelClass)
    {
    	$this->nextMiddleware = $next;

    	$this->loadModel($modelClass);
    }

    /**
     * Loads and instantiates the model.
     *
     * @param string $className Full namespaced class name of the model
     */
    protected function loadModel(string $className): void
	{
		$this->model = $this->instantiateModel($className);
	}

	/**
	 * Instantiates and returns the model instance.
	 *
	 * @param  string $class Full namespaced class name of the model
	 * @return \serve\mvc\model\Model
	 */
	private function instantiateModel(string $class): Model
	{
		return Callback::newClass($class);
	}
}
