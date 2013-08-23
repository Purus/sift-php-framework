<?php

/**
 * This is a non instantiable object
 *
 */

class StubsNonInst {

   public function aMethod() {
       return 'this was called';
   }

   private function  __construct() { }
   private function  __clone() { }
   
}
