<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Uflify compiler for javascripts.
 *
 * @package    Sift
 * @subpackage minifier
 * @see http://marijnhaverbeke.nl//uglifyjs
 */
class sfMinifierDriverUglifyApi extends sfMinifier {

  /**
   * Browser holder
   *
   * @var sfWebBrowser
   */
  protected $browser;

  /**
   * Array of default options
   */
  protected $defaultOptions = array(
    'api_url' => 'http://marijnhaverbeke.nl/uglifyjs'
  );

  /**
   * Processes the file
   *
   * @param string $file Path to a file
   * @param boolean $replace Replace the existing file?
   */
  public function doProcessFile($file, $replace = false)
  {
    $result = $this->proccess(file_get_contents($file));

    if($replace)
    {
      $this->replaceFile($file, $result);
    }

    return $result;
  }

  /**
   * Processes the string
   *
   * @param string $string
   * @return string Processed string
   */
  public function processString($string)
  {
    return $this->proccess($string);
  }

  /**
   * Process string. Calls the service API.
   *
   * @param string $string
   * @return string
   * @throws sfException
   */
  protected function proccess($string)
  {
    $browser = $this->getBrowser();

    $browser->post($this->getOption('api_url'), array(
     'utf8' => true,
     'js_code' => $string
    ),
    // headers
    array(

    ));

    if($browser->responseIsError())
    {
      throw new sfException('An error occured while requesting compiler api.');
    }

    return $browser->getResponseText();
  }

  /**
   * Returns an instance of web browser
   * @return sfWebBrowser
   */
  protected function getBrowser()
  {
    if(!$this->browser)
    {
      $this->browser = new sfWebBrowser();
    }
    return $this->browser;
  }

}