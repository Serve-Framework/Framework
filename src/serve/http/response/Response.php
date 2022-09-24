<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\http\response;

use serve\http\cookie\Cookie;
use serve\http\request\Request;
use serve\http\response\exceptions\ForbiddenException;
use serve\http\response\exceptions\InvalidTokenException;
use serve\http\response\exceptions\MethodNotAllowedException;
use serve\http\response\exceptions\NotFoundException;
use serve\http\response\exceptions\Stop;
use serve\http\session\Session;
use serve\mvc\view\View;
use function hash;
use function in_array;
use function is_string;
use function strpos;
use function strtoupper;

/**
 * HTTP Response manager.
 *
 * @author Joe J. Howard
 */
class Response
{
    /**
     * The HTTP protocol.
     *
     * @var \serve\http\response\Protocol
     */
    private $protocol;

    /**
     * The HTTP format.
     *
     * @var \serve\http\response\Format
     */
    private $format;

    /**
     * The HTTP headers.
     *
     * @var \serve\http\response\Headers
     */
    private $headers;

    /**
     * Cookie manager.
     *
     * @var \serve\http\cookie\Cookie
     */
    private $cookie;

    /**
     * Response body.
     *
     * @var \serve\http\response\Body
     */
    private $body;

    /**
     * The HTTP protocol.
     *
     * @var \serve\http\session\Session
     */
    private $session;

    /**
     * Response status.
     *
     * @var \serve\http\response\Status
     */
    private $status;

    /**
     * CDN manager.
     *
     * @var \serve\http\response\CDN
     */
    private $CDN;

    /**
     * View renderer.
     *
     * @var \serve\mvc\view\View
     */
    private $view;

    /**
     * The HTTP request.
     *
     * @var \serve\http\request\Request
     */
    private $request;

    /**
     * Has the response been sent ?
     *
     * @var bool
     */
    private $sent = false;

    /**
     * Enable response cache?
     *
     * @var bool
     */
    private $responseCache = false;

    /**
     * Enable response cache?
     *
     * @var int
     */
    private $maxCacheAge;

    /**
     * Constructor.
     *
     * @param \serve\http\response\Protocol $protocol
     * @param \serve\http\response\Format   $format
     * @param \serve\http\response\Body     $body
     * @param \serve\http\response\Status   $status
     * @param \serve\http\response\Headers  $headers
     * @param \serve\http\session\Session   $session
     * @param \serve\http\response\CDN      $CDN
     * @param \serve\mvc\view\View          $view
     * @param \serve\http\request\Request   $request
     * @param bool                          $responseCache
     * @param int                           $maxCacheAge   Max cache age in seconds (optional) (default 3600)
     */
    public function __construct(Protocol $protocol, Format $format, Body $body, Status $status, Headers $headers, Cookie $cookie, Session $session, CDN $CDN, View $view, Request $request, bool $responseCache, int $maxCacheAge = 3600)
    {
        $this->format = $format;

        $this->body = $body;

        $this->status = $status;

        $this->headers = $headers;

        $this->cookie = $cookie;

        $this->session = $session;

        $this->protocol = $protocol;

        $this->CDN = $CDN;

        $this->view = $view;

        $this->request = $request;

        $this->format->set('text/html');

        $this->format->setEncoding('utf-8');

        $this->responseCache = $responseCache;

        $this->maxCacheAge = $maxCacheAge;
    }

    /**
     * Get the protocol object.
     *
     * @return \serve\http\response\Protocol
     */
    public function protocol(): Protocol
    {
        return $this->protocol;
    }

    /**
     * Get the body object.
     *
     * @return \serve\http\response\Body
     */
    public function body(): Body
    {
        return $this->body;
    }

    /**
     * Get the format object.
     *
     * @return \serve\http\response\Format
     */
    public function format(): Format
    {
        return $this->format;
    }

    /**
     * Get the status object.
     *
     * @return \serve\http\response\Status
     */
    public function status(): Status
    {
        return $this->status;
    }

    /**
     * Get the headers object.
     *
     * @return \serve\http\response\Headers
     */
    public function headers(): Headers
    {
        return $this->headers;
    }

    /**
     * Get the cookie manager.
     *
     * @return \serve\http\cookie\Cookie
     */
    public function cookie(): Cookie
    {
        return $this->cookie;
    }

