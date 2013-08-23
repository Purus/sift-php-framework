<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTesterViewCache implements tests for the Sift view cache manager.
 *
 * @package    Sift
 * @subpackage test
 */
class sfTesterViewCache extends sfTester
{
  protected
    $viewCacheManager = null,
    $response         = null,
    $routing          = null;

  /**
   * Prepares the tester.
   */
  public function prepare()
  {
  }

  /**
   * Initializes the tester.
   */
  public function initialize()
  {
    $this->viewCacheManager = $this->browser->getContext()->getViewCacheManager();
    $this->routing = sfRouting::getInstance();
    $this->response = $this->browser->getResponse();
  }

  /**
   * Tests if the given uri is cached.
   *
   * @param  boolean $boolean      Flag for checking the cache
   * @param  boolean $with_layout  If have or not layout
   *
   * @return sfTestFunctionalBase|sfTester
   */
  public function isCached($boolean, $with_layout = false)
  {
    return $this->isUriCached($this->viewCacheManager->getCurrentCacheKey(), $boolean, $with_layout);
  }

  /**
   * Tests if the given uri is cached.
   *
   * @param  string  $uri          Uniform resource identifier
   * @param  boolean $boolean      Flag for checking the cache
   * @param  boolean $with_layout  If have or not layout
   *
   * @return sfTestFunctionalBase|sfTester
   */
  public function isUriCached($uri, $boolean, $with_layout = false)
  {
    $cacheManager = $this->viewCacheManager;

    // check that cache is enabled
    if (!$cacheManager)
    {
      $this->tester->ok(!$boolean, 'cache is disabled');

      return $this->getObjectToReturn();
    }

    if ($uri == $this->viewCacheManager->getCurrentCacheKey())
    {
      $main = true;
      $type = $with_layout ? 'page' : 'action';
    }
    else
    {
      $main = false;
      $type = $uri;
    }

    // check layout configuration
    if ($cacheManager->withLayout($uri) && !$with_layout)
    {
      $this->tester->fail('cache without layout');
      $this->tester->skip('cache is not configured properly', 2);
    }
    else if (!$cacheManager->withLayout($uri) && $with_layout)
    {
      $this->tester->fail('cache with layout');
      $this->tester->skip('cache is not configured properly', 2);
    }
    else
    {
      $this->tester->pass('cache is configured properly');
      $this->tester->diag(sprintf('cache %s layout', $with_layout ? 'with' : 'without'));

      // check page is cached
      $ret = $this->tester->is($cacheManager->has($uri), $boolean, sprintf('"%s" %s in cache', $type, $boolean ? 'is' : 'is not'));

      // check that the content is ok in cache
      if ($boolean)
      {
        if (!$ret)
        {
          $this->tester->fail('content in cache is ok');
        }
        else if($with_layout)
        {
          $response = unserialize($cacheManager->get($uri));
          $content = $response->getContent();
          $this->tester->ok($content == $this->response->getContent(), 'content in cache is ok');
        }
        else
        {
          $ret = unserialize($cacheManager->get($uri));
          $content = $ret['content'];
          $this->tester->ok(false !== strpos($this->response->getContent(), $content), 'content in cache is ok');
        }
      }
    }

    return $this->getObjectToReturn();
  }
}
