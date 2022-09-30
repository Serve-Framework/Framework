<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\http\request;

use serve\common\MagicObjectArray;

use function array_pop;
use function dirname;
use function explode;
use function intval;
use function ltrim;
use function microtime;
use function rtrim;
use function str_replace;
use function strpos;
use function strrpos;
use function substr;
use function time;
use function trim;

/**
 * Environment class.
 *
 * @author Joe J. Howard
 *
 * @property string $REQUEST_METHOD
 * @property string $SCRIPT_NAME
 * @property string $SERVER_NAME
 * @property string $SERVER_PORT
 * @property string $HTTP_PROTOCOL
 * @property string $DOCUMENT_ROOT
 * @property string $HTTP_HOST
 * @property string $DOMAIN_NAME
 * @property string $REQUEST_URI
 * @property string $REQUEST_PATH
 * @property string $REQUEST_URL
 * @property string $QUERY_STRING
 * @property string $REMOTE_ADDR
 * @property string $REFERER
 * @property string $HTTP_USER_AGENT
 * @property string $REQUEST_TIME
 * @property string $REQUEST_TIME_FLOAT
 */
class Environment extends MagicObjectArray
{
    /**
     * $_SERVER.
     *
     * @var array
     */
    private $server;

    /**
     * Constructor. Loads the properties internally.
     *
     * @param array $server Optional server overrides (optional) (default [])
     */
    public function __construct(array $server = [])
    {
        $this->server = empty($server) ? $_SERVER : $server;

        $this->data = $this->extract();
    }

    /**
     * Reload the environment properties.
     *
     * @param array $server Optional server overrides (optional) (default [])
     */
    public function reload(array $server  = []): void
    {
        $this->server = empty($server) ? $_SERVER : $server;

        $this->data = $this->extract();
    }

    /**
     * Returns a fresh copy of the environment properties.
     *
     * @return array
     */
    private function extract(): array
    {
        return
        [
            'REQUEST_METHOD'     => $this->requestMethod(),
            'SCRIPT_NAME'        => $this->scriptName(),
            'SERVER_NAME'        => $this->serverName(),
            'SERVER_PORT'        => $this->serverPort(),
            'HTTP_PROTOCOL'      => $this->httpProtocol(),
            'DOCUMENT_ROOT'      => $this->documentRoot(),
            'HTTP_HOST'          => $this->httpHost(),
            'DOMAIN_NAME'        => $this->domainName(),
            'REQUEST_URI'        => $this->requestUri(),
            'REQUEST_PATH'       => $this->requestPath(),
            'REQUEST_URL'        => $this->requestUrl(),
            'QUERY_STRING'       => $this->queryString(),
            'REMOTE_ADDR'        => $this->remoteAddr(),
            'REFERER'            => $this->referer(),
            'HTTP_USER_AGENT'    => $this->httpUserAgent(),
            'REQUEST_TIME'       => $this->requestTime(),
            'REQUEST_TIME_FLOAT' => $this->requestTimeFloat(),
        ];
    }

    /**
     * Returns the REQUEST_METHOD.
     *
     * @return string
     */
    private function requestMethod(): string
    {
        return !isset($this->server['REQUEST_METHOD']) ? 'CLI' : $this->server['REQUEST_METHOD'];
    }

    /**
     * Returns the SCRIPT_NAME.
     *
     * @return string
     */
    private function scriptName(): string
    {
        if (isset($this->server['SCRIPT_NAME']) && !empty($this->server['SCRIPT_NAME']))
        {
            $scripts = explode('/', trim($this->server['SCRIPT_NAME'], '/'));
        }
        elseif (isset($this->server['PHP_SELF']) && !empty($this->server['PHP_SELF']))
        {
            $scripts = explode('/', trim(substr($this->server['PHP_SELF'], strrpos($this->server['PHP_SELF'], '/') + 1), '/'));
        }
        else
        {
            return '/index.php';
        }

        return '/' . array_pop($scripts);
    }

    /**
     * Returns the SERVER_NAME.
     *
     * @return string
     */
    private function serverName(): string
    {
        if (isset($this->server['SERVER_NAME']))
        {
            return $this->server['SERVER_NAME'];
        }
        elseif (isset($this->server['HTTP_HOST']))
        {
            if (strpos($this->server['HTTP_HOST'], ':') !== false)
            {
                $name = explode(':', $this->server['HTTP_HOST']);

                return trim($name[0]);
            }
            elseif (strpos($this->server['HTTP_HOST'], '.') !== false)
            {
                $name = explode('.', $this->server['HTTP_HOST']);

                return trim($name[0]);
            }
        }

        return 'UNKNOWN';
    }

