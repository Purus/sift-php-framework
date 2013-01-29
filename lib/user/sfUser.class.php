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
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org> 
 */
class sfUser {

  /**
   * The namespace under which attributes will be stored.
   */
  const ATTRIBUTE_NAMESPACE = 'sift/user/sfUser/attributes';

  const CULTURE_NAMESPACE = 'sift/user/sfUser/culture';

  protected
  $parameterHolder = null,
  $attributeHolder = null,
  $culture = null,
  $context = null;

  /**
   * Retrieve the current application context.
   *
   * @return Context A Context instance.
   */
  public function getContext()
  {
    return $this->context;
  }

  /**
   * Initialize this User.
   *
   * @param Context A Context instance.
   * @param array   An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise
   *              false.
   *
   * @throws sfInitializationException If an error occurs while initializing this User.
   */
  public function initialize($context, $parameters = array())
  {
    $this->context = $context;

    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);

    $this->attributeHolder = new sfParameterHolder(self::ATTRIBUTE_NAMESPACE);

    // read attributes from storage
    $attributes = $context->getStorage()->read(self::ATTRIBUTE_NAMESPACE);
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
    if(!($culture = $context->getRequest()->getParameter('sf_culture')))
    {
      if(null === ($culture = $context->getStorage()->read(self::CULTURE_NAMESPACE)))
      {
        $culture = sfConfig::get('sf_i18n_default_culture', 'en');
      }
    }

    sfConfig::set('sf_culture', $culture);

    $this->setCulture($culture);
  }

  /**
   * Retrieve a new sfUser implementation instance.
   *
   * @param string A sfUser implementation name
   *
   * @return User A sfUser implementation instance.
   *
   * @throws sfFactoryException If a user implementation instance cannot
   */
  public static function newInstance($class)
  {
    // the class exists
    $object = new $class();

    if(!($object instanceof sfUser))
    {
      // the class name is of the wrong type
      throw new sfFactoryException(sprintf('Class "%s" is not of the type sfUser', $class));
    }

    return $object;
  }

  /**
   * Sets culture.
   *
   * @param  string culture
   */
  public function setCulture($culture)
  {
    if($this->culture != $culture)
    {
      if($this->culture != null)
      {
        // dispatch event
        sfCore::dispatchEvent('user.change_culture', array('culture' => $culture));
      }

      $this->culture = $culture;

      // change the message format object with the new culture
      if(sfConfig::get('sf_i18n'))
      {
        $this->context->getI18n()->setCulture($culture);
      }

      // add the culture in the routing default parameters
      sfConfig::set('sf_routing_defaults', array_merge((array) sfConfig::get('sf_routing_defaults'), array('sf_culture' => $culture)));
    }
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
    return sfContext::getInstance()->getRequest()->getIp();
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
    return sfContext::getInstance()->getRequest()->getIpForwardedFor();
  }

  /**
   * Returns hostname of the user IP
   * 
   * @return string
   */
  public function getHostname()
  {
    return sfContext::getInstance()->getRequest()->getHostname();
  }

  /**
   * Returns user agent of the site visitor
   * 
   * @return string
   */
  public function getUserAgent()
  {
    return sfContext::getInstance()->getRequest()->getUserAgent();
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
  
  public function getBrowserVersion()
  {
    $browser = $this->getBrowser();
    return $browser['version'];
  }
  
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

  public function setTimezone($timezone)
  {
    date_default_timezone_set($timezone);
    sfCore::getEventDispatcher()->notify(new sfEvent('user.change_timezone',
                    array('method' => 'setTimezone', 'timezone' => $timezone)));
  }

  /**
   * Returns timezone
   * 
   * @return string
   */
  public function getTimezone()
  {
    $request = sfContext::getInstance()->getRequest();
    $offset = $request->getCookie('timezone_offset');
    $dst    = $request->getCookie('timezone_daylightsavings');

    if($offset !== null && $dst !== null)
    {
      $offset *= 3600;
      $zone   = timezone_name_from_abbr('', $offset, $dst);
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
    $storage = $this->getContext()->getStorage();

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
    $event = sfCore::getEventDispatcher()->notifyUntil(
      new sfEvent('user.method_not_found', array(
          'user'      => $this,
          'method'    => $method,
          'arguments' => $arguments)));

    if(!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }

}
