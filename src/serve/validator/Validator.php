<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\validator;

use RuntimeException;
use serve\ioc\Container;
use serve\utility\Arr;
use serve\utility\Str;
use serve\validator\filters\BoolVal as FilterBool;
use serve\validator\filters\Email as FilterEmail;
use serve\validator\filters\FilterInterface;
use serve\validator\filters\FloatingPoint as FilterFloat;
use serve\validator\filters\HtmlDecode as FilterHtmlDecode;
use serve\validator\filters\HtmlEncode as FilterHtmlEncode;
use serve\validator\filters\Integer as FilterInteger;
use serve\validator\filters\Json as FilterJson;
use serve\validator\filters\LowerCase as FilterLowerCase;
use serve\validator\filters\Numeric as FilterNumeric;
use serve\validator\filters\SanitizeString as FilterString;
use serve\validator\filters\StripTags as FilterStripTags;
use serve\validator\filters\Trim as FilterTrim;
use serve\validator\filters\UpperCase as FilterUpperCase;
use serve\validator\filters\UrlDecode as FilterUrlDecode;
use serve\validator\filters\UrlEncode as FilterUrlEncode;
use serve\validator\rules\Alpha;
use serve\validator\rules\Alphanumeric;
use serve\validator\rules\AlphanumericDash;
use serve\validator\rules\AlphaSpace;
use serve\validator\rules\Email;
use serve\validator\rules\ExactLength;
use serve\validator\rules\FloatingPoint;
use serve\validator\rules\GreaterThan;

use serve\validator\rules\GreaterThanOrEqualTo;
use serve\validator\rules\In;
use serve\validator\rules\Integer;
use serve\validator\rules\IP;
use serve\validator\rules\JSON;
use serve\validator\rules\LessThan;
use serve\validator\rules\LessThanOrEqualTo;
use serve\validator\rules\MatchField;
use serve\validator\rules\MaxLength;
use serve\validator\rules\MinLength;
use serve\validator\rules\NotIn;
use serve\validator\rules\Regex;
use serve\validator\rules\Required;
use serve\validator\rules\RuleInterface;
use serve\validator\rules\URL;
use serve\validator\rules\WithParametersInterface;

use function array_unique;
use function compact;
use function in_array;
use function vsprintf;

/**
 * Input validation.
 *
 * @author Joe J. Howard
 */
class Validator
{
	/**
	 * Input.
	 *
	 * @var array
	 */
	private $input;

	/**
	 * Rule sets.
	 *
	 * @var array
	 */
	private $ruleSets;

	/**
	 * Filter sets.
	 *
	 * @var array
	 */
	private $filterSets;

	/**
	 * Container.
	 *
	 * @var \serve\ioc\Container
	 */
	private $container;

	/**
	 * Rules.
	 *
	 * @var array
	 */
	private $rules =
	[
		'alpha'                    => Alpha::class,
		'alpha_space'              => AlphaSpace::class,
		'alpha_dash'               => AlphanumericDash::class,
		'alpha_numeric'            => Alphanumeric::class,
		'email'                    => Email::class,
		'exact_length'             => ExactLength::class,
		'float'                    => FloatingPoint::class,
		'greater_than_or_equal_to' => GreaterThanOrEqualTo::class,
		'greater_than'             => GreaterThan::class,
		'in'                       => In::class,
		'integer'                  => Integer::class,
		'ip'                       => IP::class,
		'json'                     => JSON::class,
		'less_than_or_equal_to'    => LessThanOrEqualTo::class,
		'less_than'                => LessThan::class,
		'match'                    => MatchField::class,
		'max_length'               => MaxLength::class,
		'min_length'               => MinLength::class,
		'not_in'                   => NotIn::class,
		'regex'                    => Regex::class,
		'required'                 => Required::class,
		'url'                      => URL::class,
	];

