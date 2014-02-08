<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Javascript view (extension .pjs), used for generating javascript
 *
 * @package Sift
 * @subpackage view
 */
class sfJavascriptView extends sfPHPView
{
  /**
   * File extension
   *
   * @var string
   */
  protected $extension = '.pjs';

  public function configure()
  {
    $response = $this->getContext()->getResponse();
    // turn off escaping
    $this->setEscaping(false);
    // set content type
    $response->setContentType('application/x-javascript; charset=' . sfConfig::get('sf_charset'));
    // disable layout
    $response->setParameter($this->moduleName.'_'.$this->actionName.'_layout', false, 'sift/action/view');

    $this->setTemplate($this->actionName.$this->viewName.$this->getExtension());

    // Set template directory
    if (!$this->directory) {
      $this->setDirectory(sfLoader::getTemplateDir($this->moduleName, $this->getTemplate()));
    }
  }

}
