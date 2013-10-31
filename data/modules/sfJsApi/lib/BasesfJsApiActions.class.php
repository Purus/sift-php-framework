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
      $javascripts = sfAssetPackage::getJavascripts($name, false, true);
      // get stylesheets but exclude required
      $stylesheets = sfAssetPackage::getStylesheets($name, false, true);

      $stylesheets = $this->parseAssets($stylesheets, 'css');
      $javascripts = $this->parseAssets($javascripts, 'js');

      $assets = array_merge($stylesheets, $javascripts);

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
   * Form setup action. This is application specific.
   * Generates javascript code to make the form inputs rich.
   * 
   * Adds datepickers to datetime inputs, make textareas
   * rich editors and so on.
   * 
   */
  public function executeFormSetup()
  {
    $this->setViewClass('sfJavascript');    
    
    $setup = '';
    $setup = sfCore::filterByEventListeners($setup, 'js.form_setup', array());
    
    if($setup)
    {
      if($setup instanceof sfCallable)
      {
        return $this->renderCallable($setup);
      }
      else
      {
        return $this->renderText($setup);
      }
    }
    
    // rich editor
    $this->richEditorOptions = $this->getRichEditorOptions();
  }
  
  /**
   * Returns an array iof rich editor options which will be exported to javascript
   * configuration
   * 
   * @return array
   */
  protected function getRichEditorOptions()
  {
    $editor = sfRichTextEditor::factory(
      sfConfig::get('sf_rich_text_editor.driver', 'CKEditor'),
      sfConfig::get('sf_rich_text_editor.options', array())
      );
    return $editor->getOptionsForJavascript();
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
    sfLoader::loadHelpers(array('Asset', 'Url'));

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
        $source = _dynamic_path($source, $absolute);
        $raw = true;
      }

      if(!$raw)
      {
        if($type == 'js')
        {
          $source = javascript_path($source, $absolute);
        }
        elseif($type == 'css')
        {
          // FIXME: possible problems with less coming from network
          // less support
          if(isset($options['less']) || preg_match('/\.less$/i', $source))
          {
            $source = stylesheet_path($source);

            // is base domain is affecting the path, we need to take care of it
            if($baseDomain = sfConfig::get('sf_base_domain'))
            {
              $source = preg_replace(sprintf('~https?://%s%s~', $baseDomain,
                  $this->getContext()->getRequest()->getRelativeUrlRoot()), '', $source);
            }

            $source = $this->getContext()->getService('less_compiler')->compileStylesheetIfNeeded($source);

            if($baseDomain)
            {
              $source = stylesheet_path($source);
            }
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
