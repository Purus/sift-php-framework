<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHP partial view
 *
 * @package    Sift
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfPartialView extends sfPHPView
{
  protected
    $partialVars = array();
  
  /**
   * Executes any presentation logic for this view.
   */
  public function execute()
  {
  }

  /**
   * Configures template for this view.
   */
  public function configure()
  {
    $this->setDecorator(false);

    $this->setTemplate($this->actionName.$this->getExtension());
    if ('global' == $this->moduleName)
    {
      $this->setDirectory(sfConfig::get('sf_app_template_dir'));
    }
    else
    {
      $this->setDirectory(sfLoader::getTemplateDir($this->moduleName, $this->getTemplate()));
    }
  }

  /**
   * @param array $partialVars
   */
  public function setPartialVars(array $partialVars)
  {
    $this->partialVars = $partialVars;
    $this->attributeHolder->add($partialVars);
  }
  
  /**
   * Renders the presentation.
   *
   * @param array Template attributes
   *
   * @return string Current template content
   */
  public function render($templateVars = array())
  {
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer = sfTimerManager::getTimer(sprintf('Partial "%s/%s"', $this->moduleName, $this->actionName));
    }

    // execute pre-render check
    $this->preRenderCheck();

    // assigns some variables to the template
    $this->attributeHolder->add($this->getGlobalVars());
    $this->attributeHolder->add($templateVars);

    // render template
    $retval = $this->renderFile($this->getDirectory().'/'.$this->getTemplate());

    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer->addTime();
    }

    return $retval;
  }
}
