<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMacroTextFilter applies macros to the text
 *
 * @package    Sift
 * @subpackage text
 */
class sfMacroTextFilter extends sfTextFilter {

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'allowed_tags' => array()
  );

  /**
   * Macro registry instance
   *
   * @var sfTextMacroRegistry
   */
  protected $registry;

  /**
   * Constructor
   *
   * @param sfTextMacroRegistry $registry The macro registry
   * @param array $options Array of options
   * @inject text_macro_registry
   */
  public function __construct(sfTextMacroRegistry $registry, $options = array())
  {
    $this->registry = $registry;
    parent::__construct($options);
  }

  /**
   * Filters fiven text, also applied shortcodes
   *
   * @param string $content
   * @param array $params
   * @return string
   */
  public function filter(sfTextFilterContent $content)
  {
    $parsed = $this->registry->parse($content->getText(), $this->getOption('allowed_tags'));
    $content->setText($parsed);
  }

 }
