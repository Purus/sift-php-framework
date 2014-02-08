<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * JavascriptHelper.
 *
 * @package    Sift
 * @subpackage helper
 */

/**
 * Returns a link that'll trigger a javascript function using the
 * onclick handler and return false after the fact.
 *
 * Examples:
 *   <?php echo link_to_function('Greeting', "alert('Hello world!')") ?>
 *   <?php echo link_to_function(image_tag('delete'), "if confirm('Really?'){ do_delete(); }") ?>
 */
function link_to_function($name, $function, $html_options = array())
{
  $html_options = _parse_attributes($html_options);

  $html_options['href'] = isset($html_options['href']) ? $html_options['href'] : '#';
  $html_options['onclick'] = $function . '; return false;';

  return content_tag('a', $name, $html_options);
}

/**
 * Returns a button that'll trigger a javascript function using the
 * onclick handler and return false after the fact.
 *
 * Examples:
 *   <?php echo button_to_function('Greeting', "alert('Hello world!')") ?>
 */
function button_to_function($name, $function, $html_options = array())
{
  $html_options = _parse_attributes($html_options);

  $html_options['onclick'] = $function . '; return false;';
  $html_options['type'] = 'button';
  $html_options['value'] = $name;

  return tag('input', $html_options);
}

/**
 * Mark the start of a block that should only be shown in the browser if JavaScript
 * is switched on.
 */
function if_javascript()
{
  ob_start();
}

/**
 * Mark the end of a block that should only be shown in the browser if JavaScript
 * is switched on.
 */
function end_if_javascript()
{
  $content = ob_get_clean();

  echo javascript_tag("document.write('" . esc_js_no_entities($content) . "');");
}

