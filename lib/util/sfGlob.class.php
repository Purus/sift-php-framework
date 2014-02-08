<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfGlob is a utility class for searching for files and/or directories like glob().
 * It can search for files and/or directories with pattern longer than 260 characters.
 *
 * Code based on the glob() PHP_Compat function.
 *
 * @package    Sift
 * @subpackage util
 */
class sfGlob
{
    /**
     * Find pathnames matching a pattern
     *
     * @param string  $pattern       The pattern. No tilde expansion or parameter substitution is done.
     * @param integer $flags         The flags. Valid flags are:
     *
     *  * GLOB_MARK - Adds a slash to each directory returned
     *  * GLOB_NOSORT - Return files as they appear in the directory (no sorting)
     *  * GLOB_NOCHECK - Return the search pattern if no files matching it were found
     *  * GLOB_NOESCAPE - Backslashes do not quote metacharacters
     *  * GLOB_BRACE - Expands {a,b,c} to match 'a', 'b', or 'c'
     *  * GLOB_ONLYDIR - Return only directory entries which match the pattern
     *  * GLOB_ERR - Stop on read errors (like unreadable directories), by default errors are ignored.
     *
     * @param boolean $forceFallback Force the fallback?
     *
     * @return array|false Returns an array containing the matched files/directories, an empty array if no file matched or FALSE on error.
     */
    public static function find($pattern, $flags = 0, $forceFallback = false)
    {
        // sanitize the pattern
        $pattern = str_replace('/', DIRECTORY_SEPARATOR, $pattern);

        if ($forceFallback || (stripos(PHP_OS, 'win') === 0 && strlen($pattern) > 260)
            || !defined('GLOB_BRACE')
        ) {
            return self::glob($pattern, $flags);
        }

        return glob($pattern, $flags);
    }

    protected static function glob($pattern, $flags)
    {
        $return_failure = ($flags & GLOB_NOCHECK) ? array($pattern) : false;

        // build path to scan files
        $path = '.';
        $wildcards_open = '*?[';
        $wildcards_close = ']';
        if ($flags & GLOB_BRACE) {
            $wildcards_open .= '{';
            $wildcards_close .= '}';
        }

        $prefix_length = strcspn($pattern, $wildcards_open);
        if ($prefix_length) {
            if (DIRECTORY_SEPARATOR == '\\') {
                $sep = ($flags & GLOB_NOESCAPE) ? '[\\\\/]'
                    : '(?:/|\\\\(?![' . $wildcards_open . $wildcards_close . ']))';
            } else {
                $sep = '/';
            }

            if (preg_match('#^(.*)' . $sep . '#', substr($pattern, 0, $prefix_length), $matches)) {
                $path = $matches[1];
            }
        }

        $recurse = (strpos($pattern, DIRECTORY_SEPARATOR, $prefix_length) !== false);

        // scan files
        $files = self::scan($path, $flags, $recurse);

        if ($files === false) {
            return $return_failure;
        }

        // build preg pattern
        $pattern = self::convertToRegex($pattern, $flags);
        $convert_patterns = array('/\{([^{}]*)\}/e', '/\[([^\]]*)\]/e');

        $convert_replacements = array(
            'self::convertGlobBraces(\'$1\', $flags)',
            'self::convertGlobCharacterClasses(\'$1\', $flags)'
        );

        while ($flags & GLOB_BRACE) {
            $new_pattern = preg_replace($convert_patterns, $convert_replacements, $pattern);
            if ($new_pattern == $pattern) {
                break;
            }
            $pattern = $new_pattern;
        }

        $pattern = '#^' . $pattern . '\z#';

        // process files
        $results = array();
        foreach ($files as $file => $dir) {
            if (!preg_match($pattern, $file, $matches)) {
                continue;
            }
            if (($flags & GLOB_ONLYDIR) && !$dir) {
                continue;
            }

            $file = (($flags & GLOB_MARK) && $dir) ? $file . DIRECTORY_SEPARATOR : $file;
            if ($flags & GLOB_BRACE) {
                // find last matching subpattern
                $rank = 0;
                for ($i = 1, $matchc = count($matches); $i < $matchc; $i++) {
                    if (strlen($matches[$i])) {
                        $rank = $i;
                    }
                }
                $file = array($file, $rank);
            }
            $results[] = $file;
        }

        if ($flags & GLOB_BRACE) {
            usort($results, ($flags & GLOB_NOSORT) ? array('self', 'braceNoSort') : array('self', 'braceSort'));
        } elseif ($flags & GLOB_NOSORT) {
            usort($results, array('self', 'noSort'));
        } else {
            sort($results);
        }

        // array_values() for php 4 +
        $reindex = array();
        foreach ($results as $result) {
            $reindex[] = ($flags & GLOB_BRACE) ? $result[0] : $result;
        }

        if (($flags & GLOB_NOCHECK) && !count($reindex)) {
            return $return_failure;
        }

        return $reindex;
    }

    /**
     * Scans a path
     *
     * @param string $path
     *  the path to scan
     * @param int    $flags
     *  the flags passed to glob()
     * @param bool   $recurse
     *  true to scan recursively
     *
     * @return mixed
     *  an array of files in the given path where the key is the path,
     *  and the value is 1 if the file is a directory, 0 if it isn't.
     *  Returns false on unrecoverable errors, or all errors when
     *  GLOB_ERR is on.
     */
    public static function scan($path, $flags, $recurse = false)
    {
        if (!is_readable($path)) {
            return false;
        }

        $results = array();
        if (is_dir($path)) {
            $fp = opendir($path);
            if (!$fp) {
                return ($flags & GLOB_ERR) ? false : array($path);
            }
            if ($path != '.') {
                $results[$path] = 1;
            }
            while (($file = readdir($fp)) !== false) {
                if ($file[0] == '.' || $file == '..') {
                    continue;
                }
                $filepath = ($path == '.') ? $file : $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($filepath)) {
                    $results[$filepath] = 1;
                    if (!$recurse) {
                        continue;
                    }
                    $files = self::scan($filepath, $flags);
                    if ($files === false) {
                        if ($flags & GLOB_ERR) {
                            return false;
                        }
                        continue;
                    }
                    // array_merge for php 4 +
                    foreach ($files as $rfile => $rdir) {
                        $results[$rfile] = $rdir;
                    }
                    continue;
                }
                $results[$filepath] = 0;
            }
            closedir($fp);
        } else {
            $results[$path] = 0;
        }

