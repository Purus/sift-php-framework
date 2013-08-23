<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfViewConfigHandler allows you to configure views.
 *
 * @package    Sift
 * @subpackage config
 */
class sfViewConfigHandler extends sfYamlConfigHandler {

  /**
   * Executes this configuration handler.
   *
   * @param array An array of absolute filesystem path to a configuration file
   *
   * @return string Data to be written to a cache file
   *
   * @throws sfConfigurationException If a requested configuration file does not exist or is not readable
   * @throws sfParseException If a requested configuration file is improperly formatted
   * @throws sfInitializationException If a view.yml key check fails
   */
  public function execute($configFiles)
  {
    // set our required categories list and initialize our handler
    $categories = array('required_categories' => array());
    $this->initialize($categories);

    $this->mergeConfig(self::parseYamls($configFiles));

    // init our data array
    $data = array();

    $data[] = "\$context  = sfContext::getInstance();\n";
    $data[] = "\$response = \$context->getResponse();\n\n";

    // first pass: iterate through all view names to determine the real view name
    $first = true;
    foreach($this->yamlConfig as $viewName => $values)
    {
      if($viewName == 'all')
      {
        continue;
      }

      $data[] = ($first ? '' : 'else ') . "if(\$this->actionName.\$this->viewName == '$viewName')\n" .
              "{\n";
      $data[] = $this->addTemplate($viewName);
      $data[] = "}\n";

      $first = false;
    }

    // general view configuration
    $data[] = ($first ? '' : "else\n{") . "\n";
    $data[] = $this->addTemplate($viewName);
    $data[] = ($first ? '' : "}") . "\n\n";

    // second pass: iterate through all real view names
    $first = true;
    foreach($this->yamlConfig as $viewName => $values)
    {
      if($viewName == 'all')
      {
        continue;
      }

      $data[] = ($first ? '' : 'else ') . "if(\$templateName.\$this->viewName == '$viewName')\n" .
              "{\n";

      $data[] = $this->addLayout($viewName);
      $data[] = $this->addComponentSlots($viewName);
      $data[] = $this->addHtmlHead($viewName);
      $data[] = $this->addBodyAttributes($viewName);
      $data[] = $this->addEscaping($viewName);
      $data[] = $this->addHtmlAsset($viewName);

      $data[] = "}\n";

      $first = false;
    }

    // general view configuration
    $data[] = ($first ? '' : "else\n{") . "\n";

    $data[] = $this->addLayout();
    $data[] = $this->addHelpers();
    $data[] = $this->addComponentSlots();
    $data[] = $this->addHtmlHead();
    $data[] = $this->addBodyAttributes();
    $data[] = $this->addEscaping();
    $data[] = $this->addHtmlAsset();
    $data[] = ($first ? '' : "}") . "\n";

    // compile data
    $retval = sprintf("<?php\n" .
            "// auto-generated by sfViewConfigHandler\n" .
            "// date: %s\n%s\n", date('Y/m/d H:i:s'), implode('', $data));

    return $retval;
  }

