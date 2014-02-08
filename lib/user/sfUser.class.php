<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfUser wraps a client session and provides accessor methods for user
 * attributes. It also makes storing and retrieving multiple page form data
 * rather easy by allowing user attributes to be stored in namespaces, which
 * help organize data.
 *
 * @package    Sift
 * @subpackage user
 */
class sfUser extends sfConfigurable implements sfIUser, sfIService, ArrayAccess {

  /**
   * Attributes namespace
   */
  const ATTRIBUTE_NAMESPACE = 'sift/user/sfUser/attributes';

  /**
   * Culture namespace
   */
  const CULTURE_NAMESPACE = 'sift/user/sfUser/culture';

  /**
   * Flash messages namespace
   */
  const FLASH_NAMESPACE = 'sift/flash';

  /**
   * Attribute holder
   *
   * @var sfParameterHolder
   */
  protected $attributeHolder;

  /**
   * User culture
   *
   * @var string
   */
  protected $culture;

  /**
   * User timezone
   *
   * @var string
   */
  protected $timezone;

  /**
   * The dispatcher instance
   *
   * @var sfEventDispatcher
   */
  protected $dispatcher;

  /**
   * The storage instance
   *
   * @var sfIstorage
   */
  protected $storage;

  /**
   * The web request
   *
   * @var sfWebRequest
   */
  protected $request;

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'default_culture' => 'en',
    'use_flash' => true,
    // is timezone feature enabled?
    'timezone_enabled' => true,
    // cookie names for timezones
    'timezone_cookie_names' => array(
      'name' => 'timezone_name',
      'offset' => 'timezone_offset',
      'daylightsavings' => 'timezone_daylightsavings'
    )
  );

  /**
   * Constructor
   *
   * @param sfEventDispatcher $dispatcher The event dispatcher
   * @param sfIStorage $storage The storage
   * @param sfWebRequest $request The web request
   * @param array $options Options
   * @inject dispatcher
   * @inject storage
   * @inject request
   */
  public function __construct(sfEventDispatcher $dispatcher, sfIStorage $storage, sfWebRequest $request, $options = array())
  {
    $this->dispatcher = $dispatcher;
    $this->storage = $storage;
    $this->request = $request;

    $this->attributeHolder = new sfParameterHolder(self::ATTRIBUTE_NAMESPACE);
    parent::__construct($options);
  }

  /**
   * Returns the storage
   *
   * @return sfIStorage
   */
  public function getStorage()
  {
    return $this->storage;
  }

  /**
   * Returns the request
   *
   * @return sfWebRequest
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Returns the event dispatcher
   *
   * @return sfEventDispatcher
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  /**
   * Setups the user instance
   *
   */
  public function setup()
  {
    $this->attributeHolder->clear();

    // start the storage if its not started
    if(!$this->storage->isStarted())
    {
      $this->storage->start();
    }

    // read attributes from storage
    $attributes = $this->storage->read(self::ATTRIBUTE_NAMESPACE);
    if(is_array($attributes))
    {
      foreach($attributes as $namespace => $values)
      {
        $this->attributeHolder->add($values, $namespace);
      }
    }

    // use the culture defined in the user session
    // or use the default_culture option
    if(null === ($culture = $this->storage->read(self::CULTURE_NAMESPACE)))
    {
      $culture = $this->getOption('default_culture');
    }

    $this->setCulture($culture);

    if($this->getOption('timezone_enabled'))
    {
      // setup timezone
      $offset = $this->request->getCookie($this->getOption('timezone_cookie_names.offset'));
      $daylightsavings = $this->request->getCookie($this->getOption('timezone_cookie_names.daylightsavings'));
      $name = $this->request->getCookie($this->getOption('timezone_cookie_names.name'));
      // we have a timezone name
      if(sfDateTimeZone::isValid($name))
      {
        $this->setTimezone($name);
      }
      // do we have an offset?
      elseif($offset !== null && ($zone = sfDateTimeZone::getNameFromOffset($offset, (boolean)$daylightsavings)))
      {
        $this->setTimezone($zone);
      }
    }

    if($this->getOption('use_flash'))
    {
      // flag current flash to be removed after the execution filter
      $names = $this->attributeHolder->getNames(self::FLASH_NAMESPACE);
      if($names)
      {
        foreach($names as $name)
        {
          $this->attributeHolder->set($name, true, sfUser::FLASH_NAMESPACE.'/remove');
        }
        if(sfConfig::get('sf_logging_enabled'))
        {
          sfLogger::getInstance()->info('{sfUser} Flagged old flash messages ("' . implode('", "', $names) . '")');
        }
      }
    }
  }

  /**
   * Sets culture.
   *
   * @param string $culture Culture
   */
  public function setCulture($culture)
  {
    if($culture === null)
    {
      $culture = $this->getOption('default_culture');
    }

    // dispatch event only if current culture is not the same as new culture
    if($this->culture && ($this->culture !== $culture))
    {
      $this->dispatcher->notify(new sfEvent('user.change_culture', array(
        'previous' => $this->culture,
        'culture' => $culture)
      ));
    }

    $this->culture = $culture;

    sfConfig::set('sf_culture', $culture);

    // add the culture in the routing default parameters
    sfConfig::set('sf_routing_defaults', array_merge((array) sfConfig::get('sf_routing_defaults'), array('sf_culture' => $culture)));
  }

  /**
   * Gets culture.
   *
   * @return string
   */
  public function getCulture()
  {
    return $this->culture;
  }

  /**
   * Returns user IP address
   *
   * @return string
   */
  public function getIp()
  {
    return $this->request->getIp();
  }

  /**
   * Returns "REAL" IP address (in case of a proxy)
   *
   * @return string
   */
  public function getRealIp()
  {
    return $this->getIpForwardedFor() ? $this->getIpForwardedFor() : $this->getIp();
  }

  /**
   * Returns IP address of the user
   *
   * @return string
   */
  public function getIpForwardedFor()
  {
    return $this->request->getIpForwardedFor();
  }

  /**
   * Returns hostname of the user IP
   *
   * @return string
   */
  public function getHostname()
  {
    return $this->request->getHostname();
  }

  /**
   * Returns user agent of the site visitor
   *
   * @return string
   */
  public function getUserAgent()
  {
    return $this->request->getUserAgent();
  }

  /**
   * Returns browser name of the visitor user agent
   *
   * @return string
   */
  public function getBrowserName()
  {
    $browser = $this->getBrowser();

    return $browser['name'];
  }

  /**
   * Returns browser version
   * @return string
   */
  public function getBrowserVersion()
  {
    $browser = $this->getBrowser();

    return $browser['version'];
  }

  /**
   * Returns browser (aka user agent)
   *
   * @return string
   */
  public function getBrowser()
  {
    if(!$this->hasAttribute('browser_guessed', self::ATTRIBUTE_NAMESPACE))
    {
      $guess = sfUserAgentDetector::guess($this->getUserAgent());
      $this->setAttribute('browser_guessed', $guess, self::ATTRIBUTE_NAMESPACE);
    }

    return $this->getAttribute('browser_guessed', null, self::ATTRIBUTE_NAMESPACE);
  }

  /**
   * Detects if the user is bot (Google, Yahoo, Seznam)...
   *
   * @return boolean
   */
  public function isBot()
  {
    $browser = $this->getBrowser();

    return $browser['is_bot'];
  }

  /**
   * Returns true is user agent is mobile device
   *
   * @return boolean
   */
  public function isMobile()
  {
    $browser = $this->getBrowser();

    return $browser['is_mobile'];
  }

  /**
   * Sets timezone
   *
   * @param string $timezone
   */
  public function setTimezone($timezone)
  {
    $old = $this->timezone;
    $this->timezone = $timezone;
    $this->dispatcher->notify(new sfEvent('user.change_timezone', array(
      'user' => $this,
      'timezone' => $timezone,
      'old' => $old
      )
    ));
  }

  /**
   * Returns timezone
   *
   * @return string
   */
  public function getTimezone()
  {
    return $this->timezone ? $this->timezone : date_default_timezone_get();
  }

  /**
   * Retrieve referer
   *
   * @param mixed Default value
   */
  public function getReferer($default = null)
  {
    $referer = $this->getAttribute('referer', $default);

    return $referer ? $referer : $default;
  }

  /**
   * Sets referer (Uri which the user came from)
   *
   * @param string URL the user came from
   */
  public function setReferer($referer)
  {
    $this->setAttribute('referer', $referer);

    return $this;
  }

  /**
   * Sets a flash variable that will be passed to the very next action.
   *
   * @param string $name The name of the flash variable
   * @param string $value The value of the flash variable
   * @param bool $persist true if the flash have to persist for the following request (true by default)
   * @return sfUser
   */
  public function setFlash($name, $value, $persist = true)
  {
    $flash = new sfUserFlashMessage($value, $name, sfConfig::get('sf_app'));
    $this->setAttribute($name, $flash, self::FLASH_NAMESPACE);
    if($persist)
    {
      // clear removal flag
      $this->attributeHolder->remove($name, null, self::FLASH_NAMESPACE.'/remove');
    }
    else
    {
      $this->setAttribute($name, true, self::FLASH_NAMESPACE.'/remove');
    }

    return $this;
  }

  /**
   * Gets a flash variable.
   *
   * @param string $name The name of the flash variable
   * @param string $default The default value returned when named variable does not exist.
   * @param boolean $ignoreApplication Return the flash even for different application?
   * @return mixed The value of the flash variable
   */
  public function getFlash($name, $default = null, $ignoreApplication = false)
  {
    if($this->hasAttribute($name, self::FLASH_NAMESPACE))
    {
      $flash = $this->getAttribute($name, null, self::FLASH_NAMESPACE);
      if($ignoreApplication || $flash->getApplication() == sfConfig::get('sf_app'))
      {
        return $flash;
      }
    }

    return $default;
  }

  /**
   * Returns true if a flash variable of the specified name exists.
   *
   * @param string $name The name of the flash variable
   * @param boolean $ignoreApplication Return the flash even for different application?
   * @return bool true if the variable exists, false otherwise
   */
  public function hasFlash($name, $ignoreApplication = false)
  {
    if($this->getFlash($name, false, $ignoreApplication))
    {
      return true;
    }

    return false;
  }

  /**
   * Returns true if the user attribute exists (implements the ArrayAccess interface).
   *
   * @param  string $name The name of the user attribute
   *
   * @return Boolean true if the user attribute exists, false otherwise
   */
  public function offsetExists($name)
  {
    return $this->hasAttribute($name);
  }

  /**
   * Returns the user attribute associated with the name (implements the ArrayAccess interface).
   *
   * @param  string $name  The offset of the value to get
   *
   * @return mixed The user attribute if exists, null otherwise
   */
  public function offsetGet($name)
  {
    return $this->getAttribute($name, false);
  }

  /**
   * Sets the user attribute associated with the offset (implements the ArrayAccess interface).
   *
   * @param string $offset The parameter name
   * @param string $value The parameter value
   */
  public function offsetSet($offset, $value)
  {
    $this->setAttribute($offset, $value);
  }

  /**
   * Unsets the user attribute associated with the offset (implements the ArrayAccess interface).
   *
   * @param string $offset The parameter name
   */
  public function offsetUnset($offset)
  {
    $this->getAttributeHolder()->remove($offset);
  }

  /**
   * Returns attribute holder
   *
   * @return sfParameterHolder
   */
  public function getAttributeHolder()
  {
    return $this->attributeHolder;
  }

  public function getAttribute($name, $default = null, $ns = null)
  {
    return $this->attributeHolder->get($name, $default, $ns);
  }

  public function hasAttribute($name, $ns = null)
  {
    return $this->attributeHolder->has($name, $ns);
  }

  public function setAttribute($name, $value, $ns = null)
  {
    return $this->attributeHolder->set($name, $value, $ns);
  }

  public function removeAttribute($name, $ns = null)
  {
    return $this->attributeHolder->remove($name, $ns);
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown()
  {
    // remove flash that are tagged to be removed
    if($this->getOption('use_flash') && ($names = $this->attributeHolder->getNames(sfUser::FLASH_NAMESPACE.'/remove')))
    {
      foreach($names as $name)
      {
        $this->attributeHolder->remove($name, sfUser::FLASH_NAMESPACE);
        $this->attributeHolder->remove($name, sfUser::FLASH_NAMESPACE.'/remove');
      }

      if(sfConfig::get('sf_logging_enabled'))
      {
        sfLogger::getInstance()->info('{sfFilter} Removed old flash messages ("' . implode('", "', $names) . '")');
      }
    }

    $attributes = array();
    foreach($this->attributeHolder->getNamespaces() as $namespace)
    {
      $attributes[$namespace] = $this->attributeHolder->getAll($namespace);
    }

    $this->timezone = null;
    // write attributes to the storage
    $this->storage->write(self::ATTRIBUTE_NAMESPACE, $attributes);
    // write culture to the storage
    $this->storage->write(self::CULTURE_NAMESPACE, $this->culture);
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @throws sfException If called method is undefined
   */
  public function __call($method, $arguments)
  {
    $event = $this->dispatcher->notifyUntil(
        new sfEvent('user.method_not_found', array(
        'user' => $this,
        'method' => $method,
        'arguments' => $arguments)));

    if(!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }

  /**
   * __toString magic method
   *
   * @return string
   */
  public function __toString()
  {
    return sprintf('[Instance of %s]', get_class($this));
  }

}
