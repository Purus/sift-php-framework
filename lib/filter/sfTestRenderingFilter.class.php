<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTestRenderingFilter is the rendering filter used for functional testing
 *
 * @package    Sift
 * @subpackage filter
 */
class sfTestRenderingFilter extends sfRenderingFilter
{
  /**
   * Executes the filter
   *
   * @param sfFilterChain $filterChain
   */
  public function execute(sfFilterChain $filterChain)
  {
    $filterChain->execute();

    // rethrow sfForm and|or sfFormField __toString() exceptions (see sfForm and sfFormField)
    if (sfForm::hasToStringException()) {
      throw sfForm::getToStringException();
    } elseif (sfFormField::hasToStringException()) {
      throw sfFormField::getToStringException();
    }

    $this->prepare();
    $this->getContext()->getResponse()->sendContent();
  }

}
