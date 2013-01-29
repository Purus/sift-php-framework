<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMenu class provides simple menu
 *
 * @package    Sift
 * @subpackage menu
 * @author     Jonathan H. Wage
 * @author     Mishal.cz <mishal@mishal.cz>
 */
class sfMenu implements ArrayAccess, Countable, IteratorAggregate {

  protected
          $_name = null,
          $_priority = 0,
          $_route = null,
          $_level = null,
          $_parent = null,
          $_root = null,
          $_num = null,
          $_requiresAuth = null,
          $_requiresNoAuth = null,
          $_showChildren = true,
          $_current = false,
          $_options = array(),
          $_children = array(),
          $_credentials = array();

  public function __construct($name, $route = null, $options = array())
  {
    $this->_name = $name;
    $this->_route = $route;
    $this->_options = $options;

    sfLoader::loadHelpers(array('Tag', 'Url'));
  }

  /**
   * Returns route
   * 
   * @return string
   */
  public function getRoute()
  {
    return $this->_route;
  }

  public function setRoute($route)
  {
    $this->_route = $route;
    return $this;
  }

  public function getOptions()
  {
    return $this->_options;
  }

  public function setOptions($options)
  {
    $this->_options = $options;

    return $this;
  }

  public function getOption($name, $default = null)
  {
    if(isset($this->_options[$name]))
    {
      return $this->_options[$name];
    }
    return $default;
  }

  public function setOption($name, $value)
  {
    $this->_options[$name] = $value;
    return $this;
  }

  public function requiresAuth($bool = null)
  {
    if(!is_null($bool))
    {
      $this->_requiresAuth = $bool;
    }
    return $this->_requiresAuth;
  }

  public function requiresNoAuth($bool = null)
  {
    if(!is_null($bool))
    {
      $this->_requiresNoAuth = $bool;
    }
    return $this->_requiresNoAuth;
  }

  /**
   * Sets credentials for this menu item
   * 
   * @param array $credentials
   * @return sfMenu
   */
  public function setCredentials($credentials)
  {
    $this->_credentials = is_string($credentials) ? explode(',', $credentials) : (array) $credentials;
    return $this;
  }

  public function getCredentials()
  {
    return $this->_credentials;
  }

  public function hasCredentials()
  {
    return !empty($this->_credentials);
  }

  /**
   * Sets or gets show children setting
   *
   * @param bolean $bool
   * @return boolean
   */
  public function showChildren($bool = null)
  {
    if(!is_null($bool))
    {
      $this->_showChildren = $bool;
    }
    return $this->_showChildren;
  }

  /**
   * Checks if given user has acces to this item
   *
   * @param sfUser $user
   * @return boolean
   */
  public function checkUserAccess(sfUser $user = null)
  {
    // no context instance
    // or no credentials assigned
    if(!sfContext::hasInstance() || !$this->hasCredentials())
    {
      return true;
    }

    if(is_null($user))
    {
      $user = sfContext::getInstance()->getUser();
    }

    // authenticated user, but item required no authentication
    if($user->isAuthenticated() && $this->requiresNoAuth())
    {
      return false;
    }

    // not authenticated user, but item requires authentication
    if(!$user->isAuthenticated() && $this->requiresAuth())
    {
      return false;
    }

    return $user->hasCredential($this->_credentials);
  }

  /**
   * Sets item level
   *
   * @param integer $level
   * @return sfMenu
   */
  public function setLevel($level)
  {
    $this->_level = $level;
    return $this;
  }

  public function getLevel()
  {
    if(is_null($this->_level))
    {
      $count = -2;
      $obj = $this;
      do
      {
        $count++;
      }
      while($obj = $obj->getParent());
      $this->_level = $count;
    }
    return $this->_level;
  }

  public function getRoot()
  {
    if(is_null($this->_root))
    {
      $obj = $this;
      do
      {
        $found = $obj;
      }
      while($obj = $obj->getParent());
      $this->_root = $found;
    }
    return $this->_root;
  }

  public function getParent()
  {
    return $this->_parent;
  }

  public function setParent(sfMenu $parent)
  {
    return $this->_parent = $parent;
  }

  public function getPriority()
  {
    return $this->_priority;
  }

  public function setPriority($priority)
  {
    $this->_priority = $priority;
    return $this;
  }

  public function getName()
  {
    return $this->_name;
  }

  public function setName($name)
  {
    $this->_name = $name;
    return $this;
  }

  public function getChildren()
  {
    return $this->_children;
  }

  public function setChildren(array $children)
  {
    $this->_children = $children;
    return $this;
  }

