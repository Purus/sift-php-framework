<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the sfSearchResult class.
 *
 * @package Sift
 * @subpackage search 
 */
class sfSearchResult {

  /**
   * mySearchSource holder
   * 
   * @var mySearchSource
   */
  protected $source;
  protected $html;

  /**
   * Result variables holder
   * 
   * @var array
   */
  protected $vars = array(
    'relevancy' => 0,      
    'created_at' => null     
  );

  /**
   * Constructs the result
   * 
   * @param myISearchSource $source
   * @param ArrayAccess $vars
   * @throws InvalidArgumentException
   */
  public function __construct(sfISearchSource $source, $vars = array())
  {
    $this->source = $source;
    
    if(!is_array($vars))
    {
      throw new InvalidArgumentException(
        sprintf('Invalid argument $vars passed ("%s"). It should be an array or object with array access interface.', 
              gettype($vars)));
    }
    
    foreach($vars as $var => $value)
    {
      $this->vars[$var] = $value ? $value : '';
    }
    
  }

  public function __get($name)
  {
    if(!isset($this->vars[$name]))
    {
      throw new Exception(sprintf('Property "%s" does not exist', $name));
    }
    return $this->vars[$name];
  }

  public function __set($key, $value)
  {
    $this->vars[$key] = $value;
  }

  /**
   * __call
   *
   * @param string $m
   * @param string $a
   * @return void
   */
  public function __call($m, $a)
  {
    if(method_exists($this, $m))
    {
      return call_user_func_array($m, $a);
    }

    $verb = substr($m, 0, 3);
    $column = substr($m, 3);
    
    // first character lowercase
    $column[0] = strtolower($column[0]);    
    $column    = sfInflector::underscore($column);
    
    if($verb == 'get')
    {
      return $this->vars[$column];
    }
    elseif($verb == 'set')
    {
      return $this->vars[$column] = $a[0];
    }
  }

  public function getSource()
  {
    return $this->source;
  }

  public function setHtml($html)
  {
    $this->html = $html;
  }

  /**
   * Returns HTML for this search result
   * 
   * @param string $search_query Hightlight search query
   * @return string
   */
  public function getHtml(sfSearchQueryExpression $search_query = null)
  {
    if($search_query)
    {
      return sfSearchTools::highlight($this->html, $search_query);
    }
    
    return $this->html;
  }

}
