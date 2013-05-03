<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAssetPackagerFilter class handles compression of stylesheets and
 * javascript
 *
 * @package    Sift
 * @subpackage filter
 */
class sfAssetPackagerFilter extends sfFilter {

  /**
   * Executes the filter
   *
   * @param sfFilterChain $filterChain
   */
  public function execute($filterChain)
  {
    $filterChain->execute();

    $response = $this->getContext()->getResponse();
    $request  = $this->getContext()->getRequest();

    if($this->isFirstCall()
        && !$request->isAjax()
        && preg_match('|text/html|', $response->getContentType()))
    {
      sfLoader::loadHelpers(array('Tag', 'Asset'));
      $this->handleStylesheets();
      $this->handleJavascripts();
    }
  }

  /**
   * Handles javascript files
   *
   * @todo Implement caching
   */
  protected function handleJavascripts()
  {
    $context = $this->getContext();
    $response = $context->getResponse();
    $request = $context->getRequest();
    $invalid = $valid = $already_seen = array();
    $baseDomain = sfConfig::get('sf_base_domain');

    $lastModified = 0;

    foreach(array('first', '', 'last') as $position)
    {
      $invalid[$position] = array();

      foreach($response->getJavascripts($position) as $files => $options)
      {
        if(!is_array($files))
        {
          $files = array($files);
        }
        foreach($files as $file)
        {
          $file = javascript_path($file);
          if(isset($already_seen[$file]))
          {
            continue;
          }

          $already_seen[$file] = true;
          $url = $this->parseUrl($file);

          // javascript generated with application should have attribute "generated: true"
          if((isset($options['generated']) && $options['generated']))
          {
            unset($options['generated']);
            $invalid[$position][] = $file;
          }
          elseif((isset($url['host']) && $url['host'] != $request->getHost() && $url['host'] != $baseDomain)
             || preg_match('|^/sf|', $file)
             || preg_match('/\.php/i', $file)) // dynamically generated scripts
          {
            $invalid[$position][] = $file;
          }
          else
          {
            if($baseDomain)
            {
              $file = preg_replace(sprintf('~(https?://)+%s~', $baseDomain), '', $file);
            }

            $filePath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $file;

            if(sfConfig::get('sf_logging_enabled')
                    && !is_readable($filePath))
            {

              sfContext::getInstance()->getLogger()->err(sprintf('{sfAssetPackagerFilter} File "%s" is not readable or does not exist.', $file));
              continue;
            }

            $lastModified = max($lastModified, filemtime($filePath));
            $valid[] = $file;
          }
        }
      }
    }

    // remove all scripts from response
    $response->clearJavascripts();

    foreach($invalid as $position => $scripts)
    {
      foreach($scripts as $script)
      {
        $response->addJavascript($script, $position);
      }
    }

    if(count($valid))
    {
      $root = $request->getRelativeUrlRoot();

      if($baseDomain)
      {
        $path = sprintf('http'.($request->isSecure() ? 's' : '') . '://' . $baseDomain .
                        '%s/min/%s/f=', ($root ? ('/'.$root) : ('')) , $lastModified);
      }
      else
      {
        $path = sprintf('%s/min/%s/f=', $root, $lastModified);
      }

      foreach($this->makeChunks($valid, $path) as $javascripts)
      {
        // make chunks that are
        $response->addJavascript(sprintf('%s/min/%s/f=%s', $root, $lastModified, join(',', $javascripts)));
      }
    }
  }

