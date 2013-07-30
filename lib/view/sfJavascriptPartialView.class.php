<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHP partial view for javascript (.pjs)
 *
 * @package    Sift
 * @subpackage view
 */
class sfJavascriptPartialView extends sfPartialView
{
  protected $extension = '.pjs';

  public function configure()
  {
    parent::configure();
    // disable escaping
    $this->setEscaping(false);

    sfLoader::loadHelpers('Tag');
  }

  /**
   * Renders the presentation.
   *
   * @param  string $_sfFile  Filename
   *
   * @return string File content
   */
  public function render($templateVars = array())
  {
    ob_start();
    start_javascript();
    echo parent::render($templateVars);
    end_javascript();
    return ob_get_clean();
  }

}
