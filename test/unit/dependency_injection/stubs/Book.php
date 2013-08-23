<?php

class Book {

  private $_database;

  /**
   * @inject database
   */
  public function setDatabase($database)
  {
    $this->_database = $database;
  }

}