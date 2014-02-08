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
 */
class sfMenu extends sfConfigurable implements ArrayAccess, Countable, IteratorAggregate
{
  /**
   * Menu name
   *
   * @var string
   */
  protected $name;

  /**
   * Route
   *
   * @var string
   */
  protected $route;

  /**
   * Does this menu require user to be authenticated?
   *
   * @var boolean
   */
  protected $requiresAuth = false;

  /**
   * Does this menu require user to be not authenticated?
   *
   * @var boolean
   */
  protected $requiresNoAuth = false;

  /**
   * Array of children items
   *
   * @var array
   */
  protected $children = array();

  /**
   * Show children?
   *
   * @var boolean
   */
  protected $showChildren = true;

  /**
   * Nesting level
   *
   * @var integer
   */
  protected $level;

  /**
   * Returns root element
   *
   * @var sfMenu
   */
  protected $root;

  /**
   * Parent item
   *
   * @var sfMenu
   */
  protected $parent;

  /**
   * Item priority
   *
   * @var integer
   */
  protected $priority = 0;

  /**
   * Item internal number
   *
   * @var integer
   */
  protected $number;

  /**
   * Is this current item?
   *
   * @var boolean
   */
  protected $current = false;

  /**
   * Array of credentials required for this menu
   *
   * @var array
   */
  protected $credentials = array();

  /**
   * Are helpers loaded?
   *
   * @var boolean
   */
  protected static $helpersLoaded = false;

  /**
   * Constructs the menu
   *
   * @param string $name
   * @param string $route
   * @param array $options
   */
  public function __construct($name, $route = null, $options = array())
  {
    parent::__construct($options);

    $this->name = $name;
    $this->route = $route;

    $this->loadHelpers();
  }

  /**
   * Loads view helpers
   *
   */
  protected function loadHelpers()
  {
    if (self::$helpersLoaded) {
      return;
    }

    sfLoader::loadHelpers(array('Tag', 'Url'));
    self::$helpersLoaded = true;
  }

  /**
   * Sets menu name
   *
   * @param string $name
   * @return sfMenu
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Returns menu name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Returns route
   *
   * @return string
   */
  public function getRoute()
  {
    return $this->route;
  }

  /**
   * Sets menu route
   *
   * @param string $route
   * @return sfMenu
   */
  public function setRoute($route)
  {
    $this->route = $route;

    return $this;
  }

  /**
   * Does this menu require user to be authenticated?
   *
   * @param boolean|null $bool If boolean value is given, the value will be set.
   * @return sfMenu|boolean
   */
  public function requiresAuth($bool = null)
  {
    if (!is_null($bool)) {
      $this->requiresAuth = (boolean) $bool;

      return $this;
    }

    return $this->requiresAuth;
  }

  /**
   * Does this menu require non authenticated access?
   *
   * @param boolean|null $bool
   * @return sfMenu|boolean
   */
  public function requiresNoAuth($bool = null)
  {
    if (!is_null($bool)) {
      $this->requiresNoAuth = $bool;

      return $this;
    }

    return $this->requiresNoAuth;
  }

  /**
   * Returns menu label
   *
   * @return string
   */
  public function getLabel()
  {
    return $this->getOption('label', $this->name);
  }

  /**
   * Sets menu label
   *
   * @param string $label
   * @return sfMenu
   */
  public function setLabel($label)
  {
    $this->setOption('label', $label);

    return $this;
  }

  /**
   * Sets credentials for this menu item
   *
   * @param array $credentials
   * @return sfMenu
   */
  public function setCredentials($credentials)
  {
    $this->credentials = is_string($credentials) ?
            explode(',', $credentials) : (array) $credentials;

    return $this;
  }

  /**
   * Returns menu credentials
   *
   * @return array
   */
  public function getCredentials()
  {
    return $this->credentials;
  }

  /**
   * Does the menu has any credentials?
   *
   * @return boolean
   */
  public function hasCredentials()
  {
    return !empty($this->credentials);
  }

  /**
   * Checks if given user has acces to this item
   *
   * @param sfUser $user
   * @return boolean
   */
  public function checkUserAccess(sfUser $user = null)
  {
    if (!sfContext::hasInstance()) {
      return true;
    }

    if (is_null($user)) {
      $user = sfContext::getInstance()->getUser();
    }

    if ($user->isAuthenticated() && $this->requiresNoAuth()) {
      return false;
    }

    if (!$user->isAuthenticated() && $this->requiresAuth()) {
      return false;
    }

    if ($this->hasCredentials()) {
      return $user->hasCredential($this->getCredentials());
    }

    return true;
  }

