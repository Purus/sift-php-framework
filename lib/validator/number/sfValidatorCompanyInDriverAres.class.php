<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Company IN valiadator thru ARES service
 *
 * @package    Sift
 * @subpackage validator
 */
class sfValidatorCompanyInDriverAres
{
  /**
   * API url
   *
   * @var string
   */
  protected $apiUrl = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_res.cgi?odp=xml&jazyk=cz&xml=true&ICO=%s&czk=utf';

  protected $mapping =  array(
    'name' => '//are:Ares_odpovedi/are:Odpoved/D:Vypis_RES/D:ZAU/D:OF',
    'in' => '//are:Ares_odpovedi/are:Odpoved/D:Vypis_RES/D:ZAU/D:ICO',
    'town' => '//are:Ares_odpovedi/are:Odpoved/D:Vypis_RES/D:SI/D:N',
    'street' => '//are:Ares_odpovedi/are:Odpoved/D:Vypis_RES/D:SI/D:NU',
    'zip' => '//are:Ares_odpovedi/are:Odpoved/D:Vypis_RES/D:SI/D:PSC'
  );

  protected $options = array();

  public function __construct($options = array())
  {
    $this->options = $options;
  }

  public function validate($value)
  {
    $url = sprintf($this->apiUrl, $value);

    $browser = new sfWebBrowser();
    $browser->call($url);

    $response = $browser->getResponseText();

    $result = array();
    if(!$response)
    {
      return false;
    }

    $xml = new DOMDocument($response);
    $xml->loadXML($response);
    $xpath = new DomXPath($xml);

    while(list($key, $query) = each($this->mapping))
    {
      $list = $xpath->query($query);
      $i = 0;
      while($node = $list->item($i))
      {
        if(!isset($result[$i][$key]))
        {
          $result[$i][$key] = '';
        }
        else
        {
          $result[$i][$key] .= ' ';
        }
        $result[$i][$key] .= trim($node->nodeValue);
        $i++;
      }
    }

    return count($result) ? $result : false;
  }

}