  /**
   * Add new functionality (body classes, body id, body onload events, and body onunload events)
   *
   * @param string name of the current view
   * @return string
   *
   */
  private function addBodyAttributes($viewName = '')
  {
    $data = array();
    $omit = array();
    $delete_all = false;

    // Populate $body_classes with the values from ONLY the current view
    $body_classes = $this->getConfigValue('body_class', $viewName);

    // check the type and throw exception only in dev mode
    if((!is_null($body_classes) && !is_array($body_classes)) && sfConfig::get('sf_environment') == 'dev')
    {
      throw new sfConfigurationException(sprintf(
                      'Body classes has been misconfigured. "body_class" setting should be an array. "%s" given for view: "%s"', gettype($body_classes), $viewName
      ));
    }

    // If we find results from the view, check to see if there is a '-*'
    // This indicates that we will remove ALL classes EXCEPT for those passed in the current view
    if(is_array($body_classes) AND in_array('-*', $body_classes))
    {
      $delete_all = true;
      foreach($body_classes as $body_class)
      {
        if(substr($body_class, 0, 1) != '-')
        {
          $omit[] = $body_class;
        }
      }
    }

    $body_classes = $this->mergeConfigValue('body_class', $viewName);

    if(is_array($body_classes))
    {
      // remove body_classes marked with a beginning '-'
      // We exclude any body_classes that were omitted above
      $delete = array();

      foreach($body_classes as $body_class)
      {
        if(!in_array($body_class, $omit) && (substr($body_class, 0, 1) == '-' || $delete_all == true))
        {
          $delete[] = $body_class;
          $delete[] = substr($body_class, 1);
        }
      }
      $body_classes = array_diff($body_classes, $delete);
      $body_classes = array_unique($body_classes);
      foreach($body_classes as $body_class)
      {
        if($body_class)
        {
          $data[] = sprintf("  \$response->addBodyClass('%s');", $body_class);
        }
      }
    }

    // Populate $body_onloads with the values from ONLY the current view
    $body_onloads = $this->getConfigValue('body_onload', $viewName);

    // If we find results from the view, check to see if there is a '-*'
    // This indicates that we will remove ALL javascripts EXCEPT for those passed in the current view
    if(is_array($body_onloads) AND in_array('-*', $body_onloads))
    {
      $delete_all = true;
      foreach($body_onloads as $body_onload)
      {
        if(substr($body_onload, 0, 1) != '-')
        {
          $omit[] = $body_onload;
        }
      }
    }

    $body_onloads = $this->mergeConfigValue('body_onload', $viewName);
    if(is_array($body_onloads))
    {
      // remove body_onloads marked with a beginning '-'
      // We exclude any body_onloads that were omitted above
      $delete = array();

      foreach($body_onloads as $body_onload)
      {
        if(!in_array($body_onload, $omit) && (substr($body_onload, 0, 1) == '-' || $delete_all == true))
        {
          $delete[] = $body_onload;
          $delete[] = substr($body_onload, 1);
        }
      }
      $body_onloads = array_diff($body_onloads, $delete);
      $body_onloads = array_unique($body_onloads);
      foreach($body_onloads as $body_onload)
      {
        if($body_onload)
        {
          $data[] = sprintf("  \$response->addBodyOnload('%s');", $body_onload);
        }
      }
    }

    $id = $this->getconfigValue('body_id', $viewName);
    if($id)
    {
      $data[] = "  \$response->setBodyId('$id', false);";
    }
    return implode("\n", $data) . "\n";
  }

  /**
   * Merges assets and environement configuration.
   *
   * @param array A configuration array
   */
  protected function mergeConfig($myConfig)
  {
    // merge javascripts and stylesheets
    $myConfig['all']['stylesheets'] = array_merge(isset($myConfig['default']['stylesheets']) && is_array($myConfig['default']['stylesheets']) ? $myConfig['default']['stylesheets'] : array(), isset($myConfig['all']['stylesheets']) && is_array($myConfig['all']['stylesheets']) ? $myConfig['all']['stylesheets'] : array());
    unset($myConfig['default']['stylesheets']);

    $myConfig['all']['javascripts'] = array_merge(isset($myConfig['default']['javascripts']) && is_array($myConfig['default']['javascripts']) ? $myConfig['default']['javascripts'] : array(), isset($myConfig['all']['javascripts']) && is_array($myConfig['all']['javascripts']) ? $myConfig['all']['javascripts'] : array());
    unset($myConfig['default']['javascripts']);

    // merge default and all
    $myConfig['all'] = sfToolkit::arrayDeepMerge(
                    isset($myConfig['default']) && is_array($myConfig['default']) ? $myConfig['default'] : array(), isset($myConfig['all']) && is_array($myConfig['all']) ? $myConfig['all'] : array()
    );

    unset($myConfig['default']);

    $this->yamlConfig = $myConfig;
  }

