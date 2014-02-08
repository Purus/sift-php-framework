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
 * @link       http://vvv.tobiassjosten.net/symfony/stopping-spam-with-symfony-forms/
 */
class sfWidgetFormSpamProtectTimer extends sfWidgetFormInputHidden
{
  /**
   * Constructor.
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