	/**
	 * Filters.
	 *
	 * @var array
	 */
	private $filters =
	[
		'boolean'     => FilterBool::class,
		'email'       => FilterEmail::class,
		'float'       => FilterFloat::class,
		'html_decode' => FilterHtmlDecode::class,
		'html_encode' => FilterHtmlEncode::class,
		'integer'     => FilterInteger::class,
		'json'        => FilterJson::class,
		'lowercase'   => FilterLowerCase::class,
		'numeric'     => FilterNumeric::class,
		'string'      => FilterString::class,
		'strip_tags'  => FilterStripTags::class,
		'trim'        => FilterTrim::class,
		'uppercase'   => FilterUpperCase::class,
		'url_decode'  => FilterUrlDecode::class,
		'url_encode'  => FilterUrlEncode::class,
	];

	/**
	 * Original field names.
	 *
	 * @var array
	 */
	private $originalFieldNames;

	/**
	 * Is the input valid?
	 *
	 * @var bool
	 */
	private $isValid = true;

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	private $errors = [];

	/**
	 * Constructor.
	 *
	 * @param array                     $input     Input
	 * @param array                     $ruleSets  Rule sets
	 * @param array                     $filters   Filter sets (optional default [])
	 * @param \serve\ioc\Container|null $container Container (optional) (default null)
	 */
	public function __construct(array $input, array $ruleSets, array $filters = [], Container $container = null)
	{
		$this->input = $input;

		$this->ruleSets = $ruleSets;

		$this->filterSets = $filters;

		$this->container = !$container ? Container::instance() : $container;
	}

	/**
	 * Returns true if all rules passed and false if validation failed.
	 *
	 * @param  array|null &$errors If $errors is provided, then it is filled with all the error messages
	 * @return bool
	 */
	public function isValid(array &$errors = null): bool
	{
		[$isValid, $errors] = $this->process();

		return $isValid === true;
	}

	/**
	 * Returns false if all rules passed and true if validation failed.
	 *
	 * @param  array|null &$errors If $errors is provided, then it is filled with all the error messages
	 * @return bool
	 */
	public function isInvalid(array &$errors = null): bool
	{
		[$isValid, $errors] = $this->process();

		return $isValid === false;
	}

	/**
	 * Returns the validation errors.
	 *
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * Filters the input.
	 *
	 * @return array
	 */
	public function filter(): array
	{
		$input = $this->input;

		foreach ($this->filterSets as $field => $filters)
		{
			foreach ($filters as $filter)
			{
				if (isset($input[$field]))
				{
					$input[$field] = $this->applyFilter($input[$field], $filter);
				}
				else
				{
					$class = $this->filterFactory($filter);

					if ($class->filterWhenUnset() === true)
					{
						$input[$field] = $class->filter('');
					}
				}
			}
		}

		return $input;
	}

	/**
	 * Filters the input.
	 *
	 * @param  mixed  $value Field value
	 * @param  string $name  Filter name to use
	 * @return mixed
	 */
	private function applyFilter($value, string $name)
	{
		$class = $this->filterFactory($name);

		return $class->filter($value);
	}

	/**
	 * Creates a filter instance.
	 *
	 * @param  string                                   $name Rule name
	 * @return \serve\validator\filters\FilterInterface
	 */
	private function filterFactory(string $name): FilterInterface
	{
		$name = $this->getFilterClassName($name);

		if (!$this->container->has($name))
		{
			$this->container->set($name, new $name);
		}

		return $this->container->get($name);
	}

	/**
	 * Returns the filter class name.
	 *
	 * @param  string $name Filter name
	 * @return string
	 */
	private function getFilterClassName(string $name): string
	{
		if(!isset($this->filters[$name]))
		{
			throw new RuntimeException(vsprintf('Call to undefined filter rule [ %s ].', [$name]));
		}

		return $this->filters[$name];
	}

	/**
	 * Saves original field name along with the expanded field name.
	 *
	 * @param array  $fields Expanded field names
	 * @param string $field  Original field name
	 */
	private function saveOriginalFieldNames(array $fields, string $field): void
	{
		foreach($fields as $expanded)
		{
			$this->originalFieldNames[$expanded] = $field;
		}
	}

	/**
	 * Returns the original field name.
	 *
	 * @param  string $field Field name
	 * @return string
	 */
	private function getOriginalFieldName(string $field): string
	{
		return $this->originalFieldNames[$field] ?? $field;
	}