  public function addChild($child, $route = null, $options = array())
  {
    if(!$child instanceof sfMenu)
    {
      $class = get_class($this);
      $child = new $class($child, $route, $options);
    }

    $child->setParent($this);
    $child->showChildren($this->showChildren());
    $child->setNum($this->count() + 1);

    $this->_children[$child->getName()] = $child;

    return $child;
  }

  /**
   * Removes a child from this menu item
   *
   * @param mixed $name The name of sfSympalMenu instance to remove
   */
  public function removeChild($name)
  {
    $name = ($name instanceof sfMenu) ? $name->getName() : $name;

    if(isset($this->_children[$name]))
    {
      unset($this->_children[$name]);
    }
  }

  public function getNum()
  {
    return $this->_num;
  }

  public function setNum($num)
  {
    $this->_num = $num;
  }

  public function getFirstChild()
  {
    return current($this->_children);
  }

  public function getLastChild()
  {
    return end($this->_children);
  }

  public function getChild($name)
  {
    if(!isset($this->_children[$name]))
    {
      $this->addChild($name);
    }

    return $this->_children[$name];
  }

  public function hasChildren()
  {
    $children = array();
    foreach($this->_children as $child)
    {
      if($child->checkUserAccess())
      {
        $children[] = $child;
      }
    }
    return !empty($children);
  }

  public function __toString()
  {
    try
    {
      return (string) $this->render();
    }
    catch(Exception $e)
    {
      return $e->getMessage();
    }
  }

  public function render()
  {
    $timer = sfTimerManager::getTimer('Menu');
    if($this->checkUserAccess() && $this->hasChildren())
    {
      $html = '<ul>';
      foreach($this->_children as $child)
      {
        $html .= $child->renderChild();
      }
      $html .= '</ul>';
      $timer->addTime();
      return $html;
    }
  }

  public function renderChildren()
  {
    $html = '';
    foreach($this->_children as $child)
    {
      $html .= $child->renderChild();
    }
    return $html;
  }

  /**
   * Renders child item
   * 
   * @return string|void
   */
  public function renderChild()
  {
    if($this->checkUserAccess())
    {
      $classes = array();
      $class = $this->getClass();
      if($class)
      {
        $classes[] = $class;
      }

      if($this->isCurrent())
      {
        $classes[] = 'current';
      }
      if($this->isFirst())
      {
        $classes[] = 'first';
      }
      if($this->isLast())
      {
        $classes[] = 'last';
      }

      $id = false;
      if($this->getId())
      {
        $id = $this->getId();
      }

      $html = sprintf('<li%s%s>', count($classes) ? sprintf(' class="%s"', join(' ', $classes)) : null, $id ? sprintf(' id="%s"', $id) : null);

      // $html = sprintf('<li%s>', $this->isCurrent() ? ' class="current"' : null);
      $html .= $this->renderChildBody();
      if($this->hasChildren() && $this->showChildren())
      {
        $html .= $this->render();
      }
      $html .= '</li>';
      return $html;
    }
  }

  protected function isChildCurrent()
  {
    $current = false;
    if($this->isCurrent())
    {
      return true;
    }
    else
    {
      foreach($this->getChildren() as $child)
      {
        if($child->isChildCurrent())
        {
          return true;
        }
      }
    }
    return $current;
  }

  public function renderChildBody()
  {
    if($this->_route)
    {
      $html = $this->renderLink();
    }
    else
    {
      $html = $this->renderLabel();
    }
    return $html;
  }

  public function renderLink()
  {
    sfLoader::loadHelpers('Url');
    $options = $this->getOptions();

    if(!isset($options['title']))
    {
      $options['title'] = $this->getLabel();
    }

    return link_to($this->renderLabel(), $this->getRoute(), $options);
  }

  protected function generateUrl($internal_uri, $absolute = false)
  {
    static $controller;
    if(!isset($controller))
    {
      $controller = sfContext::getInstance()->getController();
    }
    return $controller->genUrl($internal_uri, $absolute);
  }

  public function renderLabel()
  {
    return $this->getLabel();
  }

  /**
   * Sets or returns if node item is current
   * 
   * @param boolean $bool
   * @return boolean
   */
  public function isCurrent($bool = null)
  {
    if(!is_null($bool))
    {
      $this->_current = $bool;
    }
    return $this->_current;
  }

  public function isLast()
  {
    return $this->getNum() == $this->getParent()->count() ? true : false;
  }

  public function isFirst()
  {
    return $this->getNum() == 1 ? true : false;
  }

