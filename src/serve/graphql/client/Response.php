<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\graphql\client;

use serve\utility\Str;

use function array_key_exists;
use function array_map;
use function explode;
use function in_array;
use function intval;
use function is_array;
use function is_numeric;
use function is_string;
use function json_decode;
use function preg_match;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function urldecode;

/**
 * Graphql response object.
 *
 * @author Joe J. Howard
 */
class Response
{
    /**
     * Normalized headers.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Response code.
     *
     * @var int
     */
    protected $status = 404;

    /**
     * Response code.
     *
     * @var int
     */
    protected $message = 'Not Found';

    /**
     * Errors.
     *
     * @var string
     */
    protected $errors = [];

    /**
     * Response body.
     *
     * @var string
     */
    protected $body = '';

    /**
     * Constructor.
     *
     * @param array|null  $headers Response headers
     * @param string|null $body    Response body
     */
    public function __construct(?array $headers = null, ?string $body = null)
    {
        if ($headers)
        {
            $this->extractHeaders($headers);
        }

        if ($body)
        {
            $this->extractBody($body);
        }
    }

    /**
     * HTTP Messaage.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * Returns response code.
     *
     * @return int
     */
    public function code(): int
    {
        return $this->status;
    }

    /**
     * Returns parsed response body.
     *
     * @return array
     */
    public function body(): array
    {
        return $this->body;
    }

    /**
     * Returns errors.
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Is the response not modified.
     *
     * @return bool
     */
    public function isNotModified(): bool
    {
        return $this->status === 304;
    }

    /**
     * Is the response empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->status === 204;
    }

    /**
     * Is this an informational response.
     *
     * @return bool
     */
    public function isInformational(): bool
    {
        return $this->status >= 100 && $this->status < 200;
    }

    /**
     * Is the response ok 200?
     *
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->status === 200;
    }

    /**
     * Is the response successful ?
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    /**
     * Is the response a redirect ?
     *
     * @return bool
     */
    public function isRedirect(): bool
    {
        return in_array($this->status, [301, 302, 303, 307]);
    }

    /**
     * Is the response forbidden ?
     *
     * @return bool
     */
    public function isForbidden(): bool
    {
        return $this->status === 403;
    }

    /**
     * Is the response 404 not found ?
     *
     * @return bool
     */
    public function isNotFound(): bool
    {
        return $this->status === 404;
    }

    /**
     * Is this a client error ?
     *
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->status >= 400 && $this->status < 500;
    }

    /**
     * Is this a server error ?
     *
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->status >= 500 && $this->status < 600;
    }

    /**
     * Returns headers or header by key.
     *
     * @param  string|null $key Optional key
     * @return mixed
     */
    public function headers(?string $key = null)
    {
        if ($key)
        {
            if (array_key_exists($key, $this->headers))
            {
                return $this->headers[$key];
            }

            return false;
        }

        return $this->headers;
    }

    /**
     * Extracts body and changes response codes if an error was found.
     *
     * @param string|null $body Response body
     */
    protected function extractBody(?string $body = null): void
    {
        if ($body)
        {
            $response = json_decode($body, true);

            $this->extractResponseErrors($response);

            $this->body = $response;
        }
        else
        {
            $this->body = [null];
        }
    }

    /**
     * Extracts errors from JSON response array.
     *
     * @param array $response Parsed response
     */
    protected function extractResponseErrors(array $response): void
    {
        $errors = [];

        // Common response for queries / functions
        if (isset($response['errors']))
        {
            $errors = $response['errors'];
        }
        // Common response for single query / function
        elseif (isset($response['error']))
        {
            $errors = $response['error'];
        }
        // Check for errors as keys or nested 2 levels deep in data
        else
        {
            $response = isset($response['data']) ? $response['data'] : $response;

            // Check 2 levels deep for errors
            foreach($response as $key => $value)
            {
                if (is_array($value))
                {
                    foreach($value as $_key => $_value)
                    {
                        if (is_string($_key) && str_contains(strtolower($_key), 'error'))
                        {
                            $errors[$_key] = $_value;
                        }
                    }
                }
                // Flat array
                if (is_string($key) && str_contains(strtolower($key), 'error'))
                {
                    $errors[] = $array[$key];
                }
            }
        }

        // Errors were found
        if (!empty($errors))
        {
            $this->errors = $errors;

            // Only change the status if it was not set "properly"
            // with the response header
            if ($this->status === 200)
            {
                $this->status = 400;

                $this->message = 'Bad Request';
            }
        }
    }

    /**
     * Extract and normalise response headers.
     *
     * @param array $headers Response headers
     */
    protected function extractHeaders(array $headers): void
    {
        $results = [];

        // Loop through the $_SERVER superglobal and save result consistently
        foreach ($headers as $headerStr)
        {
            $results[$this->extractHeaderKey($headerStr)] = $this->extractHeaderValue($headerStr);
        }

        $this->headers = $results;
    }

    /**
     * Extract header key from header string.
     *
     * @param  string $headerStr Header
     * @return string
     */
    protected function extractHeaderKey(string $headerStr): string
    {
        $name = trim(strtoupper(str_replace('-', '_', Str::getBeforeFirstChar($headerStr, ':'))));

        if (str_starts_with($name, 'HTTP/'))
        {
            return 'HTTP/1.1';
        }

        return $name;
    }

    /**
     * Extract header value from header string.
     *
     * @param  string           $headerStr Header
     * @return array|int|string
     */
    protected function extractHeaderValue(string $headerStr)
    {
        if (str_starts_with(strtoupper($headerStr), 'HTTP/'))
        {
            return $this->extractStatusHeader($headerStr);
        }

        $header = urldecode(trim(Str::getAfterFirstChar($headerStr, ':')));

        // Json
        if (!empty($header) && $header[0] === '{' && substr($header, -1) === '}')
        {
            return json_decode($header, true);
        }
        // split headers
        elseif (str_contains($header, ';'))
        {
            $headers = array_map('trim', explode(';', $header));

            foreach($headers as $i => $value)
            {
                if (str_contains($value, '='))
                {
                    $headers[$i] = array_map('trim', explode('=', $value));
                }
                elseif (is_numeric($value))
                {
                    $headers[$i] = intval($value);
                }
            }

            return $headers;
        }

        return $header;
    }

    /**
     * Excracts the status header.
     *
     * @param  string $headerStr HTTP header
     * @return array
     */
    protected function extractStatusHeader(string $headerStr): array
    {
        // (http) (code) (message)
        preg_match("/(http\/[0-9\.]+\s+)(\d+)(\s+.+)/i", $headerStr, $matches);

        $this->status = intval($matches[2]);

        $this->message = trim($matches[3]);

        return [$this->status, $this->message];
    }
}
