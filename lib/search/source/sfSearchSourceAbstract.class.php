<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * File containing the mySearchSourceAbstract class.
 *
 * @package Sift
 * @subpackage search 
 */
abstract class sfSearchSourceAbstract implements sfISearchSource {

  /**
   * Result collection holder
   *
   * @var mySearchResultCollection
   */
  protected $resultCollection;

  /**
   * An array of options
   * 
   * @var array
   */
  protected $options = array(
    'max_per_page' => 10,
    'javascripts' => array(),
    'stylesheets' => array(),
    'priority'    => 0,
    'is_secure'   => false,
    'credentials' => array()  
  );
  
  protected $numberResults = 0;
  protected $culture;
  protected $pager;
  
  /**
   * Partial used for rendering advanced options
   * 
   * @var array
   */
  protected $advancedOptions = array(
      'module' => '',
      'partial' => ''
  );
  
  /**
   * Partial used for rendering sorting feature
   * 
   * @var array
   */
  protected $sortOptions = array(
      'module' => '',
      'partial' => ''
  );
  
  protected $filters;

  /**
   * Constructs the class
   * 
   * @param array $options 
   */
  public function __construct($options = array())
  {    
    $this->resultCollection = new sfSearchResultCollection($this);
    $this->options = sfToolkit::arrayDeepMerge($this->options, $options);
    $this->construct($options);
  }

  /**
   * Has suggest feature?
   *
   * @return boolean
   */
  public function hasSuggest()
  {
    return false;
  }

  /**
   * Suggest (used by search autocompleters)
   *
   * @param string $q
   * @return array|false array
   */
  public function suggest($q)
  {
    return false;
  }

  /**
   * construct
   * Empty template method to provide concrete mySearchSource classes with the possibility
   * to hook into the constructor procedure
   *
   * @return void
   */
  public function construct($options = array())
  {
  }

  /**
   * Returns maximum number of items to be displayed per page
   * 
   * @return integer
   */
  public function getMaxPerPage()
  {
    return $this->getOption('max_per_page');
  }

  /**
   * Sets the filters
   * 
   * @param array $filters 
   */
  public function setFilters($filters)
  { 
    if(!$filters instanceof sfRequestFiltersHolder)
    {
      $filters = new sfRequestFiltersHolder($filters);
    }    
    $this->filters = $filters;
  }

  /**
   * Returns the filters
   * 
   * @return sfRequestFiltersHolder
   */
  public function getFilters()
  {
    return $this->filters;
  }

  /**
   * Has this source filters?
   * 
   * @return boolean
   */
  public function hasFilters()
  {
    if(!$this->filters)
    {
      return false;
    }
    
    $count = count($this->filters);
    
    // we remove count for ordering
    // FIXME: this is a bit hacky
    if(isset($this->filters['o']))
    {
      $count--;
    }
    
    return $count > 0;     
  }
  
  /**
   * Sets the option
   * 
   * @param string $option
   * @param string|mixed $value
   * @return mySearchSourceAbstract 
   */
  protected function setOption($option, $value)
  {
    $this->options[$option] = $value;
    return $this;
  }

  /**
   * Returns the option or throws an Exception if option is not valid
   * 
   * @param string $name
   * @return string|mixed
   * @throws Exception 
   */
  protected function getOption($name, $default = null)
  {
    if(isset($this->options[$name]))
    {
      return $this->options[$name];
    }
    
    return $default;
  }

  /**
   * Performs the search
   * 
   * @param string $q
   * @param integer $page
   * @param integer $maxPerPage
   * @throws sfException 
   */
  // abstract public function find($q, $page = 1, $maxPerPage = 10);

  /**
   * Fetches the count of the results
   * 
   * @param string $q
   * @throws sfException 
   */
  // abstract public function findNumberResults($q);

  /**
   * Returns the culture
   * 
   * @return string 
   */
  public function getCulture()
  {
    return $this->culture;
  }

  /**
   * Sets the culture
   * 
   * @param string $culture
   * @return mySearchSourceAbstract 
   */
  public function setCulture($culture)
  {
    $this->culture = $culture;
    return $this;
  }

