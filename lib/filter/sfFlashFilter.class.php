<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFlashFilter removes flash attributes from the session.
 *
 * @package    Sift
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfFlashFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain A sfFilterChain instance.
   */
  public function execute($filterChain)
  {
    $context = $this->getContext();
    $userAttributeHolder = $context->getUser()->getAttributeHolder();

    // execute this filter only once
    if ($this->isFirstCall())
    {
      // flag current flash to be removed after the execution filter
      $names = $userAttributeHolder->getNames(sfUser::FLASH_NAMESPACE);
      if ($names)
      {
        if (sfConfig::get('sf_logging_enabled'))
        {
          $context->getLogger()->info('{sfFilter} flag old flash messages ("'.implode('", "', $names).'")');
        }
        foreach ($names as $name)
        {
          $userAttributeHolder->set($name, true, 'sift/flash/remove');
        }
      }
    }

    // execute next filter
    $filterChain->execute();

    // remove flash that are tagged to be removed
    $names = $userAttributeHolder->getNames('sift/flash/remove');
    if ($names)
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        $context->getLogger()->info('{sfFilter} remove old flash messages ("'.implode('", "', $names).'")');
      }
      foreach ($names as $name)
      {
        $userAttributeHolder->remove($name, sfUser::FLASH_NAMESPACE);
        $userAttributeHolder->remove($name, 'sift/flash/remove');
      }
    }
  }
}
