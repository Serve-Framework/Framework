<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\graphql\query;

use serve\graphql\connection\ConnectionHandler;
use serve\graphql\exception\SyntaxException;

use function count;
use function is_array;
use function is_int;
use function is_null;
use function is_string;
use function key;
use function str_repeat;
use function trim;

/**
 * Graphql query builder.
 *
 * @author Joe J. Howard
 */
class Builder
{
    /**
     * Query fields.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Current query fields to operate on.
     *
     * @var array
     */
    protected $currField;

    /**
     * Connection handler instance.
     *
     * @var \serve\graphql\connection\ConnectionHandler
     */
    protected $connectionHandler;

    /**
     * Constructor.
     *
     * @param \serve\graphql\connection\ConnectionHandler $connectionHandler connection handler instance
     */
    public function __construct(ConnectionHandler $connectionHandler)
    {
        $this->connectionHandler = $connectionHandler;
    }

    /**
     * Allows to add query properties/functions by key name.
     *
     * @example $query->images(['first' => 200])
     *
     * @param  string                       $function Field name
     * @param  array|null                   $args     Function args (optional)
     * @return \serve\graphql\query\Builder
     */
    public function __call(string $function, ?array $args = null): Builder
    {
        $this->applyFunctionField($function, $args);

        return $this;
    }

    /**
     * Adds next function as an alias.
     *
     * @param  string $name Alias name
     * @return \serve\graphql\query\Builder
     */
    public function alias(string $name): Builder
    {
        $this->applyFunctionField($name . ':');

        return $this;
    }

    /**
     * Executes the query and returns the resulting json array.
     *
     * @return array
     */
    public function exec(): array
    {
        $queryStr = trim($this->stringifyFields($this->fields));

        $response = $this->connectionHandler->query($queryStr);

        if (!$response->isSuccessful())
        {
            return [null];
        }

        $body = $response->body();

        return isset($body['data']) ? $body['data'] : $body;
    }

    /**
     * Resets internal pointer to root.
     *
     * @return \serve\graphql\query\Builder
     */
    public function root(): Builder
    {
        $this->currField = null;

        return $this;
    }

    /**
     * Adds fields to current query pointer.
     *
     * @param  array                                    $fields Fields to add
     * @throws \serve\graphql\exception\SyntaxException
     * @return \serve\graphql\query\Builder
     */
    public function fields(array $fields): Builder
    {
        if (empty($this->fields) || is_null($this->currField))
        {
            throw new SyntaxException('Cannot add fields when query is empty.');
        }

        $lastField =& $this->currField;

        foreach($fields as $key => $field)
        {
            [$key, $field] = $this->normaliseField($key, $field);

            $this->currField[$key] = $field;
        }

        $this->currField =& $lastField;

        return $this;
    }

    /**
     * Normalises key/fields.
     *
     * @param  int|string   $key Fields key
     * @return array|string $key Field value
     * @return array
     */
    protected function normaliseField(string|int $key, string|array $field): array
    {
        if (is_int($key))
        {
            $key = count($this->currField);
        }
        elseif (is_array($field))
        {
            $first = key($field);

            if (is_string($first) && $first[0] === '(')
            {
                $key .= $first;

                $field = $field[$first];
            }
        }

        return [$key, $field];
    }

    /**
     * Applies query function / name field to stack.
     *
     * @param string     $function Field name
     * @param array|null $args     Function args (optional)
     */
    protected function applyFunctionField(string $function, ?array $args = null): void
    {
        $function = is_array($args) && !empty($args) ? $function . $this->normaliseFuncArgs($args[0]) : $function;

        if (empty($this->fields))
        {
            $this->fields[$function] = [];

            $this->currField =& $this->fields[$function];
        }
        else
        {
            $this->currField[$function] = [];

            $this->currField =& $this->currField[$function];
        }
    }

    /**
     * Normalises function args.
     *
     * @param  array|string $args Field args
     * @return string
     */
    protected function normaliseFuncArgs(string|array $args): string
    {
        if (is_array($args))
        {
            $func = '(';

            foreach($args as $k => $v)
            {
                $v = is_string($v) ? '"' . $v . '"' : $v;

                $func .= $k . ': ' . $v . ', ';
            }

            $func = rtrim($func, ', ');

            $func .= ')';

            return $func;
        }
        else
        {
            return '("' . $args . '")';
        }
    }

    /**
     * Stringify fields to query (recursive).
     *
     * @param array $fields Fields
     * @param int   $depth  Current depth
     */
    protected function stringifyFields(array $fields, int $depth = 1): string
    {
        $ret = '';

        $brk = "\n";

        $tab = '    ';

        foreach($fields as $key => $value)
        {
            $isAlias = is_string($key) && substr($key, -1) === ':';

            if (is_array($value))
            {
                if (is_string($key))
                {
                    $isAlias ? $ret .= str_repeat($tab, $depth) . $key . $brk : $ret .= str_repeat($tab, $depth) . $key . ' {' . $brk;
                }
                else
                {
                    $ret .= str_repeat($tab, $depth) . ' {' . $brk;
                }

                $ret .= $this->stringifyFields($value, ++$depth);

                $isAlias ? $ret .= str_repeat($tab, --$depth) . $brk : $ret .= str_repeat($tab, --$depth) . '} ' . $brk;

            }
            else
            {
                $ret .= str_repeat($tab, $depth) . $value . $brk;
            }
        }

        return $ret;
    }
}
