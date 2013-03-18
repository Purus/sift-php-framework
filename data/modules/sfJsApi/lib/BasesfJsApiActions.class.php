<?php
/*
 * This file is part of the Sift package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates javascript API
 *
 * @package Sift
 * @subpackage module
 */
class BasesfJsApiActions extends myActions {

  /**
   * Index action generates the API
   */
  public function executeIndex()
  {
    $this->setViewClass('sfJavascript');
    $this->getResponse()->setContentType('application/javascript');

    $packages = array();
    $dependencies = array();

    // get all packages
    foreach(sfAssetPackage::getAllPackages() as $name => $package)
    {
      // we skip js_api package
      if($name == 'js_api')
      {
        continue;
      }

      // get javascripts but, exclude required
      $javascripts = sfAssetPackage::getJavascripts($name, false);
      // get stylesheets but exclude required
      $stylesheets = sfAssetPackage::getStylesheets($name, false);

      $stylesheets = $this->parseAssets($stylesheets, 'css');
      $javascripts = $this->parseAssets($javascripts, 'js');

      $assets = array_merge($javascripts, $stylesheets);

      // dependencies
      if(isset($package['require']) && count($package['require']))
      {
        $dependencies[$name] = $package['require'];
      }

      $packages[$name] = $assets;
    }

    $this->packages = sfJson::encode($packages);
    $this->dependencies = sfJson::encode($dependencies);
  }

  /**
   * Parses array of asssets to be prepared for yepnope
   *
   * @param array $assets Array of assets to be parsed
   * @param string $type Type of assets. js or css
   * @return array
   */
  protected function parseAssets($assets, $type)
  {
    sfLoader::loadHelpers('Asset');

    $result = array();

    foreach($assets as $file)
    {
      if(is_array($file))
      {
        $source = key($file);
        $options = $file[$source];
      }
      else
      {
        $source = $file;
        $options = array();
      }

      $absolute = false;
      if(isset($options['absolute']))
      {
        $absolute = true;
      }

      $condition = '';
      if(isset($options['ie_condition']))
      {
        $condition = $this->parseIeCondition($options['ie_condition']);
      }

      $raw = false;
      if(isset($options['raw']))
      {
        $raw = true;
      }
      elseif(isset($options['generated']))
      {
        $file = _dynamic_path($source, $absolute);
        $raw = true;
      }

      if(!$raw)
      {
        if($type == 'js')
        {
          $file = javascript_path($source, $absolute);
        }
        elseif($type == 'css')
        {
          // FIXME: possible problems with less coming from network
          // less support
          if(isset($options['less']) || preg_match('/\.less$/i', $source))
          {
            $source = sfLessCompiler::getInstance()->compileStylesheetIfNeeded(
              stylesheet_path($source)
            );
          }
          else
          {
            $source = stylesheet_path($source, $absolute);
          }

          // mark is as css
          $source = sprintf('css!%s', $source);
        }
      }

      $position = null;
      // FIXME: reorder items?
      if(isset($options['position']))
      {
        $position = $options['position'];
      }

      $result[] = sprintf('%s%s', $condition, $source);
    }

    return $result;
  }

  /**
   * Parses IE condition to be used with YepNope IE condition plugin
   *
   * @param string $condition
   * @return string
   * @link http://yepnopejs.com/
   * @link http://msdn.microsoft.com/en-us/library/ms537512%28v=vs.85%29.aspx
   * @link http://www.quirksmode.org/css/condcom.html
   */
  protected function parseIeCondition($condition)
  {
    // Official IE condition which we need to convert to yepnope prefix like:
    // [if IE 7] -> ie7
    // [if lt IE 5] -> ielt5
    $condition = str_replace(' ', '', strtolower($condition));
    // gt: greater than [if gte IE 7]
    // lte: less than or equal to [if lte IE 7]
    if(preg_match('/(gte|lte|lt|lte)ie(\d+)/', $condition, $matches))
    {
      $version = $matches[2];
      switch($matches[1])
      {
        // lower than equal
        case 'lte':
          $condition = join('!', array(
            sprintf('ie%s', $version),
            sprintf('ielt%s', $version)
          ));
          break;

        // greater than equal
        case 'gte':
          $condition = join('!', array(
            sprintf('ie%s', $version),
            sprintf('iegt%s', $version)
          ));
          break;

        // greater than
        case 'gt':
          $condition = sprintf('iegt%s', $version);
          break;

        // lower than
        case 'lt':
          $condition = sprintf('ielt%s', $version);
          break;
      }
    }

    return sprintf('%s!', $condition);
  }

}