  /**
   * Sets or gets show children setting
   *
   * @param bolean $bool
   * @return boolean
   */
  public function showChildren($bool = null)
  {
    if (!is_null($bool)) {
      $this->showChildren = $bool;
    }

    return $this->showChildren;
  }

  /**
   * Sets item nesting level
   *
   * @param integer $level
   * @return sfMenu
   */
  public function setLevel($level)
  {
    $this->level = $level;

    return $this;
  }

  /**
   * Returns nesting level
   *
   * @return integer
   */
  public function getLevel()
  {
    if (is_null($this->level)) {
      $count = -2;
      $obj = $this;
      do {
        $count++;
      }
      while($obj = $obj->getParent());
      $this->level = $count;
    }

    return $this->level;
  }

  /**
   * Returns root item
   *
   * @return sfMenu|null
   */
  public function getRoot()
  {
    if (is_null($this->root)) {
      $obj = $this;
      do {
        $found = $obj;
      }
      while($obj = $obj->getParent());
      $this->root = $found;
    }

    return $this->root;
  }

  /**
   * Returns parent element
   *
   * @return sfMenu
   */
  public function getParent()
  {
    return $this->parent;
  }

  /**
   * Sets parent element
   *
   * @param sfMenu $parent
   * @return sfMenu The parent item
   */
  protected function setParent(sfMenu $parent)
  {
    return $this->parent = $parent;
  }

  /**
   * Returns priority
   * @return integer
   */
  public function getPriority()
  {
    return $this->priority;
  }

  /**
   * Sets priority
   *
   * @param integer $priority
   * @return sfMenu
   */
  public function setPriority($priority)
  {
    $this->priority = $priority;

    return $this;
  }

  /**
   * Returns children
   *
   * @return array
   */
  public function getChildren()
  {
    return $this->children;
  }

  /**
   * Sets children
   *
   * @param array $children
   * @return sfMenu
   */
  public function setChildren(array $children)
  {
    $this->children = $children;

    return $this;
  }

  /**
   * Add child item
   *
   * @param sfMenu|string $child
   * @param string $route
   * @param array $options
   * @return sfMenu
   */
  public function addChild($child, $route = null, $options = array())
  {
    if (!$child instanceof sfMenu) {
      $class = get_class($this);
      $child = new $class($child, $route, $options);
    }

    $child->setParent($this);
    $child->showChildren($this->showChildren());
    $child->setNumber($this->count() + 1);

    $this->children[$child->getName()] = $child;

    return $child;
  }

  /**
   * Removes a child from this menu item
   *
   * @param sfMenu|string $name The name of the sfMenu instance to remove
   * @return sfMenu
   */
  public function removeChild($name)
  {
    $name = ($name instanceof sfMenu) ? $name->getName() : $name;

    if (isset($this->children[$name])) {
      unset($this->children[$name]);
    }

    return $this;
  }

  /**
   * Return item number
   *
   * @return integer
   */
  public function getNumber()
  {
    return $this->number;
  }

  /**
   * Set item number
   *
   * @param integer $num
   */
  public function setNumber($num)
  {
    $this->number = $num;
  }

  /**
   * Returns first child
   *
   * @return sfMenu|false
   */
  public function getFirstChild()
  {
    return current($this->children);
  }

  /**
   * Returns last child
   *
   * @return sfMenu|false
   */
  public function getLastChild()
  {
    return end($this->children);
  }

  /**
   * Returns the child item. If the child does not exist, it will be created.
   *
   * @param string $name
   * @return sfMenu
   */
  public function getChild($name)
  {
    if (!isset($this->children[$name])) {
      $this->addChild($name);
    }

    return $this->children[$name];
  }

  /**
   * Checks if this item has any children
   *
   * @return boolean
   */
  public function hasChildren()
  {
    foreach ($this->children as $child) {
      if ($child->checkUserAccess() && $this->checkCondition()) {
        return true;
      }
    }

    return false;
  }

  /**
   * Is child current?
   *
   * @return boolean
   */
  protected function isChildCurrent()
  {
    $current = false;
    if ($this->isCurrent()) {
      return true;
    } else {
      foreach ($this->getChildren() as $child) {
        if ($child->isChildCurrent()) {
          return true;
        }
      }
    }

    return $current;
  }

  /**
   * Sets or returns if node item is current
   *
   * @param boolean $bool
   * @return boolean
   */
  public function isCurrent($bool = null)
  {
    if (!is_null($bool)) {
      $this->current = $bool;
    }

    return $this->current;
  }

