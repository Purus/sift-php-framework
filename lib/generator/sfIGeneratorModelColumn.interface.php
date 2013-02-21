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
  
  public function getName();
  
  public function isPrimaryKey();
  public function isForeignKey();
  public function isPartial();
  public function isLink();
  public function isComponent();
  public function isNotNull();
  public function isNull();

  public function isReal();
  public function getForeignClassName();
  public function isRelationAlias();
  
  public function getType();
  public function getSize();
  
}
