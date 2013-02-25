<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorUrl validates Urls.
 *
 * @package    Sift
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfValidatorUrl extends sfValidatorRegex
{
  const REGEX_URL_FORMAT = '/^((%s):\/\/)%s(([a-z0-9-]+\.)+[a-z]{2,6}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(:[0-9]+)?(\/?|\/\S+)$/i';

  /**
   * @param array $options   An array of options
   * @param array $messages  An array of error messages
   *
   * @see sfValidatorRegex
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);
    // does the value need protocol to be present?
    $this->addOption('strict', false);
    // check over network?
    $this->addOption('network_check', false);
    // valid protocols
    $this->addOption('protocols', array('http', 'https', 'ftp', 'ftps'));
    $this->setOption('pattern', new sfCallable(array($this, 'generateRegex')));
  }

  /**
   * Cleans the value
   *
   * @param string $value
   * @return string
   */
  protected function doClean($value)
  {
    $clean = (string) $value;

    if(!$this->getOption('strict'))
    {
      // If the URL doesn't start with protocol, assume that the link is a link to
      // http:// scheme
      if(!preg_match(sprintf('/(%s):\/\/.+/', join('|', $this->getOption('protocols'))), $clean))
      {
        $clean = 'http://'.$clean;
      }
    }

    $clean = parent::doClean($clean);

    // we need to check the existance over network
    if($this->getOption('network_check'))
    {
      $browser = new sfWebBrowser();
      try
      {
        // unsuccessful response
        if($browser->get($clean)->responseIsError())
        {
          new sfValidatorError($this, 'invalid');
        }
      }
      catch(Exception $e)
      {
      }
    }

    return $clean;
  }

  /**
   * Generates the current validator's regular expression.
   *
   * @return string
   */
  public function generateRegex()
  {
    return sprintf(self::REGEX_URL_FORMAT,
            implode('|', $this->getOption('protocols')),
            $this->getOption('strict') ? '' : '?'
      );
  }

}