  /**
   *
   *
   * @todo Implement caching
   */
  protected function handleStylesheets()
  {
    $context  = $this->getContext();
    $response = $context->getResponse();
    $request  = $context->getRequest();

    $invalid = $valid = $preserved = $already_seen = array();

    $baseDomain   = sfConfig::get('sf_base_domain');
    $lastModified = array();
    $lastModifiedPreserved = array();

    foreach(array('first', '', 'last') as $position)
    {
      $invalid[$position] = array();

      foreach($response->getStylesheets($position) as $files => $options)
      {
        if(!is_array($files))
        {
          $files = array($files);
        }

        foreach($files as $file)
        {
          $file = stylesheet_path($file);
          if(isset($already_seen[$file]))
          {
            continue;
          }

          $already_seen[$file] = true;
          $url = $this->parseUrl($file);

          if((isset($url['host']) && $url['host'] != $request->getHost() && $url['host'] != $baseDomain)
             || preg_match('|^/sf|', $file))
          {
            $invalid[$position][$file] = $options;
          }
          else
          {
            if($baseDomain)
            {
              $file = preg_replace(sprintf('~(https?://)+%s~', $baseDomain), '', $file);
            }

            $filePath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $file;

            if(sfConfig::get('sf_logging_enabled')
                    && !is_readable($filePath))
            {
              sfContext::getInstance()->getLogger()->err(sprintf('{sfAssetPackagerFilter} File "%s" is not readable or does not exist.', $file));
              continue;
            }

            if(isset($options['media']))
            {
              $media = trim($options['media']);
            }
            else
            {
              // we assume when no media set, use for all except print!
              // FIXME: make configurable
              $media = 'screen,projection,tv';
            }

            // we preserve stylesheets with ids!
            // this is used by myThemePlugin to switch it on fly with jquery
            if(isset($options['id']))
            {
              $preserved[$file] = $options;
              $lastModifiedPreserved[$file] = filemtime($filePath);
            }
            else
            {
              $valid[$media][] = $file;
              if(!isset($lastModified[$media]))
              {
                $lastModified[$media] = 0;
              }
              $lastModified[$media] = max($lastModified[$media], filemtime($filePath));
            }
          }
        }
      }
    }

    $response->clearStylesheets();

    $relativeUrlRoot = $this->getContext()->getRequest()->getRelativeUrlRoot();

    foreach($invalid as $position => $stylesheets)
    {
      foreach($stylesheets as $stylesheet => $options)
      {
        $response->addStylesheet($stylesheet, $position, $options);
      }
    }

    foreach($valid as $media => $stylesheets)
    {
      if(count($stylesheets))
      {
        if($baseDomain)
        {
          $path = sprintf('http'.($request->isSecure() ? 's' : '') . '://' .
                  $baseDomain . $relativeUrlRoot . '/min/%s/f=', $lastModified[$media]);
        }
        else
        {
          $path = sprintf('%s/min/%s/f=', $relativeUrlRoot, $lastModified[$media]);
        }

        foreach($this->makeChunks($stylesheets, $path) as $chunk)
        {
          $response->addStylesheet(sprintf($path.'%s', join(',', $chunk)), '', array('media' => $media));
        }
      }
    }

    // put the preserved back to response
    foreach($preserved as $script => $options)
    {
      $path = sprintf('%s/min/%s/f=%s', $relativeUrlRoot, $lastModifiedPreserved[$script], $script);

      if($baseDomain)
      {
        $path = 'http'.($request->isSecure() ? 's' : '') . '://' . $baseDomain . $path;
      }

      $response->addStylesheet($path, '', $options);
    }

  }

  /**
   * Make chunks of array to length limit of 2083 chars
   *
   * @param type $array
   * @return type array
   */
  protected function makeChunks($array, $path)
  {
    $context = $this->getContext();
    $request = $context->getRequest();
    $server = $request->getHttpHeader('server_name', '');

    $chunks = array();
    $rest   = array();

    while(($length = strlen($server) + strlen($path) + array_sum(array_map('strlen', $array))) > 2083)
    {
      $rest[] = array_pop($array);
    }

    $chunks[] = $array;

    if(count($rest))
    {
      $chunks = array_merge($chunks, $this->makeChunks($rest, $path));
    }

    return $chunks;
  }

  protected function parseUrl($url)
  {
    // this is a protocol less url
    if(preg_match('~^//~', $url))
    {
      $url = sprintf('http:%s', $url);
    }
    return parse_url($url);
  }

}
