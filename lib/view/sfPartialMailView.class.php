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
 */
class sfPartialMailView extends sfPartialView
{
    /**
     * Helpers loaded flag
     *
     * @var boolean
     */
    protected $helpersLoaded = false;

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
     * @return void
     */
    protected function setupDecoratorTemplate()
    {
        // manually set decorator template
        if (!$this->getDecoratorTemplate()) {
            if ($this->attributeHolder->get('sf_email_no_layout', false)) {
                return;
            }
            $type = $this->attributeHolder->get('sf_email_type', 'plain');
            // where the email layout resides
            $dataDir = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'email';
            // only if readable
            if (is_readable($tpl = ($dataDir . DIRECTORY_SEPARATOR . $type . $this->getExtension()))) {
                $this->setDecorator(true);
                $this->setDecoratorTemplate($tpl);
            }
        }
    }

    /**
     * Configures template for this view.
     */
    public function configure()
    {
        $this->setTemplate($this->actionName . $this->getExtension());
        if ('global' == $this->moduleName) {
            $this->setDirectory(sfConfig::get('sf_app_template_dir'));
        } else {
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
        if (!$decorator_template = $this->getDecoratorTemplate()) {
            return $this->formatPlainText($content);
        }

        $template = $this->getDecoratorDirectory() . '/' . $decorator_template;

        if (sfConfig::get('sf_logging_enabled')) {
            sfLogger::getInstance()->info('{sfPartialMailView} Decorate content with "' . $template . '"');
        }

        // set the decorator content as an attribute
        $this->attributeHolder->set('sf_content', $content);

        // render the decorator template and return the result
        return $this->formatPlainText($this->renderFile($template));
    }

    /**
     * Return variables usefully in email templates
     *
     * @return array
     */
    protected function getMailVars()
    {
        $context = $this->getContext();
        $request = $context->getRequest();

        $ip = $request->getIp();
        $hostname = $request->getHostname();
        $user_agent = $request->getUserAgent();

        $shortcuts = array(
            'ip'         => $ip,
            // if no hostname, use ip instead
            'hostname'   => $hostname ? $hostname : $ip,
            'user_agent' => $user_agent,
            'time'       => time()
        );

        return $shortcuts;
    }

    /**
     * Load helpers
     *
     */
    protected function loadMailHelper()
    {
        if ($this->helpersLoaded) {
            return;
        }
        $this->helpersLoaded = true;
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
        if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled')) {
            $timer = sfTimerManager::getTimer(sprintf('Mail partial "%s/%s"', $this->moduleName, $this->actionName));
        }

        // execute pre-render check
        $this->preRenderCheck();

        // assigns some variables to the template
        $this->attributeHolder->add($this->getGlobalVars());
        $this->attributeHolder->add($this->getMailVars());
        $this->attributeHolder->add($templateVars);

        $this->loadMailHelper();

        // render template
        $rendered = $this->renderFile($this->getDirectory() . '/' . $this->getTemplate());

        if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled')) {
            $timer->addTime();
        }

        $this->setupDecoratorTemplate();

        return $this->decorate($rendered);
    }

    /**
     * Formats plain text. Replaces spaces
     *
     * @param string $text
     *
     * @return string
     */
    protected function formatPlainText($text)
    {
        if ($this->attributeHolder->get('sf_email_type', 'plain') == 'plain') {
            return preg_replace("/(\r?\n){2,}/", "\n\n", $text);
        }

        return $text;
    }

}