  /**
   * Get search source name
   *
   * @return string
   */
  public function getName()
  {
    throw new sfException(sprintf('{%s} You should override getName() method!', get_class($this)));
  }

  /**
   * Get search source description
   *
   * @return string
   */
  public function getDescription()
  {
    return '';
  }

  /**
   * Returns the name of this source
   * 
   * @return string
   */
  public function __toString()
  {
    return $this->getName();
  }

  /**
   * Returns search source id
   *
   * @return string
   */
  public function getId()
  {
    return str_replace(array('my_search_source_', '_'), array('', '-'), sfInflector::underscore(get_class($this)));
  }

  /**
   * Set result array to collection
   * 
   * @param array $results
   */
  protected function setResults($results)
  {
    $this->resultCollection->setData($results);
  }

  /**
   * Sets the pager
   * 
   * @param Doctrine_Pager $pager 
   */
  protected function setPager(Doctrine_Pager $pager)
  {
    $this->pager = $pager;
  }

  /**
   * Returns the pager
   * 
   * @return Doctrine_Pager
   */
  public function getPager()
  {
    return $this->pager;
  }

  /**
   *
   * @return type 
   */
  public function getResults()
  {
    return $this->resultCollection;
  }

  /**
   * Set total number of results
   * 
   * @param integer $number total number of results
   */
  public function setNumberResults($number)
  {
    $this->numberResults = $number;
  }

  /**
   * Get total number of results
   * 
   * @return integer
   */
  public function getNumberResults()
  {
    return $this->numberResults;
  }

  /**
   * Escapes search string
   * 
   * @param string $q
   * @return string string
   */
  protected function escapeSearchString($q)
  {
    return sfSearchTools::escapeSearchString($q);
  }

  /**
   * Creates new search result object
   *
   * @param string $title
   * @param string $url
   * @param string $description
   * @return mySearchResult
   */
  protected function createResult($vars = array())
  {
    return new mySearchResult($this, $vars);
  }

  /**
   * Has this source advanced options?
   * 
   * @return boolean
   */
  public function hasAdvancedOptions()
  {
    return false;
  }

  /**
   * Returns HTML code for advanced options for this source
   * 
   * @return string 
   */
  public function getAdvancedOptionsHtml()
  {
    if(!$this->hasAdvancedOptions())
    {
      return '';
    }

    if($this->advancedOptions['module']
            && $this->advancedOptions['partial'])
    {
      sfLoader::loadHelpers(array('Url', 'Asset', 'Partial'));
      return get_partial(sprintf('%s/%s', $this->advancedOptions['module'], $this->advancedOptions['partial']), array(
                  'source' => $this
              ));
    }
    return '';
  }

  /**
   * Has sort feature?
   * 
   * @return boolean
   */
  public function hasSort()
  {
    return false;
  }

  public function getSortHtml()
  {
    if(!$this->hasSort())
    {
      return '';
    }

    if($this->sortOptions['module']
            && $this->sortOptions['partial'])
    {
      sfLoader::loadHelpers(array('Url', 'Asset', 'Partial'));
      return get_partial(sprintf('%s/%s', $this->sortOptions['module'], $this->sortOptions['partial']), 
              array(
                'source' => $this
              ));
    }
    
    return '';
  }
  
  /**
   * Returns required javascripts
   * 
   * @return array
   */
  public function getJavascripts()
  {
    return $this->getOption('javascripts', array());
  }

  /**
   * Returns required stylesheets
   * 
   * @return array 
   */
  public function getStylesheets()
  {
    return $this->getOption('stylesheets', array());
  }

  /**
   * Set source priority
   * 
   * @param integer $priority
   * @return mySearchSourceAbstract 
   */
  public function setPriority($priority)
  {
    return $this->setOption('priority', $priority);    
  }  
  
  /**
   * Gets priority
   * 
   * @return integer
   */
  public function getPriority()
  {
    return $this->getOption('priority');
  }
  
  /**
   * Is source secure? ie. requires user to be logged in?
   * 
   * @param integer $priority
   * @return mySearchSourceAbstract 
   */
  public function isSecure()
  {
    return $this->getOption('is_secure');    
  }
  
  public function getCredentials()
  {
    return $this->getOption('credentials', array());
  }
  
}