  public function getLabel()
  {
    return (is_array($this->_options) && isset($this->_options['label'])) ? $this->_options['label'] : $this->_name;
  }

  public function setLabel($label)
  {
    $this->_options['label'] = $label;

    return $this;
  }

  public function getPathAsString()
  {
    $children = array();
    $obj = $this;

    do
    {
      $children[] = $obj->getLabel();
    }
    while($obj = $obj->getParent());

    return implode(' > ', array_reverse($children));
  }

  public function callRecursively()
  {
    $args = func_get_args();
    $arguments = $args;
    unset($arguments[0]);

    call_user_func_array(array($this, $args[0]), $arguments);

    foreach($this->_children as $child)
    {
      call_user_func_array(array($child, 'callRecursively'), $args);
    }

    return $this;
  }

  public function toArray()
  {
    $array = array();
    $array['name'] = $this->getName();
    $array['level'] = $this->getLevel();
    $array['is_current'] = $this->isCurrent();
    $array['priority'] = $this->getPriority();
    $array['options'] = $this->getOptions();
    foreach($this->_children as $key => $child)
    {
      $array['children'][$key] = $child->toArray();
    }
    return $array;
  }

  public function fromArray($array)
  {
    if(isset($array['name']))
    {
      $this->setName($array['name']);
    }

    if(isset($array['level']))
    {
      $this->setLevel($array['level']);
    }

    if(isset($array['is_current']))
    {
      $this->isCurrent($array['is_current']);
    }

    if(isset($array['priority']))
    {
      $this->setPriority($array['priority']);
    }

    if(isset($array['options']))
    {
      $this->setOptions($array['options']);
    }

    if(isset($array['children']))
    {
      foreach($array['children'] as $name => $child)
      {
        $this->addChild($name)->fromArray($child);
      }
    }

    return $this;
  }

  public function sortByPriority()
  {
    $culture = sfConfig::get('sf_default_culture');

    // FIXME: only utf-8 is supported
    setlocale(LC_COLLATE, sprintf('%s.utf8', $culture), sprintf('%s.UTF-8', $culture));

    uasort($this->_children, array($this, "_sortByPriority"));

    // fluid interface
    return $this;
  }

  public function sortAllByPriority()
  {
    $this->callRecursively('sortByPriority');
  }

  protected function _sortByPriority(&$a, &$b)
  {
    if($a->getPriority() == $b->getPriority())
    {
      return strcoll($a->getName(), $b->getName());
    }
    else
    {
      return $a->getPriority() > $b->getPriority() ? -1 : 1;
    }
  }

  /**
   * __call
   *
   * @param string $m
   * @param string $a
   * @return void
   */
  public function __call($method, $arguments)
  {
    if(method_exists($this, $method))
    {
      return call_user_func_array($method, $arguments);
    }

    $verb = substr($method, 0, 3);
    $column = substr($method, 3);

    // first character lowercase
    $column[0] = strtolower($column[0]);
    if($verb == 'get')
    {
      return isset($this->$column) ? $this->$column : false;
    }
    elseif($verb == 'set')
    {
      return $this->$column = $arguments[0];
    }

    $class = get_class($this);
    $class = str_replace('sfMenu', '', $class);
    $name = $class ? 'menu.' . sfInflector::tableize($class) : 'menu';
    $name .= '.method_not_found';

    $event = sfCore::getEventDispatcher()->notifyUntil(new sfEvent($name, array(
                'method' => $method, 'arguments' => $arguments, 'menu' => $this
            )));

    if(!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }

  /**
   * Countable interface
   *
   * @return integer
   */
  public function count()
  {
    return count($this->_children);
  }

  /**
   * Returns iteratorAggregate
   *
   * @return ArrayObject Iterator
   */
  public function getIterator()
  {
    return new ArrayObject($this->_children);
  }

  public function add($value)
  {
    return $this->addChild($value)->setLabel($value);
  }

  public function current()
  {
    return current($this->_children);
  }

  public function next()
  {
    return next($this->_children);
  }

  public function key()
  {
    return key($this->_children);
  }

  public function valid()
  {
    return $this->current() !== false;
  }

  public function rewind()
  {
    return reset($this->_children);
  }

  public function offsetExists($name)
  {
    return isset($this->_children[$name]);
  }

  public function offsetGet($name)
  {
    return $this->getChild($name);
  }

  public function offsetSet($name, $value)
  {
    return $this->addChild($name)->setLabel($value);
  }

  public function offsetUnset($name)
  {
    unset($this->_children[$name]);
  }

}