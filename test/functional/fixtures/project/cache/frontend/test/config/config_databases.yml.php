<?php
// auto-generated by sfDatabaseConfigHandler
// date: 2013/01/15 14:51:20

$database = new sfPDODatabase();
$database->initialize(array (
  'dsn' => 'sqlite:D:\\data\\git\\sift.git\\test\\functional\\fixtures\\project\\data/database.sqlite',
), 'default');
$this->databases['default'] = $database;
