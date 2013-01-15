<?php

class mySearchSourceTest extends sfSearchSourceAbstract {

  public function find($q, $limit = 10, $offset = 0)
  {
    // connect to database
    // perform actual search
    // loop thru results and create for each result
    // new mySearchResult object from the "real" result
    // and assign it to the collection
    
    $results = array(
      $this->createResult(array('title' => 'TEstík', 'url'=> 'http://madcow.lab/testik', 'excerpt' => 'Toto je popísek')),
      $this->createResult(array('title' => 'TEstík 2 ', 'url'=> 'http://madcow.lab/testik2/', 'excerpt' => 'Toto je popísek 2')),
      $this->createResult(array('title' => 'TEstík 3', 'url' => 'http://madcow.lab/testik2/', 'exceprt' => 'Toto je popísek 2')),
    );

    $this->setResults($results);

    // fluent interface
    return $this;
  }
  
  public function findNumberResults($q)
  {
    return 4;
  }

  public function find2($q, $limit = 10, $offset = 0)
  {
    // connect to database
    // perform actual search
    // loop thru results and create for each result
    // new mySearchResult object from the "real" result
    // and assign it to the collection

    $results = array(
      $this->createResult(array('title' => 'TEstík z dvojky', 'url'=> 'http://madcow.lab/testik-z-dvojky/', 'excerpt' => 'Toto je popísek z testíku dva')),
      $this->createResult(array('title' => 'TEstík z dvojky 2', 'url'=> 'http://madcow.lab/testik-z-dvojky-2/', 'excerpt' => 'Toto je popísek z testíku dva dva')),
    );

    $this->setResults($results);

    // fluent interface
    return $this;
  }

  public function getName()
  {
    return 'Test';
  }

  public function getResults()
  {
    return $this->resultCollection;
  }
  

}