<?php

/**
 * This doc comment have to be specified in the extended class since the reflection
 * does not return it. Its works ok for inherited methods and properties.
 * 
 * @inject new:Something method:setForce force:true
 * @inject apple method:setApple
 */
class DummyExtended extends Dummy {}