<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanel represents a web debug panel.
 *
 * @package    Sift
 * @subpackage debug_panel
 */
abstract class sfWebDebugPanel extends sfConfigurable
{
  protected $webDebug = null,
    $status = sfILogger::INFO;

  /**
   * The debug backtrace decorator
   *
   * @var sfIDebugBacktraceDecorator
   */
  protected $debugBacktraceDecorator;

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'file_url_format' => 'editor://open?file=%file%&line=%line%',
    'backtrace_class' => 'web-debug-backtrace',
    // backtrace decorator template directory
    'backtrace_decorator_template_dir' => null,
    'backtrace_decorator_template' => null
  );

  /**
   * Array of required options
   *
   * @var array
   */
  protected $requiredOptions = array(
    'template_dir'
  );

  /**
   * Constructor.
   *
   * @param sfWebDebug $webDebug The web debug toolbar instance
   */
  public function __construct(sfWebDebug $webDebug, $options = array())
  {
    $this->webDebug = $webDebug;
    parent::__construct($options);
  }

  /**
   * Gets the link URL for the link.
   *
   * @return string The URL link
   */
  public function getTitleUrl()
  {
  }

  /**
   * Gets the icon
   *
   * @return string Icon src tag
   */
  public function getIcon()
  {
  }

  /**
   * Gets the text for the link.
   *
   * @return string The link text
   */
  abstract public function getTitle();

  /**
   * Gets the title of the panel.
   *
   * @return string The panel title
   */
  abstract public function getPanelTitle();

  /**
   * Gets the panel HTML content.
   *
   * @return string The panel HTML content
   */
  abstract public function getPanelContent();

  /**
   * Called before rendering the web debug. Allow to
   * prepare the title, content and so on.
   *
   */
  public function beforeRender()
  {
  }

  /**
   * Returns panel css
   */
  public function getPanelCss()
  {
    return '';
  }

  /**
   * Returns panel javascript
   *
   * @return string
   */
  public function getPanelJavascript()
  {
    return '';
  }

  /**
   * Returns the current status.
   *
   * @return integer A {@link sfILogger} level constant
   */
  public function getStatus()
  {
    return $this->status;
  }

  /**
   * Sets the current panel's status.
   *
   * @param integer $status A {@link sfILogger} level constant
   */
  public function setStatus($status)
  {
    $this->status = $status;
  }

  /**
   * Returns a toggleable presentation of a debug stack.
   *
   * @param  array $debugStack
   *
   * @return string
   */
  public function getDebugStack($debugStack)
  {
    if(!$debugStack instanceof sfDebugBacktrace)
    {
      $debugStack = new sfDebugBacktrace($debugStack);
    }

    $decorator = $this->getDebugBacktraceDecorator();
    $decorator->setBacktrace($debugStack);

    return $decorator->toString();
  }

  /**
   * Returns the debug backtrace decorator
   *
   * @return sfDebugBacktraceDecoratorHtml
   */
  protected function getDebugBacktraceDecorator()
  {
    if(!$this->debugBacktraceDecorator)
    {
      $options = array(
        'class' => $this->getOption('backtrace_class'),
        'template_dir' => $this->getOption('backtrace_decorator_template_dir',
                          $this->getOption('template_dir') . '/backtrace'),
      );

      if($template = $this->getOption('backtrace_decorator_template'))
      {
        $options['template'] = $template;
      }

      $this->debugBacktraceDecorator = new sfDebugBacktraceHtmlDecorator(null, $options);
    }

    return $this->debugBacktraceDecorator;
  }

  /**
   * Return file edit url
   *
   * @param string  $file A file path or class name
   * @param integer $line
   * @return string
   */
  public function getFileEditUrl($file, $line = null)
  {
    if(!($linkFormat = $this->getOption('file_url_format')))
    {
      return '';
    }

    return strtr($linkFormat, array('%file%' => $file, '%line%' => $line));
  }

  /**
   * Shortens file path
   *
   * @param string $file
   * @return string
   */
  protected function shortenFilePath($file)
  {
    return sfDebug::shortenFilePath($file);
  }

  /**
   * Format a SQL string with some colors on SQL keywords to make it more readable.
   *
   * @param  string $sql    SQL string to format
   * @return string $newSql The new formatted SQL string
   */
  public function formatSql($sql)
  {
    static $highlighter;
    if(!$highlighter)
    {
      $highlighter = sfSyntaxHighlighter::factory('sql');
    }

    return $highlighter->setCode($sql)->getHtml();
  }

}
