<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMessageSourceGettext class
 *
 * Using Gettext MO format as the message source for translation.
 *
 * See the sfI18nMessageSource::factory() method to instantiate this class.
 *
 * @author Xiang Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @package Sift
 * @subpackage i18n
 */
class sfI18nMessageSourceGettext extends sfI18nMessageSource {

  /**
   * Message data filename extension.
   * @var string
   */
  protected $dataExt = '.mo';

  /**
   * PO data filename extension
   * @var string
   */
  protected $poExt = '.po';

  /**
   * Separator between culture name and source.
   * @var string
   */
  protected $dataSeparator = '.';

  /**
   * Constructs the source
   *
   * @param string $source Catalogue directory
   */
  public function __construct($source)
  {
    $this->source = (string) $source;
  }

  /**
   * Loads the messages from a MO file.
   *
   * @param string MO file.
   * @return array of messages.
   */
  protected function &loadData($filename)
  {
    $mo = sfI18nGettext::factory('Mo', $filename);
    $mo->load();
    $result = $mo->toArray();

    $results = array();
    $count = 0;
    foreach($result['strings'] as $source => $target)
    {
      $results[$source][] = $target;  //target
      $results[$source][] = $count++; //id
      $results[$source][] = '';       //comments
    }

    return $results;
  }

  /**
   * Determines if the MO file source is valid.
   *
   * @param string MO file
   * @return boolean true if valid, false otherwise.
   */
  protected function isValidSource($filename)
  {
    return is_file($filename);
  }

  /**
   * Gets the MO file for a specific message catalogue and cultural variant.
   *
   * @param string message catalogue
   * @return string full path to the MO file.
   */
  protected function getSource($variant)
  {
    return $this->source . '/' . $variant;
  }

  /**
   * Gets the last modified unix-time for this particular catalogue+variant.
   * Just use the file modified time.
   *
   * @param string catalogue+variant
   * @return int last modified in unix-time format.
   */
  protected function getLastModified($source)
  {
    return is_file($source) ? filemtime($source) : 0;
  }

  /**
   * Gets all the variants of a particular catalogue.
   *
   * @param string catalogue name
   * @return array list of all variants for this catalogue.
   */
  protected function getCatalogueList($catalogue)
  {
    $variants = explode('_', $this->culture);
    $source = $catalogue . $this->dataExt;

    $catalogues = array($source);

    $variant = null;

    for($i = 0, $max = count($variants); $i < $max; $i++)
    {
      if(strlen($variants[$i]) > 0)
      {
        $variant .= $variant ? '_' . $variants[$i] : $variants[$i];
        $catalogues[] = $catalogue . $this->dataSeparator . $variant . $this->dataExt;
      }
    }

    $byDir = $this->getCatalogueByDir($catalogue);
    $catalogues = array_merge($byDir, array_reverse($catalogues));
    return $catalogues;
  }

