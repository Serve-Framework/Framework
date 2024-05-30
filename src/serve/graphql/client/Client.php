<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\graphql\client;

use Exception;
use serve\utility\Arr;
use serve\utility\Str;

use function array_merge;
use function file_get_contents;
use function in_array;
use function is_int;
use function is_string;
use function json_encode;
use function mb_strlen;
use function stream_context_create;
use function trim;
use function usleep;

/**
 * Graphql client.
 *
 * @author Joe J. Howard
 */
class Client
{
	/**
	 * Request method constants.
	 *
	 * @var string
	 */
	public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_DELETE = 'DELETE';

    /**
     * Content type constants.
     *
     * @var string
     */
    public const DATA_TYPE_JSON = 'application/json';
    public const DATA_TYPE_GRAPHQL = 'application/graphql';

    /**
     * Retryable status codes.
     *
     * @var array
     */
    protected const RETRIABLE_STATUS_CODES = [429, 500];

    /**
     * Authentification headers.
     *
     * @var array|null
     */
    protected $authHeaders;

    /**
     * Endpoint url.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Constructor.
     *
     * @param string     $domain      The domain to make requests to
     * @param string     $endpoint    The URL path to request
     * @param array|null $authHeaders Extra authentification headers
     */
    public function __construct(string $domain, string $endpoint, ?array $authHeaders = null)
    {
        $this->endpoint = 'https://' . trim($domain, '/') . '/' . trim($endpoint, '/');

        $this->authHeaders = !$authHeaders ? null : $this->normaliseHeaders($authHeaders);
    }

    /**
     * Makes a request.
     *
     * @param  array|string|null              $body     The request body to send
     * @param  array                          $params   Parameters on a query to be added to the URL
     * @param  array                          $headers  Any extra headers to send along with the request
     * @param  int|null                       $tries    How many times to attempt the request
     * @param  string                         $dataType The data type of the request
     * @return \serve\graphql\client\Response
     */
    public function post($body = null, array $params = [], array $headers = [], ?int $tries = null, string $dataType = self::DATA_TYPE_GRAPHQL): Response
    {
        return $this->request(self::METHOD_POST, $body, $params, $headers, $tries, $dataType);
    }

    /**
     * Makes a request.
     *
     * @param  string                         $method   The method to use
     * @param  array|string|null              $body     The request body to send
     * @param  array                          $params   Parameters on a query to be added to the URL
     * @param  array                          $headers  Any extra headers to send along with the request
     * @param  int|null                       $tries    How many times to attempt the request
     * @param  string                         $dataType The data type of the request
     * @return \serve\graphql\client\Response
     */
    public function request(string $method, $body = null, array $params = [], array $headers = [], ?int $tries = null, string $dataType = self::DATA_TYPE_GRAPHQL): Response
    {
    	$maxTries = !$tries ? 1 : $tries;

        $headers = $this->normaliseHeaders($headers);

        if (!empty($this->authHeaders))
        {
            $headers = array_merge($headers, $this->authHeaders);
        }

    	if ($body)
    	{
            if (is_string($body))
            {
                $bodyString = $body;

                $dataType = self::DATA_TYPE_GRAPHQL;
            }
            else
            {
                $dataType = self::DATA_TYPE_JSON;

                $bodyString = json_encode(['query' => $body]);
            }

            $headers['Content-length'] = mb_strlen($bodyString);
        }

        $headers['Content-Type'] = $dataType;

    	$headersStr = Arr::implodeMulti("\r\n", ': ', $headers);

        $options =
        [
  			'http' =>
  			[
    			'header'  => $headersStr,
    			'method'  => $method,
    			'content' => $bodyString,
  			],
		];

        $currentTries = 0;

        do
        {
            $currentTries++;

            try
            {
                $context = stream_context_create($options);

                $body = file_get_contents($this->endpoint, false, $context);

                $response = new Response($http_response_header, $body);
            }
            catch(Exception $e)
            {
                $response = new Response($http_response_header, $body);
            }

            if (in_array($response->code(), self::RETRIABLE_STATUS_CODES))
            {
                $retryAfter = $response->headers('RETRY_AFTER');

                if ($retryAfter)
                {
                    usleep((int) ($retryAfter * 1000000));
                }
            }
            else
            {
                break;
            }

        } while ($currentTries < $maxTries);

        return $response;
    }

    /**
     * Normalizes headers to key / val array.
     *
     * @param  array $headers Headers
     * @return array
     */
    protected function normaliseHeaders(array $headers): array
    {
    	$ret = [];

    	foreach($headers as $key => $val)
    	{
    		if (is_int($key))
    		{
    			$ret[trim(Str::getBeforeFirstChar($val, ':'))] = trim(Str::getAfterFirstChar($val, ':'));
    		}
    		else
    		{
    			$ret[$key] = trim($val);
    		}
    	}

    	return $ret;
    }
}
