<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extracts messages from php files
 *
 * @package    Sift
 * @subpackage i18n_extract
 */
class sfI18nPhpExtractor extends sfConfigurable implements sfII18nExtractor
{
 /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'functions' => array(
        '__', 'format_number_choice'
    )
  );

  /**
   * Extract i18n strings for the given content.
   *
   * @param  string The content
   *
   * @return array An array of i18n strings
   */
  public function extract($content)
  {
    $functions = $this->findFunctionCalls($this->getOption('functions'), $content);

    $strings = array();

    foreach($functions as $function)
    {
      $toBeTranslated = $function['args'][0];
      if(isset($function['args'][2]))
      {
        $domain = $function['args'][2];
      }
      else
      {
        $domain = sfI18nExtract::UNKNOWN_DOMAIN;
      }

      if(!isset($strings[$domain]))
      {
        $strings[$domain] = array();
      }

      // make the array unique (not the same translations)
      if(in_array($toBeTranslated, $strings[$domain]))
      {
        continue;
      }

      $strings[$domain][] = $toBeTranslated;
    }

    return $strings;
  }

  /**
   * Finds all function calls in $code and returns an array with an associative array for each function:
   *
   *  - name - name of the function
   *  - args - array for the function arguments. Each string literal is represented by itself, other arguments are represented by null.
   *  - line - line number
   *
   * Taken from Wordpress i18n tools
   *
   * @link http://svn.automattic.com/wordpress-i18n/tools/trunk/extract/extract.php
   */
  protected function findFunctionCalls($function_names, $code)
  {
    $comment_prefix = 'translators:';
    $tokens = token_get_all($code);
    $function_calls = array();
    $latest_comment = false;
    $in_func = false;
    foreach($tokens as $token)
    {
      $id = $text = null;
      if(is_array($token))
        list( $id, $text, $line ) = $token;
      if(T_WHITESPACE == $id)
        continue;
      if(T_STRING == $id && in_array($text, $function_names) && !$in_func)
      {
        $in_func = true;
        $paren_level = -1;
        $args = array();
        $func_name = $text;
        $func_line = $line;
        $func_comment = $latest_comment ? $latest_comment : '';

        $just_got_into_func = true;
        $latest_comment = false;
        continue;
      }
      if(T_COMMENT == $id)
      {
        $text = trim(preg_replace('%^/\*|//%', '', preg_replace('%\*/$%', '', $text)));
        if(0 === strpos($text, $comment_prefix))
        {
          $latest_comment = $text;
        }
      }
      if(!$in_func)
        continue;
      if('(' == $token)
      {
        $paren_level++;
        if(0 == $paren_level)
        { // start of first argument
          $just_got_into_func = false;
          $current_argument = null;
          $current_argument_is_just_literal = true;
        }
        continue;
      }
      if($just_got_into_func)
      {
        // there wasn't a opening paren just after the function name -- this means it is not a function
        $in_func = false;
        $just_got_into_func = false;
      }
      if(')' == $token)
      {
        if(0 == $paren_level)
        {
          $in_func = false;
          $args[] = $current_argument;
          $call = array('name' => $func_name, 'args' => $args, 'line' => $func_line);
          if($func_comment)
            $call['comment'] = $func_comment;
          $function_calls[] = $call;
        }
        $paren_level--;
        continue;
      }
      if(',' == $token && 0 == $paren_level)
      {
        $args[] = $current_argument;
        $current_argument = null;
        $current_argument_is_just_literal = true;
        continue;
      }
      if(T_CONSTANT_ENCAPSED_STRING == $id && $current_argument_is_just_literal)
      {
        // we can use eval safely, because we are sure $text is just a string literal
        eval('$current_argument = ' . $text . ';');
        continue;
      }
      $current_argument_is_just_literal = false;
      $current_argument = null;
    }
    return $function_calls;
  }
}
