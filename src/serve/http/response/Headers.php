<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/Serve-Framework/Framework/blob/master/LICENSE
 */

namespace serve\http\response;

use serve\common\MagicObjectArray;
use serve\utility\Str;
use function header;
use function is_array;
use function reset;

/**
 * Response headers.
 *
 * @author Joe J. Howard
 */
class Headers extends MagicObjectArray
{
    /**
     * Have the headers been sent?
     *
     * @var bool
     */
    private $sent = false;

    /**
     * Send the headers.
     */
    public function send(): void
    {
        if (!$this->sent())
        {
            foreach ($this->get() as $name => $value)
            {
                $value = is_array($value) ? reset($value) : $value;

                if (Str::contains($name, 'HTTP'))
                {
                    header($name . '/1.1 ' . $value, true);
                }
                else
                {
                    header($name . ':' . $value, true);
                }
            }

            $this->sent = true;
        }
    }

    /**
     * Are the headers sent ?
     */
    public function sent(): bool
    {
        return $this->sent;
    }
}
