<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Finds non "i18n ready" strings in an application.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliI18nFindTask extends sfCliBaseTask {

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
        new sfCliCommandArgument('application', sfCliCommandArgument::REQUIRED, 'The application name'),
    ));

    $this->addOptions(array(
        new sfCliCommandOption('env', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'i18n';
    $this->name = 'find';
    $this->briefDescription = 'Finds non "i18n ready" strings in an application templates';

    $scriptName = $this->environment->get('script_name');
    
    $this->detailedDescription = <<<EOF
The [i18n:find|INFO] task finds non internationalized strings embedded in templates:

  [{$scriptName} i18n:find frontend|INFO]

This task is able to find non internationalized strings in pure HTML and in PHP code:

  <p>Non i18n text</p>
  <p><?php echo 'Test' ?></p>

As the task returns all strings embedded in PHP, you can have some false positive (especially
if you use the string syntax for helper arguments).
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {    
    $application = $arguments['application'];
    
    $this->checkAppExists($application);
    
    $this->logSection($this->getFullName(), sprintf('Find non "i18n ready" strings in the "%s" application', $application));

    // Look in templates
    $dirs = array();
    $moduleNames = sfFinder::type('dir')->maxdepth(0)->relative()
            ->in($this->environment->get('sf_apps_dir') . '/' . $application . '/modules');
    
    foreach($moduleNames as $moduleName)
    {
      $dirs[] = $this->environment->get('sf_apps_dir') . '/' . 
                $application . '/' . $this->environment->get('sf_app_module_dir_name') . '/' . 
                $moduleName . '/templates';
    }
    
    $dirs[] = $this->environment->get('sf_apps_dir') . '/' . $application .  '/templates';
    

    $strings = array();
    foreach($dirs as $dir)
    {
      $templates = sfFinder::type('file')->name('*.php')->in($dir);
      foreach($templates as $template)
      {
        if(!isset($strings[$template]))
        {
          $strings[$template] = array();
        }

        $content = file_get_contents($template);
        // remove doctype        
        $content = preg_replace('/<!DOCTYPE.*?>/', '', $content);
        
        $dom = new DomDocument('1.0', $this->environment->get('sf_charset', 'UTF-8'));
        // $dom = new DomDocument();
        //libxml_use_internal_errors(true);        
        @$dom->loadXML('<doc>' . $content . '</doc>');
        // libxml_clear_errors();
        
        $nodes = array($dom);
        while($nodes)
        {
          $node = array_shift($nodes);

          if(XML_TEXT_NODE === $node->nodeType)
          {
            if(!$node->isWhitespaceInElementContent())
            {
              $strings[$template][] = $node->nodeValue;
            }
          }
          else if($node->childNodes)
          {
            for($i = 0, $max = $node->childNodes->length; $i < $max; $i++)
            {
              $nodes[] = $node->childNodes->item($i);
            }
          }
          else if('DOMProcessingInstruction' == get_class($node) && 'php' == $node->target)
          {
            // processing instruction node
            $tokens = token_get_all('<?php ' . $node->nodeValue);
            foreach($tokens as $token)
            {
              if(is_array($token))
              {
                list($id, $text) = $token;
                // this is a call to php function!
                if(T_CONSTANT_ENCAPSED_STRING === $id)
                {
                  // $strings[$template][] = substr($text, 1, -1);
                }
              }
            }
          }
        }
      }
    }

    foreach($strings as $template => $messages)
    {
      if(!$messages)
      {
        continue;
      }

      $this->logSection($this->getFullName(), sprintf('strings in "%s"', str_replace(str_replace(DIRECTORY_SEPARATOR, '/', $this->environment->get('sf_root_dir') . '/'), '', $template)), 1000);
      
      foreach($messages as $message)
      {
        $message = trim($message);
        $this->log("  $message\n");
      }
    }
    
  }

}
