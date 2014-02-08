<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Packages a plugin.
 *
 * @package     Sift
 * @subpackage  cli_task
 */
class sfCliPluginPackageTask extends sfCliPluginBaseTask
{
  protected $pluginDir   = null,
    $interactive = true;

  /**
   * @see sfCliTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCliCommandArgument('plugin', sfCliCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->addOptions(array(
      new sfCliCommandOption('plugin-version', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The plugin version'),
      new sfCliCommandOption('plugin-channel', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The plugin channel'),
      new sfCliCommandOption('plugin-stability', null, sfCliCommandOption::PARAMETER_REQUIRED, 'The plugin stability'),
      new sfCliCommandOption('non-interactive', null, sfCliCommandOption::PARAMETER_NONE, 'Skip interactive prompts'),
      new sfCliCommandOption('nocompress', null, sfCliCommandOption::PARAMETER_NONE, 'Do not compress the package'),
    ));

    $this->namespace = 'plugin';
    $this->name = 'package';

    $this->briefDescription = 'Create a plugin PEAR package';

    $scriptName = $this->environment->get('script_name');

    $this->detailedDescription = <<<EOF
The [plugin:package|INFO] task creates a plugin PEAR package:

  [{$scriptName} plugin:package sfExamplePlugin|INFO]

If your plugin includes a package.xml file, it will be used. If not, the task
will look for a package.xml.tmpl file in your plugin and use either that or a
default template to dynamically generate your package.xml file.

You can either edit your plugin's package.xml.tmpl file or use the
[--plugin-version|COMMENT] or [--plugin-stability|COMMENT] options to set the
release version and stability, respectively:

  [{$scriptName} plugin:package sfExamplePlugin --plugin-version=0.5.0 --plugin-stability=alpha|INFO]

To disable any interactive prompts in the packaging process, include the
[--non-interactive|COMMENT] option:

  [{$scriptName} plugin:package sfExamplePlugin --non-interactive|INFO]

To disable compression of the package tar, use the [--nocompress|COMMENT]
option:

  [{$scriptName} plugin:package sfExamplePlugin --nocompress|INFO]
EOF;
  }

  /**
   * @see sfCliTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->checkPluginExists($arguments['plugin']);

    $this->pluginDir = $this->commandApplication
                              ->getProject()
                              ->getPlugin($arguments['plugin'])->getRootDir();

    $this->interactive = !$options['non-interactive'];

    $cleanup = array();

    if (!file_exists($this->pluginDir.'/package.xml'))
    {
      $cleanup['temp_files'] = array();
      foreach (sfFinder::type('dir')->in($this->pluginDir) as $dir)
      {
        if (!sfFinder::type('any')->maxDepth(0)->in($dir))
        {
          $this->getFilesystem()->touch($file = $dir.'/.sf');
          $cleanup['temp_files'][] = $file;
        }
      }

      $cleanup['package_file'] = true;
      $this->generatePackageFile($arguments, $options);
    }

    $manager = $this->getPluginManager();

    // load xml
    $xml = simplexml_load_file($this->pluginDir.'/package.xml');
    $channel = (string)$xml->channel;

    try
    {
      // register the channel
      $manager->getEnvironment()->addChannel($channel);

      $package = $manager->packagePlugin($this->pluginDir.'/package.xml', $options);
    }
    catch(sfException $e)
    {
      if(isset($cleanup['package_file']))
      {
        $cleanup['package_file'] = '.error';
      }

      $this->cleanup($cleanup);

      throw new sfCliCommandException($e->getMessage());
    }

    $this->cleanup($cleanup);
  }

  /**
   * Cleanup files.
   *
   * Available options:
   *
   *  * package_file
   *
   * @param array $options
   */
  protected function cleanup(array $options = array())
  {
    $options = array_merge(array(
      'package_file' => false,
      'temp_files'   => array(),
    ), $options);

    if ($extension = $options['package_file'])
    {
      if (is_string($extension))
      {
        $this->getFilesystem()->copy($this->pluginDir.'/package.xml', $this->pluginDir.'/package.xml'.$extension, array('override' => true));
      }

      $this->getFilesystem()->remove($this->pluginDir.'/package.xml');
    }

    foreach ($options['temp_files'] as $file)
    {
      $this->getFilesystem()->remove($file);
    }
  }