        return $results;
    }

    /**
     * Converts a section of a glob pattern to a PCRE pattern
     *
     * @param string $input
     *  the pattern to convert
     * @param int    $flags
     *  the flags passed to glob()
     *
     * @return string
     *  the escaped input
     */
    public static function convertToRegex($input, $flags)
    {
        $opens = array(
            '{' => array('}', 0),
            '[' => array(']', 0),
            '(' => array(')', 0)
        );
        $ret = '';
        for ($i = 0, $len = strlen($input); $i < $len; $i++) {
            $skip = false;
            $c = $input[$i];
            $escaped = ($i && $input[$i - 1] == '\\' && ($flags & GLOB_NOCHECK == false));

            // skips characters classes and subpatterns, they are escaped in their respective helpers
            foreach ($opens as $k => $v) {
                if ($v[1]) {
                    if ($c == $v[0] && !$escaped) {
                        --$opens[$k][1];
                        $ret .= $c;
                        continue 2;
                    }
                    $skip = true;
                }
            }
            if (isset($opens[$c])) {
                if (!$escaped) {
                    ++$opens[$c][1];
                }
                $ret .= $c;
                continue;
            }
            if ($skip) {
                $ret .= $c;
                continue;
            }

            // converts wildcards
            switch ($c) {
                case '*':
                    $ret .= $escaped ? '*' : '.*';
                    continue 2;
                case '?':
                    if ($escaped) {
                        continue;
                    }
                    $ret .= '.';
                    continue 2;
            }
            $ret .= preg_quote($c, '#');
        }

        return $ret;
    }

    /**
     * Converts glob braces
     *
     * @param string $brace The contents of the braces to convert
     * @param int    $flags The flags passed to glob()
     *
     * @return string a PCRE subpattern of alternatives
     */
    protected static function convertGlobBraces($brace, $flags)
    {
        $alternatives = explode(',', $brace);
        for ($i = count($alternatives); $i--;) {
            $alternatives[$i] = self::convertToRegex($alternatives[$i], $flags);
        }

        return '(?:(' . implode(')|(', $alternatives) . '))';
    }

    /**
     * Converts glob character classes
     *
     * @param string $class The contents of the class to convert
     * @param int    $flags The flags passed to glob()
     *
     * @return string a PCRE character class
     */
    protected static function convertGlobCharacterClasses($class, $flags)
    {
        if (strpos($class, '-') !== false) {
            $class = strtr($class, array('-' => '')) . '-';
        }
        if (strpos($class, ']') !== false) {
            $class = ']' . strtr($class, array(']' => ''));
        }
        if (strpos($class, '^') !== false) {
            $class = '\^' . strtr($class, array('^' => ''));
        }

        return '[' . strtr($class, array('#' => '\#')) . ']';
    }

    /**
     * Callback sort function for GLOB_NOSORT
     *
     * Sorts first by the base name, then in reverse by the extension
     */
    protected static function noSort($a, $b)
    {
        $operands = array(array('full' => $a), array('full' => $b));
        foreach ($operands as $k => $v) {
            $v['pos'] = strrpos($v['full'], '.');
            if ($v['pos'] === false) {
                $v['pos'] = strlen($v['full']) - 1;
            }
            $v['slash'] = strrpos($v['full'], DIRECTORY_SEPARATOR);
            if ($v['slash'] === false) {
                $v['slash'] = strlen($v['full']) - 1;
            }
            $operands[$k]['dir'] = substr($v['full'], 0, $v['slash']);
            $operands[$k]['base'] = substr($v['full'], $v['slash'], $v['pos'] - $v['slash']);
            $operands[$k]['ext'] = substr($v['full'], $v['pos'] + 1);
        }
        $dir_cmp = strcmp($operands[0]['dir'], $operands[1]['dir']);
        if ($dir_cmp == 0) {
            $base_cmp = strcmp($operands[0]['base'], $operands[1]['base']);
            if ($base_cmp == 0) {
                $ext_cmp = strcmp($operands[0]['ext'], $operands[1]['ext']);

                return -$ext_cmp;
            }

            return $base_cmp;
        }

        return -$dir_cmp;
    }

    /**
     * Callback sort function for (GLOB_BRACE | GLOB_NOSORT)
     *
     *
     */
    public static function braceNosort($a, $b)
    {
        if ($a[1] == $b[1]) {
            $len_a = strlen($a[0]);
            $len_b = strlen($b[0]);
            if ($len_a == $len_b) {
                return -strcmp($a[0], $b[0]);
            }

            return ($len_a < $len_b) ? -1 : 1;
        }

        return ($a[1] < $b[1]) ? -1 : 1;
    }

    /**
     * Callback sort function for GLOB_BRACE
     *
     * Each argument should be an array where the first element is the
     * file path, and the second is its rank. The rank is the number of
     * alternatives between this match and the beginning of the brace.
     */
    protected static function braceSort($a, $b)
    {
        if ($a[1] == $b[1]) {
            return strcmp($a[0], $b[0]);
        }

        return ($a[1] < $b[1]) ? -1 : 1;
    }

}
