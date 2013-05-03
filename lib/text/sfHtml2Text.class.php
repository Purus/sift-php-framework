<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * Converts HTML to plain text
 *
 * @package Sift
 * @subpackage text
 * @license http://www.eclipse.org/legal/epl-v10.html
 */
class sfHtml2Text {

  /**
   * Tries to convert the given HTML into a plain text format - best suited for
   * e-mail display, etc.
   *
   * <p>In particular, it tries to maintain the following features:
   * <ul>
   *   <li>Links are maintained, with the 'href' copied over
   *   <li>Information in the &lt;head&gt; is lost
   * </ul>
   *
   * @param html the input HTML
   * @return the HTML converted, as best as possible, to text
   */
  public static function convert($html)
  {
    $html = self::fixNewlines($html);
    
    // http://stackoverflow.com/a/2238149/515871
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    
    $doc = new DOMDocument('1.0', 'UTF-8');
    
    // $doc->preserveWhiteSpace = false;
    // $doc->formatOutput = true;
    // $doc->recover = true;

    if(!$doc->loadHTML($html))
    {
      throw new sfException("Could not load HTML - badly formed?");
    }
    
    $output = self::iterateOverNode($doc);
    // remove leading and trailing spaces on each line
    $output = preg_replace("/[ \t]*\n[ \t]*/im", "\n", $output);
    // remove leading and trailing whitespace
    $output = trim($output);
    return $output;
  }

  /**
   * Unify newlines; in particular, \r\n becomes \n, and
   * then \r becomes \n. This means that all newlines (Unix, Windows, Mac)
   * all become \ns.
   *
   * @param text text with any number of \r, \r\n and \n combinations
   * @return the fixed text
   */
  public static function fixNewlines($text)
  {
    // replace \r\n to \n
    $text = str_replace("\r\n", "\n", $text);
    // remove \rs
    $text = str_replace("\r", "\n", $text);
    return $text;
  }

  /**
   * Iterates over the node
   * 
   * @param DOMtext $node
   * @return string 
   */
  protected static function iterateOverNode($node)
  {
    if($node instanceof DOMText)
    {
      return preg_replace("/\\s+/im", " ", $node->wholeText);
    }
    
    if($node instanceof DOMDocumentType)
    {
      // ignore
      return "";
    }

    $nextName = self::nextChildName($node);
    $prevName = self::prevChildName($node);

    $name = strtolower($node->nodeName);

    // start whitespace
    switch($name)
    {
      case "hr":
        return "------\n";

      case "style":
      case "head":
      case "title":
      case "meta":
      case "script":
        // ignore these tags
        return "";

      case "h1":
      case "h2":
      case "h3":
      case "h4":
      case "h5":
      case "h6":
        // add two newlines
        // $output = "\n";
        $output = "*** ";
        break;

      case "p":
      case "div":
        // add one line
        $output = "\n";
        break;

      case 'ul':
        $output = '---';
      break;  
    
      case 'li':
        $output = '\t* ';
      break;  
    
      default:
        // print out contents of unknown tags
        $output = "";
        break;
    }

    for($i = 0; $i < $node->childNodes->length; $i++)
    {
      $n = $node->childNodes->item($i);
      $text = self::iterateOverNode($n);
      $output .= $text;
    }

    // end whitespace
    switch($name)
    {
      case "style":
      case "head":
      case "title":
      case "meta":
      case "script":
        // ignore these tags
        return "";

      case "h1":
      case "h2":
      case "h3":
      case "h4":
      case "h5":
      case "h6":
        $output .= " ***\n";
        break;

      case "p":
      case "br":
        // add one line
        if($nextName != "div")
          $output .= "\n";
        break;

      case "div":
        // add one line only if the next child isn't a div
        if($nextName != "div" && $nextName != null)
          $output .= "\n";
        break;

        
      case "a":
        // links are returned in [text](link) format
        $href = $node->getAttribute("href");
        if($href == null)
        {
          // it doesn't link anywhere
          if($node->getAttribute("name") != null)
          {
            $output = "[$output]";
          }
        }
        else
        {
          if($href == $output)
          {
            // link to the same address: just use link
            $output;
          }
          else
          {
            // replace it
            $output = "$output ($href)";
          }
        }

        // does the next node require additional whitespace?
        switch($nextName)
        {
          case "h1": case "h2": case "h3": case "h4": case "h5": case "h6":
            $output .= "\n";
            break;
        }

      default:
      // do nothing

    }
    return $output;
  }

  protected static function prevChildName($node) 
  {
    // get the previous child
    $nextNode = $node->previousSibling;
    while($nextNode != null) 
    {
      if($nextNode instanceof DOMElement) 
      {
        break;
      }
		$nextNode = $nextNode->previousSibling;
    }
    $nextName = null;
    if($nextNode instanceof DOMElement && $nextNode != null) 
    {
      $nextName = strtolower($nextNode->nodeName);
    }
    return $nextName;
  }
  
  public static function nextChildName($node) 
  {
    // get the next child
    $nextNode = $node->nextSibling;
    while($nextNode != null) 
    {
      if($nextNode instanceof DOMElement) 
      {
        break;
      }
      $nextNode = $nextNode->nextSibling;
    }
    $nextName = null;
    if($nextNode instanceof DOMElement && $nextNode != null) 
    {
      $nextName = strtolower($nextNode->nodeName);
    }
    return $nextName;
  }

}