  /**
   * Adds a component slot statement to the data.
   *
   * @param string The view name
   *
   * @return string The PHP statement
   */
  protected function addComponentSlots($viewName = '')
  {
    $data = array();

    $components = $this->mergeConfigValue('components', $viewName);
    foreach($components as $name => $component)
    {
      if(!is_array($component) || count($component) < 1)
      {
        $component = array();
      }
      $data[] = sprintf("  \$this->setComponentSlot('%s', %s);", $name, $this->varExport($component));
    }
    return join("\n", $data) . "\n";
  }

  /**
   * Adds a template setting statement to the data.
   *
   * @param string The view name
   *
   * @return string The PHP statement
   */
  protected function addTemplate($viewName = '')
  {
    $data = '';

    $templateName = $this->getConfigValue('template', $viewName);
    $defaultTemplateName = $templateName ? "'$templateName'" : '$this->actionName';

    $data .= "  \$templateName = \$response->getParameter(\$this->moduleName.'_'.\$this->actionName.'_template', $defaultTemplateName, 'sift/action/view');\n";
    $data .= "  \$this->setTemplate(\$templateName.\$this->viewName.\$this->getExtension());\n";

    return $data;
  }

  /**
   * Adds a layout statement statement to the data.
   *
   * @param string The view name
   *
   * @return string The PHP statement
   */
  protected function addLayout($viewName = '')
  {
    // true if the user set 'has_layout' to true or set a 'layout' name for this specific action
    $hasLocalLayout = isset($this->yamlConfig[$viewName]['layout']) || (isset($this->yamlConfig[$viewName]) && array_key_exists('has_layout', $this->yamlConfig[$viewName]));

    // the layout value
    $layout = $this->getConfigValue('has_layout', $viewName) ? $this->getConfigValue('layout', $viewName) : false;

    //$this->getResponse()->setParameter($this->getModuleName().'_'.$this->getActionName().'_layout', $name, 'sift/action/view');
    // the user set a decorator in the action
    $data = <<<EOF

  if(null !== (\$layout = sfConfig::get('sift.view.'.\$this->moduleName.'_'.\$this->actionName.'_layout')))
  {
    \$this->setDecoratorTemplate(false === \$layout ? false : \$layout.\$this->getExtension());
  }
EOF;

    if($hasLocalLayout)
    {
      // the user set a decorator in view.yml for this action
      $data .= <<<EOF

  else
  {
    \$this->setDecoratorTemplate('' == '$layout' ? false : '$layout'.\$this->getExtension());
  }

EOF;
    }
    else
    {
      // no specific configuration
      // set the layout to the 'all' view.yml value except if:
      //   * the decorator template has already been set by "someone" (via view.configure_format for example)
      //   * the request is an XMLHttpRequest request
      $data .= <<<EOF

  else if (null === \$this->getDecoratorTemplate() && !\$this->context->getRequest()->isXmlHttpRequest())
  {
    \$this->setDecoratorTemplate('' == '$layout' ? false : '$layout'.\$this->getExtension());
  }

EOF;
    }

    return $data;
  }

  /**
   * Adds http metas and metas statements to the data.
   *
   * @param string The view name
   *
   * @return string The PHP statement
   */
  protected function addHtmlHead($viewName = '')
  {
    $data = array();

    foreach($this->mergeConfigValue('http_metas', $viewName) as $httpequiv => $content)
    {
      $data[] = sprintf("  \$response->addHttpMeta('%s', '%s', false);", $httpequiv, str_replace('\'', '\\\'', $content));
    }

    foreach($this->mergeConfigValue('metas', $viewName) as $name => $content)
    {
      if($name == 'title')
      {
        $data[] = sprintf("  \$response->setTitle('%s', true, false, true);", str_replace('\'', '\\\'', preg_replace('/&amp;(?=\w+;)/', '&', $content)));
      }
      else
      {
        $data[] = sprintf("  \$response->addMeta('%s', '%s', false, false);", $name, str_replace('\'', '\\\'', preg_replace('/&amp;(?=\w+;)/', '&', htmlspecialchars($content, ENT_QUOTES, sfConfig::get('sf_charset')))));
      }
    }

    $title = $this->getConfigValue('title', $viewName);
    if(!empty($title))
    {
      $data[] = sprintf("  \$response->setTitle('%s', true, false, true);", str_replace('\'', '\\\'', preg_replace('/&amp;(?=\w+;)/', '&', $title)));
    }

    return implode("\n", $data) . "\n";
  }

