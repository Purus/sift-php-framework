<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * sfBreadcrumbs provides easy interface for building breadcrumb navigation.
 *
 * @package Sift
 * @subpackage breadcrumbs
 */
class sfBreadcrumbs {

  /**
   * Response namespace
   *
   */
  const RESPONSE_NAMESPACE = 'breadcrumbs';

  /**
   * @var sfBreadcrumbs
   */
  protected static $instance = null;

  /**
   * Response holde
   *
   * @var sfResponse
   */
  protected $response;

  /**
   * Home crumb holder
   *
   * @var array
   */
  protected $home;

  /**
   * Retrieve the singleton instance of this class.
   *
   * @return sfBreadcrumbs A sfBreadcrumbs implementation instance.
   */
  public static function getInstance()
  {
    if(!isset(self::$instance))
    {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Constructs the class
   *
   * @param string $home
   * @param string $homeUrl Homepage url. Default to '@homepage'
   * @param array $homeOptions Array of home crumb options
   */
  public function __construct($home = null, $homeUrl = '@homepage', $homeOptions = array())
  {
    $this->response = sfContext::getInstance()->getResponse();

    if(!$home)
    {
      $home = sfConfig::get('sf_i18n') ?
              __('Home', array(), sfConfig::get('sf_sift_data_dir')
                                    .'/i18n/catalogues/breadcrumbs')
              : 'Home';
    }

    $this->setHome($home, $homeUrl, $homeOptions);
  }

  /**
   * Sets home crumb (the first crumb)
   *
   * @param string $name Name of the crumb
   * @param string $url Url or route for the crumb
   * @param array $options Array of options for the crumb. See BreadcrumbsHelper for usage.
   * @return sfBreadcrumbs
   * @see BreadcrumbsHelper
   */
  public function setHome($name, $url = '@homepage', $options = array())
  {
    $this->home = array(
      'name' => $name,
      'url'  => $url,
      'options' => $options
    );
    return $this;
  }

  /**
   * Clears home crumb
   *
   * @return sfBreadcrumbs
   */
  public function clearHome()
  {
    $this->home = array();
    return $this;
  }

  /**
   * Returns home crumb
   *
   * @return array
   */
  public function getHomeCrumb()
  {
    return $this->home;
  }

  /**
   * Clears all breadcrumbs
   *
   * @return sfBreadcrumbs
   */
  public function clear()
  {
    $this->response->setParameter('breadcrumbs', array(), self::RESPONSE_NAMESPACE);
    return $this;
  }

  /**
   * Alias for clear()
   *
   * @return sfBreadcrumbs
   * @see clear()
   */
  public function clearCrumbs()
  {
    return $this->clear();
  }

  /**
   * Drops the crumb
   *
   * @param string $name Crumb title
   * @param string $url Url of the crumb
   * @param array $options Array of the options
   */
  public function drop($name, $url = null, $options = array())
  {
    $crumbs = $this->response->getParameter('breadcrumbs', array(), self::RESPONSE_NAMESPACE);

    array_push($crumbs, array(
        'name' => $name,
        'url'  => $url,
        'options' => $options
    ));

    $this->response->setParameter('breadcrumbs', $crumbs, self::RESPONSE_NAMESPACE);
    return $this;
  }

  /**
   *
   * @see drop()
   */
  public function dropCrumb($name, $url = null, $options = array())
  {
    return $this->drop($name, $url, $options);
  }

  /**
   * Retrieve crumbs
   *
   * @param boolean $includeHome
   * @return array
   */
  public function getCrumbs($includeHome = true)
  {
    $crumbs = $this->response->getParameter('breadcrumbs', array(),
            self::RESPONSE_NAMESPACE);

    sfCore::getEventDispatcher()->notifyUntil(
            new sfEvent('breadcrumbs.pre_get_crumbs', array(
                array(
                  'breadcumbs' => &$this,
                  'home_included' => $includeHome
                ))
            )
      );

    if($includeHome && is_array($this->home))
    {
      array_unshift($crumbs, $this->home);
    }

    // pass by event system
    $dispatcher = sfCore::getEventDispatcher();
    $event = $dispatcher->filter(new sfEvent('breadcrumbs.get_crumbs',
            array(
              'breadcrumbs' => &$this,
              'home_included' => $includeHome
            )), $crumbs);

    return $event->getReturnValue();
  }

}
