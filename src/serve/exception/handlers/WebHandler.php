<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\exception\handlers;

use Exception;
use serve\exception\ExceptionParserTrait;
use serve\http\request\Request;
use serve\http\response\exceptions\MethodNotAllowedException;
use serve\http\response\exceptions\RequestException;
use serve\http\response\Response;
use serve\mvc\view\View;
use Throwable;
use function dirname;
use function implode;
use function in_array;
use function intval;
use function json_encode;
use function time;

/**
 * Error web handler.
 *
 * @author Joe J. Howard
 */
class WebHandler
{
	use ExceptionParserTrait;

	/**
	 * Request instance.
	 *
	 * @var \serve\http\request\Request
	 */
	protected $request;

	/**
	 * Response instance.
	 *
	 * @var \serve\http\response\Response
	 */
	protected $response;

	/**
	 * View instance.
	 *
	 * @var \serve\mvc\view\View
	 */
	protected $view;

	/**
	 * Constructor.
	 *
	 * @param \serve\http\request\Request   $request  Request instance
	 * @param \serve\http\response\Response $response Response instance
	 * @param \serve\mvc\view\View          $view     View instance
	 */
	public function __construct(Request $request, Response $response, View $view)
	{
		$this->request = $request;

		$this->response = $response;

		$this->view = $view;
	}

	/**
	 * Should we return the error as JSON?
	 *
	 * @return bool
	 */
	protected function returnAsJson(): bool
	{
		$jsonMimeTypes = ['application/json', 'text/json'];

		if($this->request->isAjax() || in_array($this->response->format()->get(), $jsonMimeTypes))
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns a detailed error page.
	 *
	 * @param  Throwable $exception    Exception
	 * @param  bool      $returnAsJson Should we return JSON?
	 * @param  bool      $isBot        Is the user-agent a bot?
	 * @return string
	 */
	protected function getDetailedError(Throwable $exception, bool $returnAsJson, bool $isBot): string
	{
		$vars =
		[
    		'errcode'      => $exception->getCode(),
    		'errName'      => $this->errName($exception),
    		'errtype'      => $this->errtype($exception),
    		'errtime'      => time(),
    		'errmsg'       => $exception->getMessage(),
    		'errfile'      => $exception->getFile(),
    		'errline'      => intval($exception->getLine()),
    		'errClass'     => $this->errClass($exception),
    		'errTrace'     => $this->errTrace($exception),
    		'errUrl'       => $this->request->environment()->REQUEST_URL,
    		'clientIP'     => $this->request->environment()->REMOTE_ADDR,
    		'logFiles'     => [],
    		'errFileLines' => $this->errSource($exception),
    	];

    	// Bots get a plain error message
    	if ($isBot)
    	{
    		return $vars['errmsg'];
    	}

		if ($returnAsJson)
		{
			return json_encode($vars);
		}

		// Return detailed error view
		return $this->view->display(dirname(__FILE__) . '/views/debug.php', $vars);
	}

	/**
	 * Returns a generic error page.
	 *
	 * @param  Throwable $exception    Exception
	 * @param  bool      $returnAsJson Should we return JSON?
	 * @return string
	 */
	protected function getGenericError(Throwable $exception, bool $returnAsJson): string
	{
		if ($returnAsJson)
		{
			return json_encode(['message' => $exception instanceof RequestException ? $exception->getMessage() : 'Aw, snap! An error has occurred while processing your request.']);
		}

		// Get details from status object
		$vars =
		[
			'code'    => $this->response->status()->get(),
			'title'   => $this->response->status()->message(),
			'body'    => $exception instanceof RequestException ? $exception->getMessage() : 'Aw, snap! An error has occurred while processing your request.',
		];

		return $this->view->display(dirname(__FILE__) . '/views/generic.php', $vars);
	}

	/**
	 * Display an error page to end user.
	 *
	 * @param  Throwable $exception   Exception
	 * @param  bool      $showDetails Should we show a detailed error page
	 * @return false
	 */
	public function handle(Throwable $exception, bool $showDetails = true): bool
	{
		// Set the status
		if ($exception instanceof RequestException)
		{
			$status = $exception->getCode();

			if ($exception instanceof MethodNotAllowedException)
			{
				$this->response->headers()->set('allows', implode(',', $exception->getAllowedMethods()));
			}
		}
		else
		{
			$status = 500;
		}

		// Set the
		$this->response->status()->set($status);

		// Set appropriate content type header
		if (($returnAsJson = $this->returnAsJson()) === true)
		{
			$this->response->format()->set('application/json');
		}
		else
		{
			$this->response->format()->set('text/html');
		}

		// Set the response body
		if ($showDetails)
		{
			$this->response->body()->set($this->getDetailedError($exception, $returnAsJson, $this->request->isBot()));
		}
		else
		{
			$this->response->body()->set($this->getGenericError($exception, $returnAsJson));
		}

		$this->response->send();

		// Return false to stop further error handling
		return false;
	}
}