  /**
   * Adds stylesheets and javascripts statements to the data.
   *
   * @param string The view name
   *
   * @return string The PHP statement
   */
  protected function addHtmlAsset($viewName = '')
  {
    $data = array();

    $packages = $this->mergeConfigValue('asset_packages', $viewName);

    $packageStylesheets = array();
    $packageJavascripts = array();

    foreach($packages as $package)
    {
      $packageStylesheets = array_merge($packageStylesheets, sfAssetPackage::getStylesheets($package));
      $packageJavascripts = array_merge($packageJavascripts, sfAssetPackage::getJavascripts($package));
    }

    // Merge the current view's stylesheets with the app's default stylesheets
    $stylesheets = $this->mergeConfigValue('stylesheets', $viewName);
    $stylesheets = array_merge($packageStylesheets, $stylesheets);

    $tmp = array();
    foreach((array) $stylesheets as $css)
    {
      $position = '';
      if(is_array($css))
      {
        $key = key($css);
        $options = $css[$key];
        if(isset($options['position']))
        {
          $position = $options['position'];
          unset($options['position']);
        }
      }
      else
      {
        $key = $css;
        $options = array();
      }

      $key = $this->replaceConstants($key);

      if('-*' == $key)
      {
        $tmp = array();
      }
      else if('-' == $key[0])
      {
        unset($tmp[substr($key, 1)]);
      }
      else
      {
        $tmp[$key] = sprintf("  \$response->addStylesheet('%s', '%s', %s);", $key, $position, str_replace("\n", '', var_export($options, true)));
      }
    }

    $data = array_merge($data, array_values($tmp));

    // Populate $javascripts with the values from ONLY the current view
    $javascripts = $this->mergeConfigValue('javascripts', $viewName);
    $javascripts = array_merge($packageJavascripts, $javascripts);

    $tmp = array();
    foreach((array) $javascripts as $js)
    {
      $position = '';
      if(is_array($js))
      {
        $key = key($js);
        $options = $js[$key];
        if(isset($options['position']))
        {
          $position = $options['position'];
          unset($options['position']);
        }
      }
      else
      {
        $key = $js;
        $options = array();
      }

      $key = $this->replaceConstants($key);

      if('-*' == $key)
      {
        $tmp = array();
      }
      elseif('-' == $key[0])
      {
        unset($tmp[substr($key, 1)]);
      }
      else
      {
        $tmp[$key] = sprintf("  \$response->addJavascript('%s', '%s', %s);", $key, $position, str_replace("\n", '', var_export($options, true)));
      }
    }

    $data = array_merge($data, array_values($tmp));

    return implode("\n", $data) . "\n";
  }

  /**
   * Adds an escaping statement to the data.
   *
   * @param string The view name
   *
   * @return string The PHP statement
   */
  protected function addEscaping($viewName = '')
  {
    $data = array();

    $escaping = $this->getConfigValue('escaping', $viewName);

    if(isset($escaping['strategy']))
    {
      $data[] = sprintf("  \$this->setEscaping(%s);", var_export($escaping['strategy'], true));
    }

    if(isset($escaping['method']))
    {
      $data[] = sprintf("  \$this->setEscapingMethod(%s);", var_export($escaping['method'], true));
    }

    return implode("\n", $data) . "\n";
  }

  /**
   * Adds helpers to be loaded.
   *
   * @param string The view name
   *
   * @return string The PHP statement
   */
  protected function addHelpers($viewName = '')
  {
    $data = array();

    $helpers = $this->getConfigValue('helpers', $viewName);

    if($helpers)
    {
      $data[] = sprintf("  \$this->addHelpers(%s);", var_export($helpers, true));

      return implode("\n", $data) . "\n";
    }

    return '';
  }

}
