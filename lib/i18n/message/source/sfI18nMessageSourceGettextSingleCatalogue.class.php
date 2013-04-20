<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfI18nMessageSourceGettextSingleCatalogue class is a gettext source for only
 * single catalogue.
 *
 * @package Sift
 * @subpackage i18n
 */
class sfI18nMessageSourceGettextSingleCatalogue extends sfI18nMessageSourceGettext {

  /**
   * Catalogue name
   * @var string
   */
  protected $catalogue;

  /**
   * Constructs the source. All methods which contain $catalogue as an argument
   * are called with the $catalogue.
   *
   * @param string $source Catalogue dir
   * @param string $catalogue Catalogue name
   */
  public function __construct($source, $catalogue)
  {
    $this->source = (string)$source;
    $this->catalogue = $catalogue;
  }

  /**
   * Saves the list of untranslated blocks to the translation source.
   * If the translation was not found, you should add those
   * strings to the translation source via the append() method.
   *
   * @param string the catalogue to add to
   * @return boolean true if saved successfuly, false otherwise.
   */
  public function save($catalogue = null)
  {
    return parent::save($this->catalogue);
  }

  /**
   * Deletes a particular message from the specified catalogue.
   *
   * @param string the source message to delete.
   * @param string the catalogue to delete from.
   * @return boolean true if deleted, false otherwise.
   */
  public function delete($message, $catalogue = null)
  {
    return parent::delete($message, $this->catalogue);
  }

  /**
   * Updates the translation.
   *
   * @param string the source string.
   * @param string the new translation string.
   * @param string comments
   * @param string the catalogue of the translation.
   * @return boolean true if translation was updated, false otherwise.
   */
  public function update($text, $target, $comments = '', $catalogue = null)
  {
    return parent::update($text, $target, $comments, $this->catalogue);
  }

  /**
   * Loads data from the catalogue
   *
   * @param string $catalogue
   * @return sfI18nMessageSourceGettextSingleCatalogue
   */
  public function load($catalogue = null)
  {
    return parent::load($this->catalogue);
  }

  /**
   * Gets all the variants of a particular catalogue.
   *
   * @param string catalogue name
   * @return array list of all variants for this catalogue.
   */
  protected function getCatalogueList($catalogue = null)
  {
    return parent::getCatalogueList($this->catalogue);
  }

  /**
   * Gets the variant for a catalogue depending on the current culture.
   *
   * @param string catalogue
   * @return string the variant.
   * @see save()
   * @see update()
   * @see delete()
   */
  protected function getVariants($catalogue = null)
  {
    return parent::getVariants($this->catalogue);
  }

}
