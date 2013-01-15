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

}