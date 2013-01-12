<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHP partial view for mail text
 *
 * @package    Sift
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfPartialMailView extends sfPHPView
{
  /**
   * Executes any presentation logic for this view.
   */
  public function execute()
  {
  }

  /**
   * Setups decorator template for this view based on email type (plain or html)
   * Email decorator templates should be in "sf_data_dir/email/plain.php" for PLAIN
   * or "sf_data_dir/email/html.php" for HTML version
   *
   * @param string $type plain or html
   * @param boolean $noLayout use decorator?
   * @return void
   * @throws sfException If decorator template is missing
   */
  protected function setupDecoratorTemplate($type, $noLayout)
  {
    if($noLayout)
    {
      $this->setDecoratorTemplate(false);
      return;
    }
    
    // where the email layout resides
    $dataDir = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'email';
    if(is_readable($dataDir . DIRECTORY_SEPARATOR . $type . '.php'))
    {
      $this->setDecoratorTemplate($dataDir . DIRECTORY_SEPARATOR . $type);
    }
    else
    {
      throw new sfException(sprintf('{sfPartialMailView} Email decorator template "%s" not found in "%s"', $type, $dataDir));
    }
  }

  /**
   * Configures template for this view.
   */
  public function configure()
  {
    $this->setTemplate($this->actionName.$this->getExtension());
    if('global' == $this->moduleName)
    {
      $this->setDirectory(sfConfig::get('sf_app_template_dir'));
    }
    else
    {
      $this->setDirectory(sfLoader::getTemplateDir($this->moduleName, $this->getTemplate()));
    }
  }

  /**
   * Loop through all template slots and fill them in with the results of
   * presentation data.
   *
   * @param string A chunk of decorator content
   *
   * @return string A decorated template
   */
  protected function decorate($content)
  {
    if(!$decorator_template = $this->getDecoratorTemplate())
    {
      return $content;
    }

    $template = $this->getDecoratorDirectory().'/'.$decorator_template;

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->getContext()->getLogger()->info('{sfPartialMailView} decorate content with "'.$template.'"');
    }

    // set the decorator content as an attribute
    $this->attributeHolder->set('sf_content', $content);

    // render the decorator template and return the result
    return $this->renderFile($template);
  }
  
  protected function getMailVars()
  {
    $context    = $this->getContext();
    $request    = $context->getRequest();

    $ip         = $request->getIp();
    $hostname   = $request->getHostname();
    $user_agent = $request->getUserAgent();

    $shortcuts = array(
      'ip' => $ip,
      // if no hostname, use ip instead
      'hostname' => $hostname ? $hostname : $ip,
      'user_agent' => $user_agent,
      'time' => time()
    );

    return $shortcuts;
  }

  protected function loadMailHelper()
  {
    static $mailHelpersLoaded = 0;

    if($mailHelpersLoaded)
    {
      return;
    }

    $mailHelpersLoaded = 1;

    sfLoader::loadHelpers(array('Mail'));    
  }

  /**
   * Renders the presentation.
   *
   * @param array Template attributes
   *
   * @return string Current template content
   */
  public function render($templateVars = array())
  {
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer = sfTimerManager::getTimer(sprintf('Mail partial "%s/%s"', $this->moduleName, $this->actionName));
    }

    $template = $this->getDirectory().'/'.$this->getTemplate();
    
    // execute pre-render check
    $this->preRenderCheck();

    // assigns some variables to the template
    $this->attributeHolder->add($this->getGlobalVars());
    $this->attributeHolder->add($this->getMailVars());
    $this->attributeHolder->add($templateVars);

    $type     = $this->attributeHolder->get('sf_email_type', 'plain');
    $noLayout = $this->attributeHolder->get('sf_email_no_layout', false);

    $this->setupDecoratorTemplate($type, $noLayout);

    $this->loadMailHelper();

    // render template
    $retval = $this->renderFile($this->getDirectory().'/'.$this->getTemplate());

    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer->addTime();
    }

    return $this->decorate($retval);
  }

}
