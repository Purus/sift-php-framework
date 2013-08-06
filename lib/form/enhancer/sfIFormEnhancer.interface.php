<?php
/*
 * This file is part of the Sift package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIFormEnhancer interface
 *
 * @package    Sift
 * @subpackage form_enhancer
 */
interface sfIFormEnhancer {

  /**
   * Enhance form
   *
   * @param sfForm $form
   */
  public function enhance(sfForm $form);

  /**
   * Enhances any forms before they're passed to the template.
   *
   * @param sfEvent $event
   * @param array $variables Array of variables passed to the template
   * @return array Variables
   */
  public function filterTemplateVariables(sfEvent $event, array $variables);

}
