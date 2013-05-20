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
   * Cache holder of path aliases
   *
   * @var array
   */
  protected $pathAliases;

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
   */
  protected function handleJavascripts()
  {
    $context = $this->getContext();
    $response = $context->getResponse();
    $request = $context->getRequest();
    $invalid = $valid = $already_seen = array();
    $baseDomain = sfConfig::get('sf_base_domain');
    $lastModified = 0;
    $lastModifiedPreserved = array();

    foreach(array('first', '', 'last') as $position)
    {
      $valid[$position] = array();
      $invalid[$position] = array();
      $preserved[$position] = array();

      foreach($response->getJavascripts($position) as $files => $options)
      {
        if(!is_array($files))
        {
          $files = array($files);
        }

        foreach($files as $file)
        {
          // generated script
          if(!isset($options['generated']) && !$options['generated'])
          {
            $file = javascript_path($file);
          }

          if(isset($already_seen[$file]))
          {
            continue;
          }

          $already_seen[$file] = true;

          // can be packaged?
          if(!$this->canBePackaged($file, $options, $baseDomain))
          {
            $invalid[$position][$file] = $options;
          }
          else
          {
            $file = $this->stripBaseDomain($file, $baseDomain);
            $filePath = $this->getFilePath($file);

            if(!is_readable($filePath))
            {
              $this->log(sprintf('File "%s" is not readable or does not exist.', $file));
              // mark as invalid
              $invalid[$position][$file] = $options;
              continue;
            }

            // we have some options, dont touch it
            if(count($options))
            {
              $preserved[$position][$file] = $options;
              $lastModifiedPreserved[$position][$file] = filemtime($filePath);
            }
            else
            {
              $valid[$position][] = $file;
              $lastModified = max($lastModified, filemtime($filePath));
            }
          }
        }
      }
    }

    // remove all scripts from response
    $response->clearJavascripts();

    $root = $request->getRelativeUrlRoot();

    if(count($valid))
    {
      if($baseDomain)
      {
        $path = sprintf('http'.($request->isSecure() ? 's' : '') . '://' . $baseDomain .
                        '%s/min/%s/f=', ($root ? ('/'.$root) : ('')) , $lastModified);
      }
      else
      {
        $path = sprintf('%s/min/%s/f=', $root, $lastModified);
      }

      foreach($valid as $position => $scripts)
      {
        foreach($this->makeChunks($scripts, $path) as $javascripts)
        {
          if(!count($javascripts))
          {
            continue;
          }
          $response->addJavascript(sprintf('%s/min/%s/f=%s', $root, $lastModified, join(',', $javascripts)), $position);
        }
      }
    }

    foreach($invalid as $position => $scripts)
    {
      foreach($scripts as $script => $options)
      {
        $response->addJavascript($script, $position, $options);
      }
    }

    foreach($preserved as $position => $allScripts)
    {
      if(!count($allScripts))
      {
        continue;
      }

      // put the preserved back to response
      foreach($allScripts as $script => $options)
      {
        $path = sprintf('%s/min/%s/f=%s', $root, $lastModifiedPreserved[$position][$script], $script);

        if($baseDomain)
        {
          $path = 'http'.($request->isSecure() ? 's' : '') . '://' . $baseDomain . $path;
        }

        $response->addJavascript($path, $position, $options);
      }
    }

  }

  /**
   * Handles stylesheets
   *
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
          if(isset($options['generated']))
          {
            $file = _dynamic_path($file);
          }
          else
          {
            // less support
            if(isset($options['less']) || preg_match('/\.less$/i', $file))
            {
              $file = sfLessCompiler::getInstance()->compileStylesheetIfNeeded(
                      stylesheet_path($file)
              );
              unset($options['less']);
            }
            else
            {
              $file = stylesheet_path($file);
            }
          }

          if(isset($already_seen[$file]))
          {
            continue;
          }

          $already_seen[$file] = true;

          if(!$this->canBePackaged($file))
          {
            $invalid[$position][$file] = $options;
          }
          else
          {
            $file = $this->stripBaseDomain($file, $baseDomain);
            $filePath = $this->getFilePath($file);

            if(!is_readable($filePath))
            {
              $invalid[$position][$file] = $options;

              $this->log(sprintf('File "%s" is not readable or does not exist.', $file));
              continue;
            }

            if(isset($options['media']))
            {
              $media = trim($options['media']);
            }
            else
            {
              // we assume when no media set, use for all except print!
              $media = $this->getParameter('default_stylesheet_media', 'screen,projection,tv');
            }

            // we preserve stylesheets with ids or ie conditions!
            if(isset($options['id']) || isset($options['ie_condition']))
            {
              $preserved[$position][$file] = $options;
              $lastModifiedPreserved[$position][$file] = filemtime($filePath);
            }
            else
            {
              $valid[$position][$media][] = $file;
              if(!isset($lastModified[$position][$media]))
              {
                $lastModified[$position][$media] = 0;
              }
              $lastModified[$position][$media] = max($lastModified[$position][$media], filemtime($filePath));
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

    foreach($valid as $position => $allStylesheets)
    {
      foreach($allStylesheets as $media => $stylesheets)
      {
        if(!count($stylesheets))
        {
          continue;
        }

        if($baseDomain)
        {
          $path = sprintf('http'.($request->isSecure() ? 's' : '') . '://' .
                  $baseDomain . $relativeUrlRoot . '/min/%s/f=', $lastModified[$position][$media]);
        }
        else
        {
          $path = sprintf('%s/min/%s/f=', $relativeUrlRoot, $lastModified[$position][$media]);
        }

        foreach($this->makeChunks($stylesheets, $path) as $chunk)
        {
          $response->addStylesheet(sprintf('%s%s', $path, join(',', $chunk)), $position, array('media' => $media));
        }
      }
    }

    foreach($preserved as $position => $allStylesheets)
    {
      // put the preserved back to response
      foreach($allStylesheets as $stylesheet => $options)
      {
        $path = sprintf('%s/min/%s/f=%s', $relativeUrlRoot, $lastModifiedPreserved[$position][$stylesheet], $stylesheet);

        if($baseDomain)
        {
          $path = 'http'.($request->isSecure() ? 's' : '') . '://' . $baseDomain . $path;
        }

        $response->addStylesheet($path, $position, $options);
      }
    }
  }

  /**
   * Make chunks of array to length limit of 2083 chars
   *
   * @param array $array Array to ochunk
   * @param string $path Path
   * @return array
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

  /**
   * Parses url. Handles protocol less urls.
   *
   * @param string $url
   * @return array
   * @see parse_url
   */
  protected function parseUrl($url)
  {
    // this is a protocol less url
    if(preg_match('~^//~', $url))
    {
      $url = sprintf('http:%s', $url);
    }
    return parse_url($url);
  }

  /**
   * Returns path to a file on the disk. Does not check if file exists.
   *
   * @param string $file Web path to a file
   * @return string Path to a file on the disk
   */
  protected function getFilePath($file)
  {
    $filePath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $file;
    $relativeUrlRoot = $this->getContext()->getRequest()->getRelativeUrlRoot();

    // check path aliases
    foreach($this->getPathAliases() as $alias => $path)
    {
      if(!$path)
      {
        continue;
      }

      if(preg_match(sprintf('#^%s%s#', $relativeUrlRoot, preg_quote($alias, '#')), $file))
      {
        $filePath = $path . '/' . ltrim($file, '/');
        break;
      }
    }

    return $filePath;
  }

  /**
   * Returns an array of path aliases
   *
   * @return array
   */
  protected function getPathAliases()
  {
    if(!$this->pathAliases)
    {
      $aliases = $this->getParameter('path_aliases', array());
      $aliases = sfCore::filterByEventListeners($aliases, 'filter.asset_packager.path_aliases');
      $this->pathAliases = $aliases;
    }

    return $this->pathAliases;
  }

  /**
   * Strips base domain from the file web path
   *
   * @param string $file Path to a file (accesed from web)
   * @param string $baseDomain Base domain
   * @return string
   */
  protected function stripBaseDomain($file, $baseDomain)
  {
    if(empty($baseDomain))
    {
      return $file;
    }

    return preg_replace(sprintf('~(https?://)+%s~', $baseDomain), '', $file);
  }

  /**
   * Can the $file be packaged?
   *
   * @param string $file
   * @param array $options Options for the asset
   * @param string $baseDomain Base domain
   * @return boolean
   */
  protected function canBePackaged($file, $options = array(), $baseDomain = '')
  {
    // generated?
    if(isset($options['generated']) && $options['generated'])
    {
      return false;
    }

    $request = $this->getContext()->getRequest();

    $url = $this->parseUrl($file);
    // this file is not possible to minify
    // its either:
    // * generated by the application
    // * from different host
    if((isset($url['host']) && $url['host'] != $request->getHost() && $url['host'] != $baseDomain))
    {
      return false;
    }

    return true;
  }

  /**
   * Logs a message to logger instance (only if enabled via configuration)
   *
   * @param string $message Message to log
   * @param string $priority Priority of the log entry
   */
  protected function log($message, $priority = sfLogger::ERR)
  {
    if(sfConfig::get('sf_logging_enabled'))
    {
      sfLogger::getInstance()->log(sprintf('{sfAssesPackager} %s', $message), $priority);
    }
  }

}
