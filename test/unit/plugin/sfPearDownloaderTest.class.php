<?php

/**
 * sfPearDownloaderTest is a class to be able to test a PEAR channel without the HTTP layer.
 *
 */
class sfPearDownloaderTest extends sfPearDownloader {

  /**
   * @see PEAR_REST::downloadHttp()
   */
  public function downloadHttp($url, &$ui, $save_dir = '.', $callback = null, $lastmodified = null, $accept = false, $channel = false)
  {
    try
    {
      $file = sfPluginTestHelper::convertUrlToFixture($url);
    }
    catch(sfException $e)
    {
      return PEAR::raiseError($e->getMessage());
    }

    if($lastmodified === false || $lastmodified)
    {
      return array($file, 0, array());
    }

    return $file;
  }

}
