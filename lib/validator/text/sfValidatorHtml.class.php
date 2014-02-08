<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorHtml validates an HTML string. It also converts the input value to a string.
 * It utilizes sfSanitizer
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorHtml extends sfValidatorString
{
  /**
   * Configures the current validator.
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addMessage('allowed_tags', 'The text contains unallowed HTML tags.');

    $this->addOption('strip', true);
    // Mandatory. We don't complain about HTML here, we clean it
    $this->setOption('strip', true);

    parent::configure($options, $messages);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $clean = (string) $value;

    if ($this->getOption('strip'))
    {
      $clean = sfSanitizer::sanitize($clean, $this->getOptionOrFalse('allowed_tags'), $this->getOptionOrFalse('complete'), $this->getOptionOrFalse('allowed_attributes'), $this->getOptionOrFalse('allowed_styles'));
    }
    else
    {
      throw new sfException('That should not happen strip is set in configure in sfValidatorHtml');
    }

    $clean = parent::doClean($clean);

    return $clean;
  }

  protected function getOptionOrFalse($s)
  {
    $option = $this->getOption($s);
    if (is_null($option))
    {
      return false;
    }

    return $option;
  }
}
