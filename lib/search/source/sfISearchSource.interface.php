<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfISearchSource - search source interface
 * 
 * @package Sift
 * @subpackage search
 */
interface sfISearchSource {

  public function find($q, $page = 1, $perPage = 10);  
  public function findNumberResults($q);
  public function hasSuggest();  
  public function hasSort();
  public function suggest($q);  
  public function getResults();
  public function getName();
  public function getDescription();  
  public function getStylesheets();  
  public function getJavascripts();  
  public function isSecure();  
  public function getCredentials();
  
}
