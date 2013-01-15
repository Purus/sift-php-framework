<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Returns a default value instead of throwing an error on validation failure.
 *
 *     $this->validatorSchema['sort'] = new sfValidatorDefault(array(
 *       'validator' => new sfValidatorChoice(array('choices' => array('up', 'down'))),
 *       'default'   => 'up',
 *     ));
 *
 * If no default option is provided, the supplied validator's empty value will
 * be returned on error.
 *
 * @package    Sift
 * @subpackage validator
 * @author     Kris Wallsmith <kris.wallsmith@symfony-project.com>
 */
class sfValidatorDefault extends sfValidatorBase
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * validator: The validator to use
   *  * default:   The value to return if the validator fails
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('validator');
    $this->addOption('default', null);
  }

  /**
   * @see sfValidatorBase
   */
  protected function isEmpty($value)
  {
    return false;
  }

  /**
   * @see sfValidatorBase
   *
   * @throws InvalidArgumentException If the validator option is not a validator object
   */
  protected function doClean($value)
  {
    $validator = $this->getOption('validator');

    if (!$validator instanceof sfValidatorBase)
    {
      throw new InvalidArgumentException('The "validator" option must be an instance of sfValidatorBase.');
    }

    try
    {
      return $validator->clean($value);
    }
    catch (sfValidatorError $error)
    {
      return null === $this->getOption('default') ? $validator->getEmptyValue() : $this->getOption('default');
    }
  }
}
