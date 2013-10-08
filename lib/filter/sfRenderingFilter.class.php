<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRenderingFilter is the last filter registered for each filter chain. This
 * filter does the rendering.
 *
 * @package    Sift
 * @subpackage filter
 */
class sfRenderingFilter extends sfFilter {

  /**
   * Array of default parameters
   *
   * @var array
   */
  protected $defaultParameters = array(
    'charset' => 'utf-8',
    'compress' => true,
    'compression_level' => 9,
    'compression_min_length' => 2048,
    'whitespace_removal_condition' => true,
  );

  /**
   * Executes this filter.
   *
   * @param sfFilterChain The filter chain.
   * @throws sfInitializeException If an error occurs during view initialization
   * @throws sfViewException       If an error occurs while executing the view
   */
  public function execute(sfFilterChain $filterChain)
  {
    // execute next filter
    $filterChain->execute();

    // rethrow sfForm and|or sfFormField __toString() exceptions (see sfForm and sfFormField)
    if(sfForm::hasToStringException())
    {
      throw sfForm::getToStringException();
    }
    elseif(sfFormField::hasToStringException())
    {
      throw sfFormField::getToStringException();
    }

    // send headers + content
    if(sfView::RENDER_VAR != $this->getContext()->getController()->getRenderMode())
    {
      $this->log('Render to client.', sfILogger::INFO);
      $this->prepare();
      $this->getContext()->getResponse()->send();
    }
  }

  /**
   * Prepares the response for rendering to client
   *
   */
  protected function prepare()
  {
    $response = $this->getContext()->getResponse();

    $this->getContext()->getEventDispatcher()->notify(new sfEvent('response.pre_send', array(
      'response' => $response
    )));

    // can only for HTML text
    if(!$response->isHeaderOnly()
        && is_string($response->getContent())
        && preg_match('~(text/html|javascript)+~', $response->getContentType(), $matches))
    {
      // remove whitespace
      if(!sfConfig::get('sf_test') && $this->getParameter('whitespace_removal_condition') &&
          strpos($response->getContentType(), 'text/html') !== false)
      {
        $this->log('Removing whitespace from the response content.', sfILogger::INFO);
        $content = $this->removeWhitespace($response->getContent());
        $response->setContent($content);
        $response->setHttpHeader('Content-Length', strlen($content));
      }

      // compress
      if($this->canCompress() && ($encoding = $this->getClientEncoding()))
      {
        $content = $response->getContent();

        // no need to waste resources in compressing very little data
        if(mb_strlen($content, $this->getParameter('charset')) >
            $this->getParameter('compression_min_length'))
        {
          try
          {
            $content = $this->compress(
              $content,
              $encoding,
              $this->getParameter('compression_level')
            );

            $this->log('Compresed the output using "{encoding}". Compressed size {saving}%.', sfILogger::INFO, array(
              'encoding' => $encoding,
              'saving' => ($total = strlen($response->getContent())) > 0 ? ((round(strlen($content) / $total, 2) * 100)) : 0
            ));

            $response->setContent($content);
            $response->setHttpHeader('Content-Encoding', $encoding);
          }
          // not implemented type of compression, see getClientEncoding()
          catch(LogicException $e)
          {
          }
        }
      }
    }
  }

  /**
   * Can the response be compressed? The output can be compress when
   * following conditions are met:
   *
   * 1) The `compress` parameter must be `true`
   * 2) No output was found in the output buffer
   *
   * @return boolean
   */
  protected function canCompress()
  {
    if(!$this->getParameter('compress'))
    {
      return false;
    }

    return ob_get_length() == 0;
  }

  /**
   * Compresses the content using gzip compression
   *
   * @param string $content The content to compress
   * @param string $encoding The encoding method
   * @return string, compressed content when browser does support gzip encoding
   */
  protected function compress($content, $encoding, $level = 9)
  {
    switch($encoding)
    {
      case 'x-gzip':
      case 'gzip':
        $compressed = substr(gzcompress($content, $level), 0, -4);
        $content = "\x1f\x8b\x08\x00\x00\x00\x00\x00" . $compressed;
      break;

      case 'deflate':
        $content = gzdeflate($content, $level);
      break;

      default:
        throw new LogicException(sprintf('The encoding method "%s" is not implemented', $encoding));
    }

    return $content;
  }

  /**
   * Returns current browser support for compression gzip or x-gzip
   *
   * @return boolean|string Returns false if gzip is not supported or suppported encoding
   */
  protected function getClientEncoding()
  {
    $acceptEncoding = $this->getContext()->getRequest()->getHttpHeader('Accept-Encoding');
    $encoding = false;
    if(strpos($acceptEncoding, 'x-gzip') !== false)
    {
      $encoding = 'x-gzip';
    }
    else if(strpos($acceptEncoding, 'gzip') !== false)
    {
      $encoding = 'gzip';
    }
    else if(strpos($acceptEncoding, 'deflate') !== false)
    {
      $encoding = 'deflate';
    }
    return $encoding;
  }

  /**
   * Removes extra white space within the text.
   *
   * Trim leading white space and blank lines from html code,
   * cleaning up code and saving bandwidth. Does not
   * affect <PRE>></PRE>, <TEXTAREA></TEXTAREA> and <SCRIPT></SCRIPT> blocks.
   *
   * @param string $content The content to trim
   */
  protected function removeWhitespace($content)
  {
    // Pull out the script blocks
    preg_match_all("!<script[^>]+>.*?</script>!is", $content, $match);
    $scripts = $match[0];
    $content = preg_replace("!<script[^>]+>.*?</script>!is", '@@@SIFT:TRIM:SCRIPT@@@', $content);

    // Pull out the pre blocks
    preg_match_all("!<pre[^>]*>.*?</pre>!is", $content, $match);
    $pres = $match[0];
    $content = preg_replace("!<pre[^>]*>.*?</pre>!is", '@@@SIFT:TRIM:PRE@@@', $content);

    // Pull out the textarea blocks
    preg_match_all("!<textarea[^>]+>.*?</textarea>!is", $content, $match);
    $textareas = $match[0];
    $content = preg_replace("!<textarea[^>]+>.*?</textarea>!is", '@@@SIFT:TRIM:TEXTAREA@@@', $content);

    $content = preg_replace(array(
                "/[\r\n][\r\n\t]*[\r\n]/i",
                // strip spaces between tags
                "/(\s+)?(\<.+\>)(\s+)?/",
                // strip comments
                // "/<!--((?!-->).)*-->/",
                // get rid of mutliple spaces and replace it with one
                "/(\s){2,}/i"), array(
                chr(10), " $2 ", // add spaces !
                // '',
                ' '), $content);

    // replace script blocks
    $this->replace("@@@SIFT:TRIM:SCRIPT@@@", $scripts, $content);
    // replace pre blocks
    $this->replace("@@@SIFT:TRIM:PRE@@@", $pres, $content);
    // replace textarea blocks
    $this->replace("@@@SIFT:TRIM:TEXTAREA@@@", $textareas, $content);
    return $content;
  }

  /**
   * Replaces string within another string
   *
   * @param string $string The string
   * @param array $replace The replacement string
   * @param string $subject The subject
   */
  protected function replace($string, $replace, &$subject)
  {
    $length = strlen($string);
    $pos = 0;
    for($i = 0, $count = count($replace); $i < $count; $i++)
    {
      if(($pos = strpos($subject, $string, $pos)) !== false)
      {
        $subject = substr_replace($subject, $replace[$i], $pos, $length);
      }
      else
      {
        break;
      }
    }
  }

}
