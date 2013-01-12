<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDimensionsView extends the default view and injects alternative paths to search for templates
 *
 * @package    Sift
 * @subpackage view
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * */
class sfDimensionsView extends sfPHPView {

  /**
   * Configures the view and injects new template paths
   *
   * @todo make this work with partials (currently not an issue as this is handled by sfLoader::getTemplatePath())
   *
   */
  public function configure()
  {
    parent::configure();

    $sf_dimension_dirs = sfConfig::get('sf_dimension_dirs', array());

    foreach($sf_dimension_dirs as $dir)
    {
      if(is_readable($this->getDirectory() . '/' . $dir . '/' . $this->getTemplate()))
      {
        $this->setDirectory($this->getDirectory() . '/' . $dir);
        break;
      }
    }

    foreach($sf_dimension_dirs as $dir)
    {
      if(is_readable($this->getDecoratorDirectory() . '/' . $dir . '/' . $this->getDecoratorTemplate()))
      {
        $this->setDecoratorDirectory($this->getDecoratorDirectory() . '/' . $dir);
        break;
      }
    }
  }

}