  /**
   * Traverses through the directory structure to find the catalogues.
   * This should only be called by getCatalogueList()
   *
   * @param string a particular catalogue.
   * @return array a list of catalogues.
   * @see getCatalogueList()
   */
  protected function getCatalogueByDir($catalogue)
  {
    $variants = explode('_', $this->culture);
    $catalogues = array();

    $variant = null;

    for($i = 0, $max = count($variants); $i < $max; $i++)
    {
      if(strlen($variants[$i]) > 0)
      {
        $variant .= $variant ? '_' . $variants[$i] : $variants[$i];
        $catalogues[] = $variant . '/' . $catalogue . $this->dataExt;
      }
    }

    return array_reverse($catalogues);
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
  protected function getVariants($catalogue = 'messages')
  {
    if(empty($catalogue))
    {
      $catalogue = 'messages';
    }

    foreach($this->getCatalogueList($catalogue) as $variant)
    {
      $file = $this->getSource($variant);
      $po = $this->getPOFile($file);
      if(is_file($file) || is_file($po))
      {
        return array($variant, $file, $po);
      }
    }

    return false;
  }

  protected function getPOFile($MOFile)
  {
    return substr($MOFile, 0, strlen($MOFile) - strlen($this->dataExt)) . $this->poExt;
  }

  /**
   * Saves the list of untranslated blocks to the translation source.
   * If the translation was not found, you should add those
   * strings to the translation source via the append() method.
   *
   * @param string the catalogue to add to
   * @return boolean true if saved successfuly, false otherwise.
   */
  public function save($catalogue = 'messages')
  {
    $messages = $this->untranslated;

    if(count($messages) <= 0)
    {
      return false;
    }

    $variants = $this->getVariants($catalogue);

    if($variants)
    {
      list($variant, $MOFile, $POFile) = $variants;
    }
    else
    {
      list($variant, $MOFile, $POFile) = $this->createMessageTemplate($catalogue);
    }

    if(is_writable($MOFile) == false)
    {
      throw new sfException(sprintf("Unable to save to file %s, file must be writable.", $MOFile));
    }
    if(is_writable($POFile) == false)
    {
      throw new sfException(sprintf("Unable to save to file %s, file must be writable.", $POFile));
    }

    // set the strings as untranslated.
    $strings = array();
    foreach($messages as $message)
    {
      $strings[$message] = '';
    }

    // load the PO
    $po = sfI18nGettext::factory('Po', $POFile);
    $po->load();
    $result = $po->toArray();

    $existing = count($result['strings']);

    // add to strings to the existing message list
    $result['strings'] = array_merge($result['strings'], $strings);

    $new = count($result['strings']);

    if($new > $existing)
    {
      // change the date 2004-12-25 12:26
      $result['meta']['PO-Revision-Date'] = @date('Y-m-d H:i:s');

      $po->fromArray($result);
      $mo = $po->toMO();
      if($po->save() && $mo->save($MOFile))
      {
        if(!empty($this->cache))
        {
          $this->cache->clean($variant, $this->culture);
        }

        return true;
      }
      else
      {
        return false;
      }
    }

    return false;
  }

  /**
   * Deletes a particular message from the specified catalogue.
   *
   * @param string the source message to delete.
   * @param string the catalogue to delete from.
   * @return boolean true if deleted, false otherwise.
   */
  function delete($message, $catalogue = 'messages')
  {
    $variants = $this->getVariants($catalogue);
    if($variants)
    {
      list($variant, $MOFile, $POFile) = $variants;
    }
    else
    {
      return false;
    }

    if(is_writable($MOFile) == false)
    {
      throw new sfException(sprintf("Unable to modify file %s, file must be writable.", $MOFile));
    }

    if(is_writable($POFile) == false)
    {
      throw new sfException(sprintf("Unable to modify file %s, file must be writable.", $POFile));
    }

    $po = sfI18nGettext::factory('PO', $POFile);
    $po->load();
    $result = $po->toArray();

    foreach($result['strings'] as $string => $value)
    {
      if($string == $message)
      {
        $result['meta']['PO-Revision-Date'] = @date('Y-m-d H:i:s');
        unset($result['strings'][$string]);

        $po->fromArray($result);
        $mo = $po->toMO();
        if($po->save() && $mo->save($MOFile))
        {
          if(!empty($this->cache))
          {
            $this->cache->clean($variant, $this->culture);
          }

          return true;
        }
        else
        {
          return false;
        }
      }
    }

    return false;
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
  public function update($text, $target, $comments, $catalogue = 'messages')
  {
    $variants = $this->getVariants($catalogue);
    if($variants)
    {
      list($variant, $MOFile, $POFile) = $variants;
    }
    else
    {
      return false;
    }

    if(is_writable($MOFile) == false)
    {
      throw new sfException(sprintf("Unable to update file %s, file must be writable.", $MOFile));
    }

    if(is_writable($POFile) == false)
    {
      throw new sfException(sprintf("Unable to update file %s, file must be writable.", $POFile));
    }

    $po = sfI18nGettext::factory('PO', $POFile);
    $po->load();
    $result = $po->toArray();

    foreach($result['strings'] as $string => $value)
    {
      if($string == $text)
      {
        $result['strings'][$string] = $target;
        $result['meta']['PO-Revision-Date'] = @date('Y-m-d H:i:s');

        $po->fromArray($result);
        $mo = $po->toMO();

        if($po->save() && $mo->save($MOFile))
        {
          if(!empty($this->cache))
          {
            $this->cache->clean($variant, $this->culture);
          }

          return true;
        }
        else
        {
          return false;
        }
      }
    }

    return false;
  }

  /**
   * Returns a list of catalogue as key and all it variants as value.
   *
   * @return array list of catalogues
   */
  public function catalogues()
  {
    return $this->getCatalogues();
  }

  /**
   * Returns a list of catalogue and its culture ID. This takes care
   * of directory structures.
   * E.g. array('messages','en_AU')
   *
   * @return array list of catalogues
   */
  protected function getCatalogues($dir = null, $variant = null)
  {
    $dir = $dir ? $dir : $this->source;
    $files = scandir($dir);

    $catalogue = array();

    foreach($files as $file)
    {
      if(is_dir($dir . '/' . $file) && preg_match('/^[a-z]{2}(_[A-Z]{2,3})?$/', $file))
      {
        $catalogue = array_merge($catalogue, $this->getCatalogues($dir . '/' . $file, $file));
      }

      $pos = strpos($file, $this->dataExt);

      if($pos > 0 && substr($file, -1 * strlen($this->dataExt)) == $this->dataExt)
      {
        $name = substr($file, 0, $pos);
        $dot = strrpos($name, $this->dataSeparator);
        $culture = $variant;
        $cat = $name;
        if(is_int($dot))
        {
          $culture = substr($name, $dot + 1, strlen($name));
          $cat = substr($name, 0, $dot);
        }
        $details[0] = $cat;
        $details[1] = $culture;

        $catalogue[] = $details;
      }
    }
    sort($catalogue);

    return $catalogue;
  }

  protected function createMessageTemplate($catalogue)
  {
    if(is_null($catalogue))
    {
      $catalogue = 'messages';
    }

    $variants = $this->getCatalogueList($catalogue);
    $variant = array_shift($variants);
    $mo_file = $this->getSource($variant);
    $po_file = $this->getPOFile($mo_file);

    $dir = dirname($mo_file);
    if(!is_dir($dir))
    {
      @mkdir($dir);
      @chmod($dir, 0777);
    }

    if(!is_dir($dir))
    {
      throw new sfException(sprintf("Unable to create directory %s.", $dir));
    }

    $po = sfI18nGettext::factory('PO', $po_file);
    $result['meta']['PO-Revision-Date'] = date('Y-m-d H:i:s');
    $result['meta']['Content-Type'] = 'text/plain; charset=utf-8';

    // add another meta to save translators time clicking in the translation tool
    $result['meta']['Language'] = $this->culture;
    $result['meta']['Project-Id-Version'] = '1.0';

    $result['strings'] = array();

    $po->fromArray($result);
    $mo = $po->toMO();
    if($po->save() && $mo->save($mo_file))
    {
      return array($variant, $mo_file, $po_file);
    }
    else
    {
      throw new sfException(sprintf("Unable to create file %s and %s.", $po_file, $mo_file));
    }
  }

}
