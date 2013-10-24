<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSpamProtectTimer is a validator for sfWidgetFormSpamProtectTimer
 * widget
 *
 * @package    Sift
 * @subpackage validator
 * @link       http://vvv.tobiassjosten.net/symfony/stopping-spam-with-symfony-forms/
 */
class sfValidatorSpamProtectTimer extends sfValidatorBase {

  /**
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->setOption('required', true);
    $this->addMessage('tampered', 'Spam bots are not welcomed here. Go away!');
    $this->addMessage('nan', 'Spam bots are not welcomed here. Go away!');
    $this->addMessage('min_time', 'Spam bots are not welcomed here. Go away!');
    $this->addMessage('max_time', 'Spam bots are not welcomed here. Go away!');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $time = base64_decode($value);
    if(!$time)
    {
      throw new sfValidatorError($this, 'tampered');
    }
    if(!is_numeric($time))
    {
      throw new sfValidatorError($this, 'nan');
    }

    $time_ago = time() - $time;
    if($time_ago > 84600)
    {
      throw new sfValidatorError($this, 'max_time');
    }

    if($time_ago < 7)
    {
      throw new sfValidatorError($this, 'min_time');
    }

    return time();
  }

  /**
   * Returns messages used by the validator. This method is usefull to
   * i18n extract which extract only those messages which are in use
   *
   * @return array Array of messages.
   */
  public function getActiveMessages()
  {
    $messages = $this->messages;
    unset($messages['invalid']);
    unset($messages['required']);
    return $messages;
  }

}