    /**
     * Get the session manager.
     *
     * @return \serve\http\session\Session
     */
    public function session(): Session
    {
        return $this->session;
    }

    /**
     * Get the CDN object.
     *
     * @return \serve\http\response\CDN
     */
    public function CDN(): CDN
    {
        return $this->CDN;
    }

    /**
     * Get the view object.
     *
     * @return \serve\mvc\view\View
     */
    public function view(): View
    {
        return $this->view;
    }

    /**
     * Enables ETag response cache.
     */
    public function enableCaching(): void
    {
        $this->responseCache = true;
    }

    /**
     * Disables ETag response cache.
     */
    public function disableCaching(): void
    {
        $this->responseCache = false;
    }

    /**
     * Is the response cacheable?
     *
     * @return bool
     */
    public function isCacheable(): bool
    {
        if ($this->responseCache === false)
        {
            return false;
        }

        if (in_array($this->status->get(), [200, 203, 204, 206, 300, 301, 404, 405, 410, 414, 501]) === false)
        {
            return false;
        }

        if (in_array(strtoupper($this->request->getMethod()), ['GET', 'HEAD']) === false)
        {
            return false;
        }

        $cacheControl = $this->headers->get('Cache-Control');

        if (is_string($cacheControl))
        {
            if (strpos($cacheControl, 'no-store') !== false || strpos($cacheControl, 'no-cache') !== false)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Finalize all objects before sending the response.
     */
    public function finalize(): void
    {
        $this->finalizeBody();

        $this->finalizeHeaders();
    }

    /**
     * Finalize the response headers/.
     */
    private function finalizeHeaders(): void
    {
        $this->headers->set('Content-Type', $this->format->get() . ';' . $this->format->getEncoding());

        $this->headers->set('Content-length', $this->body->length());

        // Cache-Control header
        if ($this->isCacheable())
        {
            // ETag header and conditional GET check
            $hash = '"' . hash('sha256', $this->body->get()) . '"';

            $this->headers->set('Cache-Control', 'private, max-age=' . $this->maxCacheAge);

            $this->headers->set('ETag', $hash);

            if ($this->request->headers()->HTTP_IF_NONE_MATCH === $hash)
            {
                $this->status->set(304);
            }
        }
        else
        {
            $this->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        $this->headers->set('HTTP', $this->status->get() . ' ' . $this->status->message());

        // No body to send
        if ($this->status->isRedirect() || $this->status->isEmpty() || $this->status->isNotModified())
        {
            $this->headers->remove('Content-Type');

            $this->headers->remove('Content-Length');

            $this->body->clear();
        }
    }

    /**
     * Finalize the body from the cache and CDN.
     */
    private function finalizeBody(): void
    {
        $this->body->set($this->CDN->filter($this->body->get()));
    }

    /**
     * Send the HTTP response.
     */
    public function send(): void
    {
        if (!$this->sent)
        {
            $this->finalize();

            $this->session->save();

            $this->headers->send();

            $this->cookie->send();

            if ($this->request->getMethod() !== 'HEAD')
            {
                echo $this->body->get();
            }
        }

        $this->sent = true;
    }

    /**
     * Immediately send a redirect response.
     *
     * @param  string                               $url    The absolute URL to redirect to
     * @param  int                                  $status The redirect status (optional) (default 302)
     * @throws \serve\http\response\exceptions\Stop
     */
    public function redirect(string $url, int $status = 302): void
    {
        $this->responseCache = false;

        $this->status->set(302);

        $this->headers->set('Location', $url);

        $this->body->clear();

        $this->send();

        throw new Stop;
    }

    /**
     * Send a not found response.
     *
     * @throws \serve\http\response\exceptions\NotFoundException
     */
    public function notFound(): void
    {
        throw new NotFoundException();
    }

    /**
     * Send a forbidden response.
     *
     * @throws \serve\http\response\exceptions\ForbiddenException
     */
    public function forbidden(): void
    {
        throw new ForbiddenException();
    }

    /**
     * Send a invalid token response.
     *
     * @throws \serve\http\response\exceptions\InvalidTokenException
     */
    public function invalidToken(): void
    {
        throw new InvalidTokenException();
    }

    /**
     * Send a invalid token response.
     *
     * @throws \serve\http\response\exceptions\MethodNotAllowedException
     */
    public function methodNotAllowed(): void
    {
        throw new MethodNotAllowedException();
    }
}
