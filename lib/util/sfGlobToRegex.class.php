<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Match globbing patterns against text.
 *
 * if(preg_match(sfGlobToRegex::toRegex("foo.*"), 'foo.bar')) echo "matched\n";
 *
 * sfGlobToRegex implements glob(3) style matching that can be used to match
 * against text, rather than fetching names from a filesystem.
 *
 * based on perl Text::Glob module.
 *
 * @package    Sift
 * @subpackage util
 */
class sfGlobToRegex
{
    /**
     * Tests if given $value matches the glob pattern
     *
     * @param string  $glob    Glob pattern
     * @param string  $value   The input string.
     * @param array   $matches If matches is provided, then it is filled with the results of search. $matches[0] will contain the text that matched the full pattern, $matches[1] will have the text that matched the first captured parenthesized subpattern, and so on.
     * @param integer $flags   preg_match search flags like PREG_OFFSET_CAPTURE
     * @param integer $offset  Alternate place from which to start the search (in bytes).
     * @param boolean $strictLeadingDot
     * @param boolean $strictWildcardSlash
     *
     * @return integer|false Returns 1 if the pattern matches given subject, 0 if it does not, or FALSE if an error occurred.
     * @see preg_match()
     */
    public static function match(
        $glob,
        $value,
        &$matches = array(),
        $flags = 0,
        $offset = 0,
        $strictLeadingDot = true,
        $strictWildcardSlash = true
    ) {
        return preg_match(
            self::toRegex($glob, $strictLeadingDot, $strictWildcardSlash, false),
            $value,
            $matches,
            $flags,
            $offset
        );
    }

    /**
     * Tests if given $value matches the glob pattern
     *
     * @param string  $glob    Glob pattern
     * @param string  $value   The input string.
     * @param array   $matches If matches is provided, then it is filled with the results of search. $matches[0] will contain the text that matched the full pattern, $matches[1] will have the text that matched the first captured parenthesized subpattern, and so on.
     * @param integer $flags   preg_match_all search flags like PREG_OFFSET_CAPTURE
     * @param integer $offset  Alternate place from which to start the search (in bytes).
     * @param boolean $strictLeadingDot
     * @param boolean $strictWildcardSlash
     *
     * @return integer|false Returns 1 if the pattern matches given subject, 0 if it does not, or FALSE if an error occurred.
     * @see preg_match_all()
     */
    public static function matchAll(
        $glob,
        $value,
        &$matches = array(),
        $flags = 0,
        $offset = 0,
        $strictLeadingDot = true,
        $strictWildcardSlash = true
    ) {
        return preg_match_all(
            self::toRegex($glob, $strictLeadingDot, $strictWildcardSlash, false),
            $value,
            $matches,
            $flags,
            $offset
        );
    }

    /**
     * Returns a regexp which is the equivalent of the glob pattern.
     *
     * @param string  $glob The glob pattern
     * @param boolean $strictLeadingDot
     * @param boolean $strictWildcardSlash
     *
     * @return string regex The regexp
     */
    public static function toRegex($glob, $strictLeadingDot = true, $strictWildcardSlash = true, $plain = false)
    {
        $firstByte = true;
        $escaping = false;
        $inCurlies = 0;
        $regex = '';
        $sizeGlob = strlen($glob);
        for ($i = 0; $i < $sizeGlob; $i++) {
            $car = $glob[$i];
            if ($firstByte) {
                if ($strictLeadingDot && '.' !== $car) {
                    $regex .= '(?=[^\.])';
                }
                $firstByte = false;
            }

            if ('/' === $car) {
                $firstByte = true;
            }

            if ('.' === $car || '(' === $car || ')' === $car || '|' === $car || '+' === $car || '^' === $car
                || '$' === $car
            ) {
                $regex .= "\\$car";
            } elseif ('*' === $car) {
                $regex .= $escaping ? '\\*' : ($strictWildcardSlash ? '[^/]*' : '.*');
            } elseif ('?' === $car) {
                $regex .= $escaping ? '\\?' : ($strictWildcardSlash ? '[^/]' : '.');
            } elseif ('{' === $car) {
                $regex .= $escaping ? '\\{' : '(';
                if (!$escaping) {
                    ++$inCurlies;
                }
            } elseif ('}' === $car && $inCurlies) {
                $regex .= $escaping ? '}' : ')';
                if (!$escaping) {
                    --$inCurlies;
                }
            } elseif (',' === $car && $inCurlies) {
                $regex .= $escaping ? ',' : '|';
            } elseif ('\\' === $car) {
                if ($escaping) {
                    $regex .= '\\\\';
                    $escaping = false;
                } else {
                    $escaping = true;
                }
                continue;
            } else {
                $regex .= $car;
            }
            $escaping = false;
        }

        return $plain ? $regex : '#^' . $regex . '$#';
    }

}
