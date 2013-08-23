<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The parser takes a string and reads all of the inject
 * information.  Its an internal class, no reason for the user
 * to need to parse his own strings.
 *
 * @package Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionMapBuilderParser {

  private $_info;
  private $_string;
  private $_matches = array();
  private $_matched = 0;
  private $_options = array();

  /**
   * The string to parse
   *
   * @param string $string
   * @return sfDependencyInjectionMapBuilderParser
   */
  public function setString($string)
  {
    $this->_string = $string;
    $this->_options = array();
    return $this;
  }

  /**
   * Matches the string
   *
   * @return sfDependencyInjectionMapBuilderParser
   */
  public function match()
  {
    $this->_matched = preg_match_all(
      '/@inject(.*?)(\n|$)/i', $this->_string, $this->_matches
    );
    return $this;
  }

  /**
   * Any information on whats about to be parsed.  Usually this is a
   * Reflection method/property, but it can be anything.  Used when
   * throwing errors/exceptions or debugging.
   *
   * @param mixed $info
   * @return sfDependencyInjectionMapBuilderParser
   */
  public function setInfo($info)
  {
    $this->_info = $info;
    return $this;
  }

  /**
   * Checks if the string has a inject command
   *
   * @return bool
   */
  public function hasCommand()
  {
    return $this->_matched > 0;
  }

  /**
   * Return ths number of inject commands
   *
   * @return integer
   */
  public function getNumberOfCommands()
  {
    return count($this->_matches[1]);
  }

  private function _defaultOptions()
  {
    return array(
      'dependencyName' => null,
      'force' => false,
      'injectWith' => null,
      'injectAs' => null,
      'newClass' => false,
    );
  }

  /**
   * This function builds an array of options
   * for each of the commands that were matched.
   * This options array is readable/similar to
   * a dependency map item.
   *
   */
  public function buildOptions()
  {
    foreach($this->_matches[1] as $command)
    {
      $command = trim($command);
      $options = $this->_defaultOptions();
      if($command != "")
      {
        /*
         * Valid commands look something like this:
         *
         * @inject new:Class
         * @inject DependencyName
         * @inject DependencyName method:setName force:true
         * @inject DependencyName property:name
         * @inject DependencyName constructor:1
         */
        $params = explode(" ", $command);
        for($i = 0; $i < count($params); $i++)
        {
          $parts = explode(":", $params[$i]);
          if(count($parts) != 2)
          {
            if($i == 0)
            {
              // dependency name
              $options['dependencyName'] = $params[$i];
            }
            else
            {
              throw new sfParseException(sprintf('Invalid option "%s". Correct syntax is Option:Value. Info: %s', 
                  $params[$i], $this->_info));
            }
          }
          else
          {
            // option
            $key = $parts[0];
            $value = $parts[1];
            if($key == 'force')
            {
              $options['force'] = $value;
            }
            elseif($key == 'new')
            {
              $options['newClass'] = $value;
            }
            else
            {
              $options['injectWith'] = $key;
              $options['injectAs'] = $value;
            }
          }
        }
      }
      // congrats, you made it out of nesting hell alive
      $this->_options[] = $options;
    }
  }

  /**
   * Returns all of the options, an an array.
   *
   * And option is an array of data
   *
   * @return array
   */
  public function getOptions()
  {
    return $this->_options;
  }

}
