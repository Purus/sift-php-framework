<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfXmlElement provides additional functionality to SimpleXMLElement
 *
 * @package    Sift
 * @subpackage xml
 */
class sfXmlElement extends SimpleXMLElement {

  public function asHTML()
  {
    $ele = dom_import_simplexml($this);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $element = $dom->importNode($ele, true);
    $dom->appendChild($element);
    return $dom->saveHTML();
  }

  /**
   * Converts the element to array
   *
   * @return array
   */
  public function toArray()
  {
    return self::elementToArray($this);
  }

  /**
   * Converts the element to array
   *
   * @param SimpleXmlElement|sfXmlElement $element
   * @return array
   * @link http://www.binarytides.com/convert-simplexml-object-to-array-in-php/
   */
  public static function elementToArray($element)
  {
    $array = array();
    foreach($element->children() as $k => $r)
    {
      if(count($r->children()) == 0)
      {
        if($element->$k->count() == 1)
        {
          $array[$r->getName()] = strval($r);
        }
        else
        {
          $array[$r->getName()][] = strval($r);
        }
      }
      else
      {
        $array[$r->getName()][] = self::elementToArray($r);
      }
    }
    return $array;
  }

}
