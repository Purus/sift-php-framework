<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Breadcrumbs helper
 *
 * @package Sift
 * @subpackage helper
 */

/**
 * Builds breadcrumb navigation
 *
 * @param array|string $options Options
 * @return string
 */
function breadcrumbs($options = array())
{
  $options = _parse_attributes($options);
  $force = _get_option($options, 'force', false);

  // don't display breadrumbs on homepage
  if (sfRouting::getInstance()->getCurrentRouteName() == 'homepage' && !$force) {
    return '';
  }

  $separator = _get_option($options, 'separator', '/');

  if (!isset($options['class'])) {
    $options['class'] = 'breadcrumbs';
  }

  $title = '';
  if (!isset($options['no_info'])) {
    $options['no_info'] = false;
  }
  if (!$options['no_info'] && !isset($options['info'])) {
    $title = 'You are here:';
  } elseif (!$options['no_info']) {
    $title = $options['info'];
  }

  unset($options['no_info']);

  // load crumbs
  $breadcrumbs = myBreadcrumbs::getInstance();
  if (isset($options['home'])) {
    $breadcrumbs->setHome($options['home']);
    unset($options['home']);
  }

  $crumbs = $breadcrumbs->getCrumbs();

  $_crumbs = array();
  for ($i = 0, $count = count($crumbs), $last = $count - 1; $i < $count; $i++) {
    if (!isset($crumbs[$i]['options']['title'])) {
      $crumbs[$i]['options']['title'] = trim($crumbs[$i]['name']);
    }

    if ($i == $last) {
      $_crumbs[] = content_tag('span', trim($crumbs[$i]['name']), $crumbs[$i]['options']);
    } else {
      if ($i == 0) {
        $crumbs[$i]['options']['class'] = isset($crumbs[$i]['options']['class']) ?
            array_merge($crumbs[$i]['options']['class'], array('first')) : 'first';
      }

      $_crumbs[] = link_to_if($crumbs[$i]['url'],
          '<span>' . ($crumbs[$i]['name']) . '</span>', $crumbs[$i]['url'], $crumbs[$i]['options']);
    }
  }

  if (sfConfig::get('sf_i18n')) {
    if (!$catalogue = _get_option($options, 'catalogue')) {
      $catalogue = sfConfig::get('sf_sift_data_dir') . DIRECTORY_SEPARATOR .
                    'i18n' . DIRECTORY_SEPARATOR . 'catalogues' .
                    DIRECTORY_SEPARATOR . 'breadcrumbs';
    }

    if (!empty($title)) {
      $title = __($title, array(), $catalogue);
    }
  }

  $tag = _get_option($options, 'tag');

  switch (strtolower($tag)) {
    // bootstrap compatibility
    case 'ul':

      $content = array();
      for ($i = 0, $count = count($_crumbs), $last = $count - 1; $i < $count; $i++) {
        if ($i < $last) {
          $li = $content[] = content_tag('li', $_crumbs[$i] . content_tag('span', '/', array('class' => 'divider')));
        } else {
          $li = $content[] = content_tag('li', $_crumbs[$i], array('class' => 'active'));
        }
      }

      $content = content_tag('ul', ($title ? content_tag('li', $title) : '')
        . ' ' .  join("\n", $content), array('class' => 'breadcrumb'));

    break;

    case 'p':
    default:
      $content = content_tag('p', $title . ' ' . join(' ' . $separator . ' ', $_crumbs));
    break;
  }

  $html = content_tag('div', $content, $options);

  return $html;
}

/**
 * Drops breadcrumb (used from view)
 *
 * @param string $title
 * @param string $url
 * @param array $options
 */
function drop_breadcrumb($title, $url, $options = array())
{
  myBreadcrumbs::getInstance()->dropCrumb($title, $url, $options);
}

/**
 * Clear breadcrumbs (used from view)
 *
 */
function clear_breadcrumbs()
{
  myBreadcrumbs::getInstance()->clearCrumbs();
}
