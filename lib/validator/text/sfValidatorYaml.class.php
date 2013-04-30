<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorYaml validates a string to be valid YAML.
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorYaml extends sfValidatorString {

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * type: Type of the structure (converted to PHP, ie. array, string)
   *
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addOption('type');

    $this->setMessage('invalid', 'The value is invalid YAML string.');
    $this->addMessage('invalid_type', 'The YAML string is invalid type. Should be "%type%".');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $clean = parent::doClean($value);

    $parser = new sfYamlParser();

    try
    {
      $yaml = $parser->parse($clean);

      if($type = $this->getOption('type'))
      {
        if(gettype($yaml) != $type)
        {
          throw new sfValidatorError($this, 'invalid_type', array('value' => $value, 'type' => $type));
        }
      }

    }
    catch(sfValidatorError $e)
    {
      throw $e;
    }
    catch(Exception $e)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }

    return $clean;
  }

}
