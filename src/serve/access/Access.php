<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\access;

use serve\file\Filesystem;
use serve\http\request\Request;
use serve\http\response\exceptions\ForbiddenException;
use serve\http\response\Response;
use function in_array;

/**
 * Access/Security.
 *
 * @author Joe J. Howard
 */
class Access
{
	/**
	 * Request object.
	 *
	 * @var \serve\http\request\Request
	 */
	private $request;

	/**
	 * Response object.
	 *
	 * @var \serve\http\response\Response
	 */
	private $response;

	/**
	 * Filesystem object.
	 *
	 * @var \serve\file\Filesystem
	 */
	private $filesystem;

	/**
	 * Path to robots.txt.
	 *
	 * @var string
	 */
	private $robotsPath;

	/**
	 * Is ip address blocking enabled ?
	 *
	 * @var bool
	 */
	private $ipBlockEnabled;

	/**
	 * Array of whitelisted ip addresses.
	 *
	 * @var array
	 */
	private $ipWhitelist;

    /**
     * Constructor.
     *
     * @param \serve\http\request\Request   $request     Request object
     * @param \serve\http\response\Response $response    Response object
     * @param \serve\file\Filesystem        $filesystem  Filesystem instancea
     * @param bool                          $blockIps    Should we block all IP address except the whitelist (optional) (default false)
     * @param array                         $ipWhitelist Array of whitelisted ip addresses (optional) (default [])
     */
    public function __construct(Request $request, Response $response, Filesystem $filesystem, $blockIps = false, $ipWhitelist = [])
    {
        $this->request = $request;

        $this->response = $response;

        $this->filesystem = $filesystem;

        $this->robotsPath = $request->environment()->DOCUMENT_ROOT . '/robots.txt';

        $this->ipBlockEnabled = $blockIps;

        $this->ipWhitelist = $ipWhitelist;
    }

	/**
	 * Is ip blocking enabled ?
	 *
	 * @return bool
	 */
	public function ipBlockEnabled(): bool
	{
		return $this->ipBlockEnabled;
	}

	/**
	 * Is ip address allowed.
	 *
	 * @return bool
	 */
	public function isIpAllowed(): bool
	{
		if (empty($this->ipWhitelist))
		{
			return true;
		}

		$ip = $this->request->environment()->REMOTE_ADDR;

		if (!empty($ip))
		{
			return in_array($ip, $this->ipWhitelist);
		}

		return false;
	}

	/**
	 * Block the current request.
	 */
	public function block(): void
	{
		throw new ForbiddenException('Blocked IP Address. The CMS has IP address blocking enabled - blocked ip: "' . $this->request->environment()->REMOTE_ADDR . '" from access.');
	}

	/**
	 * Returns the default robots.txt file contents.
	 *
	 * @return string
	 */
	public function defaultRobotsText(): string
	{
		return "User-agent: *\nDisallow:";
	}

	/**
	 * Returns the block all robots.txt file contents.
	 *
	 * @return string
	 */
	public function blockAllRobotsText(): string
	{
		return "User-agent: *\nDisallow: /";
	}

	/**
	 * Save the robots.txt file.
	 *
	 * @param string $content Content to put into the file
	 */
	public function saveRobots(string $content = ''): void
	{
		$this->filesystem->putContents($this->robotsPath, $content);
	}

	/**
	 * Save the robots.txt file.
	 */
	public function deleteRobots(): void
	{
		if ($this->filesystem->exists($this->robotsPath))
		{
			$this->filesystem->delete($this->robotsPath);
		}
	}
}