	/**
	 * Parses the rule.
	 *
	 * @param  string $rule Rule
	 * @return object
	 */
	private function parseRule(string $rule)
	{
		[$name, $parameters] = $this->parseRuleParams($rule);

		return (object) compact('name', 'parameters');
	}

	/**
	 * Parses the rule parameters.
	 *
	 * @param  string $rule Rule
	 * @return array
	 */
	private function parseRuleParams(string $rule): array
	{
		if (!Str::contains($rule, '('))
		{
			return [$rule, []];
		}

		$name = Str::getBeforeFirstChar($rule, '(');

		$param = trim(rtrim(Str::getAfterFirstChar($rule, '('), ')'));

		if (Str::contains($param, '[') || Str::contains($param, '{'))
		{
			$_paramArr = json_decode($param);

			if (is_array($_paramArr) && !empty($_paramArr) && json_last_error() == JSON_ERROR_NONE)
			{
				$param = $_paramArr;
			}
		}

		return [$name, [$param]];
	}

	/**
	 * Returns the rule class name.
	 *
	 * @param  string $name Rule name
	 * @return string
	 */
	private function getRuleClassName(string $name): string
	{
		if(!isset($this->rules[$name]))
		{
			throw new RuntimeException(vsprintf('Call to undefined validation rule [ %s ].', [$name]));
		}

		return $this->rules[$name];
	}

	/**
	 * Creates a rule instance.
	 *
	 * @param  string                               $name Rule name
	 * @return \serve\validator\rules\RuleInterface
	 */
	private function ruleFactory(string $name): RuleInterface
	{
		$name = $this->getRuleClassName($name);

		if (!$this->container->has($name))
		{
			$this->container->set($name, function() use ($name)
			{
				return new $name;
			});
		}

		return $this->container->get($name);
	}

	/**
	 * Returns true if the input field is considered empty and false if not.
	 *
	 * @param  mixed $value Value
	 * @return bool
	 */
	private function isInputFieldEmpty($value): bool
	{
		return in_array($value, ['', null, []], true);
	}

	/**
	 * Returns the error message.
	 *
	 * @param  \serve\validator\rules\RuleInterface $rule       Rule
	 * @param  string                               $field      Field name
	 * @param  object                               $parsedRule Parsed rule
	 * @return string
	 */
	private function getErrorMessage(RuleInterface $rule, $field, $parsedRule): string
	{
		$field = $this->getOriginalFieldName($field);

		return $rule->getErrorMessage($field);
	}

	/**
	 * Validates the field using the specified rule.
	 *
	 * @param  string $field Field name
	 * @param  string $rule  Rule
	 * @return bool
	 */
	private function validate(string $field, string $rule): bool
	{
		$parsedRule = $this->parseRule($rule);

		$rule = $this->ruleFactory($parsedRule->name);

		// Just return true if the input field is empty and the rule doesn't validate empty input

		if($this->isInputFieldEmpty($inputValue = Arr::get($this->input, $field)) && $rule->validateWhenEmpty() === false)
		{
			return true;
		}

		// Set parameters if the rule requires it

		if($rule instanceof WithParametersInterface)
		{
			$rule->setParameters($parsedRule->parameters);
		}

		// Validate input

		if($rule->validate($inputValue, $this->input) === false)
		{
			$this->errors[$field] = $this->getErrorMessage($rule, $field, $parsedRule);

			return $this->isValid = false;
		}

		return true;
	}

	/**
	 * Processes all validation rules and returns an array containing
	 * the validation status and potential error messages.
	 *
	 * @return array
	 */
	private function process(): array
	{
		foreach($this->ruleSets as $field => $ruleSet)
		{
			// Ensure that we don't have any duplicated rules for a field

			$ruleSet = array_unique($ruleSet);

			// Validate field and stop as soon as one of the rules fail

			foreach($ruleSet as $rule)
			{
				if($this->validate($field, $rule) === false)
				{
					break;
				}
			}
		}

		return [$this->isValid, $this->errors];
	}
}