    /**
     * Returns the SERVER_PORT.
     *
     * @return int
     */
    private function serverPort(): int
    {
        return isset($this->server['SERVER_PORT']) ? intval($this->server['SERVER_PORT']) : 80;
    }

    /**
     * Returns the HTTP_PROTOCOL.
     *
     * @return string
     */
    private function httpProtocol(): string
    {
        if (isset($this->server['SERVER_PORT']) && $this->server['SERVER_PORT'] === 443)
        {
            return 'https';
        }
        elseif (isset($this->server['HTTPS']) && ($this->server['HTTPS'] === 1 || $this->server['HTTPS'] === 'on'))
        {
            return 'https';
        }

        return 'http';
    }

    /**
     * Returns the DOCUMENT_ROOT.
     *
     * @return string
     */
    private function documentRoot(): string
    {
        return isset($this->server['DOCUMENT_ROOT']) ? $this->server['DOCUMENT_ROOT'] : dirname(__FILE__, 5);
    }

    /**
     * Returns the HTTP_HOST.
     *
     * @return string
     */
    private function httpHost(): string
    {
        if (isset($this->server['HTTP_HOST']))
        {
            return $this->httpProtocol() . '://' . str_replace(['http://', 'https://'], ['', ''], $this->server['HTTP_HOST']);
        }

        return '';
    }

    /**
     * Returns the DOMAIN_NAME.
     *
     * @return string
     */
    private function domainName(): string
    {
        return str_replace('www.', '', str_replace($this->httpProtocol() . '://', '', $this->httpHost()));
    }

    /**
     * Returns the REQUEST_URI.
     *
     * @return string
     */
    private function requestUri(): string
    {
        return isset($this->server['REQUEST_URI']) ? $this->server['REQUEST_URI'] : '/';
    }

    /**
     * Returns the REQUEST_URI without the query string.
     *
     * @return string
     */
    private function requestPath(): string
    {
        $uri = $this->requestUri();

        if (strpos($uri, '?') !== false)
        {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        return ltrim(rtrim($uri, '/'), '/');
    }

    /**
     * Returns the REQUEST_URI.
     *
     * @return string
     */
    private function requestUrl(): string
    {
        return $this->httpHost() . $this->requestUri();
    }

    /**
     * Returns the QUERY_STRING.
     *
     * @return string
     */
    private function queryString(): string
    {
        $uri = $this->requestUri();

        return strpos($uri, '?') !== false ? substr($uri, strrpos($uri, '?') + 1) : '';
    }

    /**
     * Returns the REMOTE_ADDR.
     *
     * @return string
     */
    private function remoteAddr(): string
    {
        if (isset($this->server['HTTP_CLIENT_IP']))
        {
            $ipaddress = $this->server['HTTP_CLIENT_IP'];
        }
        elseif (isset($this->server['HTTP_X_FORWARDED_FOR']))
        {
            $ipaddress = $this->server['HTTP_X_FORWARDED_FOR'];
        }
        elseif (isset($this->server['HTTP_X_FORWARDED']))
        {
            $ipaddress = $this->server['HTTP_X_FORWARDED'];
        }
        elseif (isset($this->server['HTTP_FORWARDED_FOR']))
        {
            $ipaddress = $this->server['HTTP_FORWARDED_FOR'];
        }
        elseif (isset($this->server['HTTP_FORWARDED']))
        {
            $ipaddress = $this->server['HTTP_FORWARDED'];
        }
        elseif (isset($this->server['REMOTE_ADDR']))
        {
            $ipaddress = $this->server['REMOTE_ADDR'];
        }
        else
        {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    /**
     * Returns the HTTP_REFERER.
     *
     * @return string
     */
    private function referer(): string
    {
        return isset($this->server['HTTP_REFERER']) ? $this->server['HTTP_REFERER'] : '';
    }

    /**
     * Returns the HTTP_USER_AGENT.
     *
     * @return string
     */
    private function httpUserAgent()
    {
        return isset($this->server['HTTP_USER_AGENT']) ? $this->server['HTTP_USER_AGENT'] : '';
    }

    /**
     * Returns the REQUEST_TIME.
     *
     * @return int
     */
    private function requestTime(): int
    {
       return isset($this->server['REQUEST_TIME']) ? $this->server['REQUEST_TIME'] : time();
    }

    /**
     * Returns the REQUEST_TIME_FLOAT.
     *
     * @return float
     */
    private function requestTimeFloat(): float
    {
        return isset($this->server['REQUEST_TIME_FLOAT']) ? $this->server['REQUEST_TIME_FLOAT'] : microtime(true);
    }
}