  /**
   * Generates a package.xml file in the plugin directory.
   *
   * @todo Move this into its own task
   */
  protected function generatePackageFile(array $arguments, array $options)
  {
    if(!file_exists($templatePath = $this->pluginDir.'/package.xml.tmpl'))
    {
      $templatePath = $this->environment->get('sf_sift_data_dir').'/skeleton/plugin/package.xml.tmpl';
    }

    $template = file_get_contents($templatePath);

    $tokens = array(
      'PLUGIN_NAME'  => $arguments['plugin'],
      'CURRENT_DATE' => date('Y-m-d'),
      'ENCODING'     => $this->environment->get('sf_charset', 'UTF-8'),
    );

    if (false !== strpos($template, '##SUMMARY##'))
    {
      $tokens['SUMMARY'] = $this->askAndValidate('Summarize your plugin in one line:', new sfValidatorCallback(array(
        'required' => true,
        'callback' => create_function('$a, $b', 'return htmlspecialchars($b, ENT_QUOTES, \'UTF-8\');'),
      ), array(
        'required' => 'You must provide a summary of your plugin.',
      )), array(
        'value' => htmlspecialchars($this->getProjectProperty('author'), ENT_QUOTES, $this->environment->get('sf_charset', 'UTF-8')),
      ));
    }

    if (false !== strpos($template, '##LEAD_NAME##'))
    {
      $validator = new sfValidatorString(array(), array('required' => 'A lead developer name is required.'));
      $tokens['LEAD_NAME'] = $this->askAndValidate('Lead developer name:', $validator, array(
        'value' => htmlspecialchars($this->getProjectProperty('author'), ENT_QUOTES, $this->environment->get('sf_charset', 'UTF-8')),
      ));
    }

    if (false !== strpos($template, '##LEAD_EMAIL##'))
    {
      $validator = new sfValidatorEmail(array(), array('required' => 'A valid lead developer email address is required.', 'invalid' => '"%value%" is not a valid email address.'));
      $tokens['LEAD_EMAIL'] = $this->askAndValidate('Lead developer email:', $validator, array(
        'value' => htmlspecialchars($this->getProjectProperty('author'), ENT_QUOTES, $this->environment->get('sf_charset', 'UTF-8'))
      ));
    }

    if (false !== strpos($template, '##LEAD_USERNAME##'))
    {
      $validator = new sfValidatorString(array(), array('required' => 'A lead developer username is required.'));
      $tokens['LEAD_USERNAME'] = $this->askAndValidate('Lead developer username:', $validator, array(
        'value' => htmlspecialchars($this->getProjectProperty('username'), ENT_QUOTES, $this->environment->get('sf_charset', 'UTF-8')),
      ));
    }

    if (false !== strpos($template, '##PLUGIN_VERSION##'))
    {
      $validator = new sfValidatorRegex(array('pattern' => '/\d+\.\d+\.\d+/', ), array('required' => 'A valid version number is required.', 'invalid' => '"%value%" is not a valid version number.'));
      $tokens['PLUGIN_VERSION'] = $this->askAndValidate('Plugin version number (i.e. "1.0.5"):', $validator, array('value' => $options['plugin-version']));

      // set api version based on plugin version
      $tokens['API_VERSION'] = version_compare($tokens['PLUGIN_VERSION'], '0.1.0', '>') ? join('.', array_slice(explode('.', $tokens['PLUGIN_VERSION']), 0, 2)).'.0' : $tokens['PLUGIN_VERSION'];
    }

    if (false !== strpos($template, '##STABILITY##'))
    {
      $validator = new sfValidatorChoice(array('choices' => $choices = array('devel', 'alpha', 'beta', 'stable')), array('required' => 'A valid stability is required.', 'invalid' => '"%value%" is not a valid stability ('.join('|', $choices).').'));
      $tokens['STABILITY'] = $this->askAndValidate('Plugin stability:', $validator, array('value' => $options['plugin-stability']));
    }

    if (false !== strpos($template, '##CHANNEL##'))
    {
      $tokens['CHANNEL'] = $this->askAndValidate('Plugin channel:', new sfValidatorCallback(array(
        'required' => true,
        'callback' => create_function('$a, $b', 'return htmlspecialchars($b, ENT_QUOTES, \'UTF-8\');'),
      ), array(
        'required' => 'You must provide a channel.',
      )), array(
        'value' => $options['plugin-channel'],
      ));
    }

    // FIXME: this should be configured somewhere!
    $tokens['SIFT_CHANNEL'] = $this->environment->get('pear-channel', 'pear.lab');

    $finder = sfFinder::type('any')->maxdepth(0)
                ->discard('package.xml.tmpl')
                ->prune('nbproject')->discard('nbproject')
                ->prune('.gitignore')->discard('.gitignore');

    $tokens['CONTENTS'] = $this->buildContents($this->pluginDir, $finder);

    $this->getFilesystem()->copy($templatePath, $this->pluginDir.'/package.xml');
    $this->getFilesystem()->replaceTokens($this->pluginDir.'/package.xml', '##', '##', $tokens);

    // remove those tokens that shouldn't be written to the template
    unset(
      $tokens['ENCODING'],
      $tokens['CURRENT_DATE'],
      $tokens['PLUGIN_VERSION'],
      $tokens['API_VERSION'],
      $tokens['STABILITY'],
      $tokens['CONTENTS']
    );

    if (count($tokens))
    {
      // create or update package.xml template
      $this->getFilesystem()->copy($templatePath, $this->pluginDir.'/package.xml.tmpl');
      $this->getFilesystem()->replaceTokens($this->pluginDir.'/package.xml.tmpl', '##', '##', $tokens);
    }
  }

