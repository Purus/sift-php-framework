<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSpamProtect is a simple widget for protecting form against spam
 * bots. Main idea is based on the blog post by Tobias Sjösten
 *
 * @package    Sift
 * @subpackage form_widget
 * @author     Mishal.cz <mishal at mishal dot cz>
 * @link       http://vvv.tobiassjosten.net/symfony/stopping-spam-with-symfony-forms/
 */
class sfWidgetFormSpamProtectTimer extends sfWidgetFormInputHidden {

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * id_format:       The format for the generated HTML id attributes (%s by default)
   *  * is_hidden:       true if the form widget must be hidden, false otherwise (false by default)
   *  * needs_multipart: true if the form widget needs a multipart form, false otherwise (false by default)
   *  * default:         The default value to use when rendering the widget
   *  * label:           The label to use when the widget is rendered by a widget schema
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidget
   */
  public function __construct($options = array(), $attributes = array())
  {
    $attributes['value'] = base64_encode(time());
    parent::__construct($options, $attributes);
  }
  
}
