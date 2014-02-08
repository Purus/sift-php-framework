<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Limited scope for PHP code evaluation and script including.
 *
 * @package    Sift
 * @subpackage util
 * @author     David Grudl
 */
final class sfLimitedScope
{
    /**
     * Static class - cannot be instantiated.
     */
    final public function __construct()
    {
        throw new LogicException('Static class cannot be initialized');
    }

    /**
     * Evaluates code in limited scope.
     *
     * @param string $code      PHP code
     * @param array  $variables The local variables
     *
     * @return mixed the return value of the evaluated code
     */
    public static function evaluate( /* $code, array $vars = NULL */)
    {
        if (func_num_args() > 1) {
            extract(func_get_arg(1));
        }
        $res = eval('?>' . func_get_arg(0));
        if ($res === false && ($error = error_get_last()) && $error['type'] === E_PARSE) {
            throw new sfParseException($error['message']);
        }

        return $res;
    }

    /**
     * Includes script in a limited scope.
     *
     * @param  string $file file to include
     * @param  array  $vars local variables
     *
     * @return mixed the return value of the included file
     */
    public static function load( /* $file, array $vars = NULL */)
    {
        if (func_num_args() > 1 && is_array(func_get_arg(1))) {
            extract(func_get_arg(1));
        }

        return include func_get_arg(0);
    }

    /**
     * Renders the script in a limited scope.
     *
     * @param  string $file file to include
     * @param  array  $vars ocal variables
     *
     * @return mixed the return value of the included file
     */
    public static function render( /* $file, array $vars = NULL */)
    {
        ob_start();
        $args = func_get_args();
        call_user_func_array(array('sfLimitedScope', 'load'), $args);

        return ob_get_clean();
    }

}
