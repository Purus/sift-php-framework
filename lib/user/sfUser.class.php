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
class sfUser implements sfIService {

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
   * Parameter holder
   *
   * @var sfParameterHolder
   */
  protected $parameterHolder;

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
   * Service container
   *
   * @var sfServiceContainer
   */
  protected $serviceContainer;

  /**
   * Construct the user
   *
   * @param sfServiceContainer $serviceContainer
   * @param array $parameters An associative array of initialization parameters.
   */
  public function __construct(sfServiceContainer $serviceContainer, $parameters = array())
  {
    $this->serviceContainer = $serviceContainer;

    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);

    $this->attributeHolder = new sfParameterHolder(self::ATTRIBUTE_NAMESPACE);

    // read attributes from storage
    $attributes = $this->serviceContainer->get('storage')->read(self::ATTRIBUTE_NAMESPACE);
    if(is_array($attributes))
    {
      foreach($attributes as $namespace => $values)
      {
        $this->attributeHolder->add($values, $namespace);
      }
    }

    // set the user culture to sf_culture parameter if present in the request
    // otherwise
    //  - use the culture defined in the user session
    //  - use the default culture set in i18n.yml
    if(!($culture = $this->serviceContainer->get('request')->getParameter('sf_culture')))
    {
      if(null === ($culture = $this->serviceContainer->get('storage')->read(self::CULTURE_NAMESPACE)))
      {
        $culture = sfConfig::get('sf_i18n_default_culture', 'en');
      }
    }

    $this->setCulture($culture);
  }

  /**
   * Sets culture.
   *
   * @param  string culture
   */
  public function setCulture($culture)
  {
    if($culture === null)
    {
      $culture = sfConfig::get('sf_i18n_default_culture');
    }

    // dispatch event
    $this->serviceContainer->get('event_dispatcher')->notify(new sfEvent('user.change_culture', array(
        'previous' => $this->culture,
        'culture' => $culture)));

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
    return $this->serviceContainer->get('request')->getIp();
  }

  /**
   * Returns "REAL" IP address (in case of a proxy)
   *
   * @return string
   */
  public function getRealIp()
  {
    return $this->getIpForwardedFor() ?
            $this->getIpForwardedFor() : $this->getIp();
  }

  /**
   * Returns IP address of the user
   *
   * @return string
   */
  public function getIpForwardedFor()
  {
    return $this->serviceContainer->get('request')->getIpForwardedFor();
  }

  /**
   * Returns hostname of the user IP
   *
   * @return string
   */
  public function getHostname()
  {
    return $this->serviceContainer->get('request')->getHostname();
  }

  /**
   * Returns user agent of the site visitor
   *
   * @return string
   */
  public function getUserAgent()
  {
    return $this->serviceContainer->get('request')->getUserAgent();
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
    date_default_timezone_set($timezone);
    $this->serviceContainer->get('event_dispatcher')->notify(new sfEvent('user.change_timezone', array('method' => 'setTimezone', 'timezone' => $timezone)));
  }

  /**
   * Returns timezone
   *
   * @return string
   */
  public function getTimezone()
  {
    $request = $this->context->getRequest();
    $offset = $request->getCookie('timezone_offset');
    $dst = $request->getCookie('timezone_daylightsavings');

    if($offset !== null && $dst !== null)
    {
      $offset *= 3600;
      $zone = timezone_name_from_abbr('', $offset, $dst);
      if($zone !== false)
      {
        $this->setTimezone($zone);
        return $zone;
      }
    }

    return date_default_timezone_get();
  }

  /**
   * Returns parameter holder
   *
   * @return sfParameterHolder
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
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

  public function getParameter($name, $default = null, $ns = null)
  {
    return $this->parameterHolder->get($name, $default, $ns);
  }

  public function hasParameter($name, $ns = null)
  {
    return $this->parameterHolder->has($name, $ns);
  }

  public function setParameter($name, $value, $ns = null)
  {
    return $this->parameterHolder->set($name, $value, $ns);
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown()
  {
    $storage = $this->serviceContainer->get('storage');
    $attributes = array();
    foreach($this->attributeHolder->getNamespaces() as $namespace)
    {
      $attributes[$namespace] = $this->attributeHolder->getAll($namespace);
    }
    // write attributes to the storage
    $storage->write(self::ATTRIBUTE_NAMESPACE, $attributes);
    // write culture to the storage
    $storage->write(self::CULTURE_NAMESPACE, $this->culture);
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
    $event = $this->serviceContainer->get('event_dispatcher')->notifyUntil(
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
