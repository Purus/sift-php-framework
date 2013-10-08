<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCacheFilter deals with page caching and action caching.
 *
 * @package Sift
 * @subpackage filter
 */
class sfCacheFilter extends sfFilter {

  protected
    $cacheManager = null,
    $request = null,
    $response = null,
    $routing = null,
    $cache = array();

  /**
   * @see sfFilter
   */
  public function initialize(sfContext $context, $parameters = array())
  {
    parent::initialize($context, $parameters);

    $this->cacheManager = $context->getViewCacheManager();
    $this->request = $context->getRequest();
    $this->response = $context->getResponse();
    $this->routing = sfRouting::getInstance();
  }

  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain A sfFilterChain instance
   */
  public function execute(sfFilterChain $filterChain)
  {
    // execute this filter only once, if cache is set and no GET or POST parameters
    if(!sfConfig::get('sf_cache'))
    {
      $filterChain->execute();
      return;
    }

    if($this->executeBeforeExecution())
    {
      $filterChain->execute();
    }

    $this->executeBeforeRendering();
  }

  public function executeBeforeExecution()
  {
    $uri = $this->cacheManager->getCurrentCacheKey();

    if(null === $uri)
    {
      return true;
    }

    // page cache
    $cacheable = $this->cacheManager->isCacheable($uri);

    $this->log('The uri "{uri}" is {cacheable}.', sfILogger::INFO, array(
      'uri' => $uri,
      'cacheable' => $cacheable ? 'cacheable' : 'not cacheable'
    ));

    if($cacheable && $this->cacheManager->withLayout($uri))
    {
      $inCache = $this->cacheManager->getPageCache($uri);

      $this->log('The uri "{uri}" is {in_cache}.', sfILogger::INFO, array(
        'uri' => $uri,
        'in_cache' => $inCache ? 'in cache' : 'not in cache'
      ));

      $this->cache[$uri] = $inCache;
      if($inCache)
      {
        // update the local response reference with the one pulled from the cache
        $this->response = $this->context->getResponse();
        // page is in cache, so no need to run execution filter
        return false;
      }
    }
    return true;
  }

  /**
   * Executes this filter.
   */
  public function executeBeforeRendering()
  {
    // cache only 200 HTTP status
    if(200 != $this->response->getStatusCode())
    {
      return;
    }

    $uri = $this->cacheManager->getCurrentCacheKey();

    // save page in cache
    if(isset($this->cache[$uri]) && false === $this->cache[$uri])
    {
      $this->setCacheExpiration($uri);
      $this->setCacheValidation($uri);

      // set Vary headers
      foreach($this->cacheManager->getVary($uri, 'page') as $vary)
      {
        $this->response->addVaryHttpHeader($vary);
      }

      $this->cacheManager->setPageCache($uri);
    }

    // cache validation
    $this->checkCacheValidation();
  }

  /**
   * Sets cache expiration headers.
   *
   * @param string $uri An internal URI
   */
  protected function setCacheExpiration($uri)
  {
    // don't add cache expiration (Expires) if
    //   * the client lifetime is not set
    //   * the response already has a cache validation (Last-Modified header)
    //   * the Expires header has already been set
    if(!$lifetime = $this->cacheManager->getClientLifeTime($uri, 'page'))
    {
      return;
    }

    if($this->response->hasHttpHeader('Last-Modified'))
    {
      return;
    }

    if(!$this->response->hasHttpHeader('Expires'))
    {
      $this->response->setHttpHeader('Expires', $this->response->getDate(time() + $lifetime), false);
      $this->response->addCacheControlHttpHeader('max-age', $lifetime);
    }
  }

  /**
   * Sets cache validation headers.
   *
   * @param string $uri An internal URI
   */
  protected function setCacheValidation($uri)
  {
    // don't add cache validation (Last-Modified) if
    //   * the client lifetime is set (cache.yml)
    //   * the response already has a Last-Modified header
    if($this->cacheManager->getClientLifeTime($uri, 'page'))
    {
      return;
    }

    if(!$this->response->hasHttpHeader('Last-Modified'))
    {
      $this->response->setHttpHeader('Last-Modified', $this->response->getDate(time()), false);
    }

    if(sfConfig::get('sf_etag'))
    {
      $etag = '"' . md5($this->response->getContent()) . '"';
      $this->response->setHttpHeader('ETag', $etag);
    }
  }

  /**
   * Checks cache validation headers.
   */
  protected function checkCacheValidation()
  {
    // Etag support
    if(sfConfig::get('sf_etag'))
    {
      $etag = '"' . md5($this->response->getContent()) . '"';
      if($this->request->getHttpHeader('IF_NONE_MATCH') == $etag)
      {
        $this->response->setStatusCode(304);
        $this->response->setHeaderOnly(true);
        $this->log('ETag matches If-None-Match (send 304).', sfILogger::INFO);
      }
    }

    // conditional GET support
    // never in debug mode
    if($this->response->hasHttpHeader('Last-Modified') && (!sfConfig::get('sf_debug') || sfConfig::get('sf_test')))
    {
      $lastModified = $this->response->getHttpHeader('Last-Modified');
      if($this->request->getHttpHeader('IF_MODIFIED_SINCE') == $lastModified)
      {
        $this->response->setStatusCode(304);
        $this->response->setHeaderOnly(true);
        $this->log('Last-Modified matches If-Modified-Since (send 304).', sfILogger::INFO);
      }
    }
  }

}
