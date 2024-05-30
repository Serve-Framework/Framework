<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace serve\utility;

use serve\utility\markdown\Parsedown;
use serve\utility\markdown\ParsedownExtra;

use function preg_replace;
use function strip_tags;
use function trim;

/**
 * Convert markdown to HTML.
 *
 * @author Joe J. Howard
 */
class Markdown
{
    /**
     * Convert markdown to HTML.
     *
     * @see    https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet
     * @param  string $text  Text in markdown
     * @param  bool   $extra Convert with markdown extra
     * @return string
     */
    public static function convert(string $text, bool $extra = true): string
    {
        $parser = $extra ? new ParsedownExtra : new Parsedown;

        return $parser->text($text);
    }

    /**
     * Converts markdown to plain text.
     *
     * @param  string $str The input string
     * @return string
     */
    public static function plainText(string $str): string
    {
        return trim(preg_replace('/[\r\n]+/', ' ', strip_tags(self::convert(trim($str)))));
    }
}