  /**
   * Returns an XML string for the contents of the supplied directory.
   *
   * @param   string           $directory
   * @param   sfFinder         $finder
   * @param   SimpleXMLElement $baseXml
   *
   * @return  string
   */
  protected function buildContents($directory, sfFinder $finder = null, SimpleXMLElement $baseXml = null)
  {
    if (null === $finder)
    {
      $finder = sfFinder::type('any')->maxdepth(0);
    }

    if (null === $baseXml)
    {
      $baseXml = new SimpleXMLElement('<dir name="/"/>');
    }

    foreach ($finder->in($directory) as $entry)
    {
      if (is_dir($entry))
      {
        $entryXml = $baseXml->addChild('dir');
        $entryXml['name'] = basename($entry);

        $this->buildContents($entry, null, $entryXml);
      }
      else
      {
        $entryXml = $baseXml->addChild('file');
        $entryXml['name'] = basename($entry);
        $entryXml['role'] = 'data';
      }
    }

    // format using DOM to omit XML declaration
    $domElement = dom_import_simplexml($baseXml);
    $domDocument = $domElement->ownerDocument;
    $domDocument->encoding = $this->environment->get('sf_charset', 'UTF-8');
    $xml = $domDocument->saveXml($domElement);

    return $xml;
  }

  /**
   * @see sfCliTask
   */
  public function askAndValidate($question, sfValidatorBase $validator, array $options = array())
  {
    if ($this->interactive)
    {
      return parent::askAndValidate($question, $validator, $options);
    }
    else
    {
      return $validator->clean(isset($options['value']) ? $options['value'] : null);
    }
  }
}
