<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface for model columns
 *
 * @package    Sift
 * @subpackage generator
 */
interface sfIGeneratorModelColumn {

  public function getRenderer();
  public function setRenderer($renderer);
  public function setRendererArguments(array $arguments);
  public function getRendererArguments();
  public function render($context);

  public function getName();
  public function getHelp();
  public function getCssClass();

  public function isSortable();

  public function isPrimaryKey();
  public function isForeignKey();
  public function isPartial();
  public function isLink();
  public function isComponent();
  public function isNotNull();
  public function isNull();  
  // does the column represent ip address?
  public function isIpAddress();  
  // does it represent culture (lang) column?
  public function isCulture();

  public function isReal();
  public function getForeignClassName();
  public function isRelationAlias();

  public function getType();
  public function getSize();

}
