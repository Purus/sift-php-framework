<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The parser takes a string and reads all of the @inject information.
 *
 * @package Sift
 * @subpackage dependency_injection
 */
class sfDependencyInjectionInjectCommandParser
{
  /**
   * Debug information
   *
   * @var string
   */
  protected $debugInformation = '';

  /**
   * The string to parse
   *
   * @var string
   */
  protected $string = '';

  /**
   * Constructs the parser
   *
   * @param string $string The string to parse
   * @param string $information The debug information
   */
  public function __construct($string = '', $debugInformation = '')
  {
    $this->string = $string;
    $this->debugInformation = $debugInformation;
  }

  /**
   * The string to parse
   *
   * @param string $string
   * @return sfDependencyInjectionInjectCommandParser
   */
  public function setString($string)
  {
    $this->string = (string) $string;

    return $this;
  }

  /**
   * Any information on whats about to be parsed. Usually this is a
   * Reflection method/property, but it can be anything.  Used when
   * throwing errors/exceptions or debugging.
   *
   * @param string $information
   * @return sfDependencyInjectionInjectCommandParser
   */
  public function setDebugInformation($information)
  {
    $this->debugInformation = $information;

    return $this;
  }

  /**
   * Returns an array of default options
   *
   * @return array
   */
  protected function getDefaultItemOptions()
  {
    return sfDependencyInjectionMapItem::getDefaultOptions();
  }

  /**
   * Parses the @inject command. Valid commands look something like this:
   *
   * <pre>
   * @inject new:Class
   * @inject dependencyName
   * @inject dependencyName required:true
   * @inject dependencyName method:setName force:true
   * @inject dependencyName property:name
   * @inject dependencyName constructor:1
   * </pre>
   * @param string $command The command to parse
   * @throws sfParseException If there is an error when parsing the string
   */
  protected function parseCommand($command)
  {
    $command = trim($command);
    $result = array();
    $params = array_map('trim', explode(' ', $command));

    for ($i = 0; $i < count($params); $i++) {
      $parts = array_map('trim', explode(':', $params[$i]));
      if (count($parts) != 2) {
        if ($i == 0) {
          // dependency name
          $result['dependency_name'] = $params[$i];
          continue;
        } else {
          throw new sfParseException(sprintf('Invalid option "%s" for command "%s". Correct syntax is Option:Value. %s', $params[$i], $command, $this->debugInformation));
        }
      }

      $key = $parts[0];
      $value = $parts[1];

      switch ($key) {
        case 'new':
          $result['new_class'] = $value;
        break;

        case 'force':
        case 'required':
          $result[$key] = $this->convertToBoolean($value);
        break;

        default:
          $result['inject_with'] = $key;
          $result['inject_as'] = $value;
        break;
      }
    }

    if (empty($result['dependency_name']) && empty($result['new_class'])) {
      throw new sfParseException(sprintf('Invalid command "%s"', $command, $this->debugInformation));
    }

    return array_merge($this->getDefaultItemOptions(), $result);
  }

  /**
   * Converts the value to boolean
   *
   * @param string $value
   * @return boolean
   */
  protected function convertToBoolean($value)
  {
    return sfDependencyInjectionMapItem::convertToBoolean($value);
  }

  /**
   * Parses the string for inject commands
   *
   * @return array Array of commands
   */
  public function parse()
  {
    if (!preg_match_all('/@inject(.*?)(\n|$)/i', $this->string, $matches)) {
      return false;
    }

    $commands = array();
    foreach ($matches[1] as $command) {
      $commands[] = $this->parseCommand($command);
    }

    return $commands;
  }

}
