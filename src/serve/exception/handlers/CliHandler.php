<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\exception\handlers;

use ErrorException;
use Exception;
use serve\cli\input\Input;
use serve\cli\output\helpers\OrderedList;
use serve\cli\output\helpers\UnorderedList;
use serve\cli\output\Output;
use serve\exception\ExceptionLogicTrait;
use Throwable;
use function array_keys;
use function get_class;
use function in_array;
use function intval;

/**
 * Error CLI handler.
 *
 * @author Joe J. Howard
 */
class CliHandler
{
	use ExceptionLogicTrait;

	/**
	 * Error.
	 *
	 * @var Exception|\serve\http\response\exceptions\ForbiddenException|\serve\http\response\exceptions\InvalidTokenException|\serve\http\response\exceptions\MethodNotAllowedException|\serve\http\response\exceptions\NotFoundException|\serve\http\response\exceptions\RequestException|\serve\http\response\exceptions\Stop|Throwable
	 */
	protected $exception;

	/**
	 * Response instance.
	 *
	 * @var \serve\cli\output\Output
	 */
	private $output;

	/**
	 * View instance.
	 *
	 * @var \serve\cli\input\Input
	 */
	private $input;

	/**
	 * Constructor.
	 *
	 * @param Throwable                $exception Exception being thrown
	 * @param \serve\cli\input\Input   $input     Input
	 * @param \serve\cli\output\Output $output    Output
	 */
	public function __construct(Throwable $exception, Input $input, Output $output)
	{
		$this->exception = $exception;

		$this->input = $input;

		$this->output = $output;
	}

	/**
	 * Display an error page to end user.
	 *
	 * @param  bool  $showDetails Should we show a detailed error page
	 * @return false
	 */
	public function handle(bool $showDetails = true): bool
	{
		// Set the response body
		if ($showDetails)
		{
			$this->output->write($this->getDetailedError());
		}
		else
		{
			$this->output->write('An error has occurred while running the command.');
		}

		// Return false to stop further error handling
		return false;
	}

	/**
	 * Determines the exception type.
	 *
	 * @param  Throwable $exception Throwable
	 * @return string
	 */
	protected function determineExceptionType(Throwable $exception): string
	{
		if ($exception instanceof ErrorException)
		{
			$code = $exception->getCode();

			$codes =
			[
				E_ERROR             => 'Fatal Error',
				E_PARSE             => 'Parse Error',
				E_COMPILE_ERROR     => 'Compile Error',
				E_COMPILE_WARNING   => 'Compile Warning',
				E_STRICT            => 'Strict Mode Error',
				E_NOTICE            => 'Notice',
				E_WARNING           => 'Warning',
				E_RECOVERABLE_ERROR => 'Recoverable Error',
				E_DEPRECATED        => 'Deprecated',
				E_USER_NOTICE       => 'Notice',
				E_USER_WARNING      => 'Warning',
				E_USER_ERROR        => 'Error',
				E_USER_DEPRECATED   => 'Deprecated',
			];

			return in_array($code, array_keys($codes)) ? $codes[$code] : 'ErrorException';
		}

		return get_class($exception);
	}

	/**
	 * Escape formatting tags.
	 *
	 * @param  string $string String to escape
	 * @return string
	 */
	private function escape(string $string): string
	{
		return $this->output->formatter()->escape($string);
	}

	/**
	 * Returns a detailed error page.
	 *
	 * @return string
	 */
	private function getDetailedError(): string
	{
		$ul = new UnorderedList($this->output);
		$ol = new OrderedList($this->output);

		$error =
		[
			'TYPE    : ' . $this->determineExceptionType($this->exception),
			'MESSAGE : ' . $this->exception->getMessage(),
			'CLASS   : ' . $this->errClass(),
			'FILE    : ' . $this->exception->getFile(),
			'LINE    : ' . intval($this->exception->getLine()),
			'TRACE   : ',
		];

		return '<bg_red><white>' . $ul->render($error) . $ol->render($this->errTrace()) . '</white></bg_red>';
	}
}