  /**
   * Converts the object to string.
   *
   * @return string
   */
  public function __toString()
  {
    try {
      return (string) $this->render();
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }

  /**
   * Renders the menu
   *
   * @param array Array of options
   * @return string
   */
  public function render($options = array())
  {
    $html = '';

    if ($this->checkUserAccess() && $this->hasChildren() && $this->checkCondition()) {
      $html = '<ul>';
      foreach ($this->children as $child) {
        $html .= $child->renderChild();
      }
      $html .= '</ul>';
    }

    // output valid HTML code
    return $html == '<ul></ul>' ? '' : $html;
  }

  /**
   * Renders children
   *
   * @return string
   */
  public function renderChildren()
  {
    $html = '';
    foreach ($this->children as $child) {
      $html .= $child->renderChild();
    }

    return $html;
  }

  /**
   * Renders child item
   *
   * @return string
   */
  public function renderChild()
  {
    $html = '';

    // can user access this item?
    if ($this->checkUserAccess() && $this->checkCondition()) {
      $attributes = array();

      if ($id = $this->getId()) {
        $attributes['id'] = $id;
      }

      if ($classes = $this->getCssClasses()) {
        $attributes['class'] = join(' ', $classes);
      }

      $html = sfHtml::tag('li', $attributes, true) . $this->renderChildBody();

      if ($this->hasChildren() && $this->showChildren()) {
        $html .= $this->render();
      }

      $html .= '</li>';
    }

    return $html;
  }

  /**
   * Returns css classes for this item
   *
   * @return array Array of css classes
   */
  public function getCssClasses()
  {
    $classes = array();

    if ($class = $this->getClass()) {
      $classes[] = $class;
    }

    if ($this->isCurrent()) {
      $classes[] = 'current';
    }

    if ($this->isFirst()) {
      $classes[] = 'first';
    }

    if ($this->isLast()) {
      $classes[] = 'last';
    }

    return array_unique($classes);
  }

  /**
   * Render child nody
   *
   * @return string
   */
  public function renderChildBody()
  {
    if ($this->route) {
      return $this->renderLink();
    }

    return $this->renderLabel();
  }

  /**
   * Renders link
   *
   * @return string
   */
  public function renderLink()
  {
    $options = $this->getOptions();
    $options['title'] = $this->getOption('title', $this->getLabel());

    return link_to($this->renderLabel(), $this->getRoute(), $options);
  }

  /**
   * Renders label
   *
   * @return string
   */
  public function renderLabel()
  {
    return $this->getLabel();
  }

  /**
   * Is this last item?
   *
   * @return boolean
   */
  public function isLast()
  {
    return $this->getNumber() == $this->getParent()->count() ? true : false;
  }

  /**
   * Is this first item?
   *
   * @return boolean
   */
  public function isFirst()
  {
    return $this->getNumber() == 1 ? true : false;
  }

  /**
   * Returns path as string
   *
   * @param separator $separatorPath Path separator
   * @param boolean   $withLinks Render items as links or only labels?
   * @param boolean   $includeRoot Include root item?
   * @return string
   */
  public function getPathAsString($separator = ' > ',
          $withLinks = true, $includeRoot = false)
  {
    $children = $this->getPath($withLinks, $includeRoot);

    return implode($separator, $children);
  }

  /**
   * Returns path as array
   *
   * @param boolean $withLinks Render items as links or only labels?
   * @param boolean $includeRoot Include root item?
   * @return array
   */
  public function getPath($withLinks = true, $includeRoot = false)
  {
    $children = array();
    $obj = $this;

    do {
      $children[] = $withLinks && $obj->getRoute() ? $obj->renderLink() : $obj->getLabel();
    } while ($obj = $obj->getParent());

    if (!$includeRoot) {
      // root is last item
      unset($children[count($children)-1]);
    }

    return array_reverse($children);
  }

  /**
   * Returns condition
   *
   * @return string|null
   */
  public function getCondition()
  {
    return $this->getOption('condition');
  }

  /**
   *
   * @param string|sfCallable $condition
   * @return sfMenu
   */
  public function setCondition($condition)
  {
    $this->setOption('condition', $condition);

    return $this;
  }

  /**
   * Checks if condition to render this menu item is met
   *
   * @return boolean
   */
  public function checkCondition()
  {
    $condition = $this->getCondition();

    // no condition is set
    if (!$condition) {
      return true;
    }

    if ($condition instanceof sfCallable) {
      return (boolean) $condition->call($this);
    } elseif (sfToolkit::isCallable($condition)) {
      return (boolean) call_user_func($condition, $this);
    }

    // pass to sfConfig, lower the condition since
    // all keys in sfConfig are lowercased
    return (boolean) sfConfig::get(strtolower($condition));
  }

  /**
   * Call a method recursively on chilren
   *
   * @return sfMenu
   */
  public function callRecursively()
  {
    $args = func_get_args();
    $arguments = $args;
    unset($arguments[0]);

    call_user_func_array(array($this, $args[0]), $arguments);

    foreach ($this->children as $child) {
      call_user_func_array(array($child, 'callRecursively'), $args);
    }

    return $this;
  }

  /**
   * Returns the array representation of the menu
   *
   * @return array
   */
  public function toArray()
  {
    $array = array();
    $array['name'] = $this->getName();

    if ($route = $this->getRoute()) {
      $array['route'] = $route;
    }

    $array['level'] = $this->getLevel();
    $array['is_current'] = $this->isCurrent();
    $array['priority'] = $this->getPriority();
    $array['options'] = $this->getOptions();

    foreach ($this->children as $key => $child) {
      $array['children'][$key] = $child->toArray();
    }

    return $array;
  }

  /**
   * Creates the menu from array
   *
   * @param array $array
   * @return sfMenu
   */
  public function fromArray($array)
  {
    if (isset($array['name'])) {
      $this->setName($array['name']);
    }

    if (isset($array['route'])) {
      $this->setRoute($array['route']);
    }

    if (isset($array['level'])) {
      $this->setLevel($array['level']);
    }

    if (isset($array['is_current'])) {
      $this->isCurrent($array['is_current']);
    }

    if (isset($array['priority'])) {
      $this->setPriority($array['priority']);
    }

    if (isset($array['options'])) {
      $this->setOptions($array['options']);
    }

    if (isset($array['children'])) {
      foreach ($array['children'] as $name => $child) {
        $this->addChild($name)->fromArray($child);
      }
    }

    return $this;
  }

  /**
   * Sorts children items by priority. If two items have the same priority
   * they will be sorted by name.
   *
   * WARNING! This reorders the items, but methods like isLast() does not work!
   * Workaround is needed!
   *
   * @return sfMenu
   * @todo Find a workaround for a bug with invalid $this->isLast() $this->isFirst() calls after reordering. See above notice.
   */
  public function sortByPriority($culture = null)
  {
    if (is_null($culture)) {
      $culture = sfContext::getInstance()->getUser()->getCulture();
    }

    // FIXME: a bit hacky way of sorting
    $this->collator = sfCollator::getInstance($culture);

    uasort($this->children, array($this, '_sortByPriority'));

    $this->collator = null;

    // fluid interface
    return $this;
  }

  /**
   * Sorts all children and their descendants by priority
   *
   * @return sfMenu
   * @see callRecursively()
   */
  public function sortAllByPriority()
  {
    return $this->callRecursively('sortByPriority');
  }

  /**
   * Sort by priority, then by name.
   *
   * For internal usage.
   *
   * @param sfMenu $a
   * @param sfMenu $b
   * @return -1|1|0
   */
  protected function _sortByPriority($a, $b)
  {
    if ($a->getPriority() == $b->getPriority()) {
      return $this->collator->compare($a->getName(), $b->getName());
    } else {
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
    if (method_exists($this, $method)) {
      return call_user_func_array($method, $arguments);
    }

    $verb = substr($method, 0, 3);
    $column = substr($method, 3);

    // first character lowercase
    $column[0] = strtolower($column[0]);
    if ($verb == 'get') {
      return isset($this->$column) ? $this->$column : false;
    } elseif ($verb == 'set') {
      return $this->$column = $arguments[0];
    }

    $class = get_class($this);
    $class = str_replace('sfMenu', '', $class);
    $name = $class ? 'menu.' . sfInflector::tableize($class) : 'menu';
    $name .= '.method_not_found';

    $event = sfCore::getEventDispatcher()->notifyUntil(new sfEvent($name, array(
        'method' => $method, 'arguments' => $arguments, 'menu' => $this
    )));

    if (!$event->isProcessed()) {
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
    return count($this->children);
  }

  /**
   * Returns iteratorAggregate
   *
   * @return ArrayObject Iterator
   */
  public function getIterator()
  {
    return new ArrayObject($this->children);
  }

  public function add($value)
  {
    return $this->addChild($value)->setLabel($value);
  }

  public function current()
  {
    return current($this->children);
  }

  public function next()
  {
    return next($this->children);
  }

  public function key()
  {
    return key($this->children);
  }

  public function valid()
  {
    return $this->current() !== false;
  }

  public function rewind()
  {
    return reset($this->children);
  }

  public function offsetExists($name)
  {
    return isset($this->children[$name]);
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
    unset($this->children[$name]);
  }

}
