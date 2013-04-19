<?php
/*
 * This file is part of the Sift package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Translation tool
 *
 * @package Sift
 * @subpackage module
 */
class BasesfI18nComponents extends myComponents {

  /**
   * Translation tool
   *
   */
  public function executeTranslationTool()
  {
    $translations = $this->getContext()->getI18n()->getRequestedTranslations();
    $trans = array_values($translations);
    $untranslated = 0;

    foreach($trans as $catalogue => $catalogueTranslations)
    {
      foreach($catalogueTranslations as $transId => $trans)
      {
        if(!$trans['is_translated'])
        {
          $untranslated++;
        }
      }
    }

    $this->translations = $translations;
    $this->untranslated = $untranslated;
  }

}
