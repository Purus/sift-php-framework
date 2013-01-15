<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function include_tabs($templateName, $vars = array())
{
  include_partial($templateName, $vars);
}

/**
 * Builds tabs
 *
 * @param array $items
 * @param string $selected
 * @param array $options
 * @return string string
 */
function build_tabs($items, $selected = null, $options = array())
{
  $html = array();
  if(!is_array($items))
  {
    $items = array($items);
  }
  
  if(!$selected)
  {
    $selected = sfRouting::getInstance()->getCurrentInternalUri(true);
  }

  $html[] = '<ul>';
  foreach($items as $href => $options)
  {
    $a = $li = array();

    if($href == $selected)
    {
      $li['class'] = 'selected';
    }

    $name = $title = $options['name'];
    if(isset($options['title']))
    {
      $title = $options['title'];
    }
    if(isset($options['condition']) && !$options['condition'])
    {
      continue;
    }

    $a['href']  = url_for($href);
    $a['title'] = $title;

    if($href != $selected)
    {
      $html[] = content_tag('li', content_tag('a', $name, $a), $li);
    }
    else
    {
      $html[] = content_tag('li', content_tag('span', $name), $li);
    }
    
  }

  $html[] = '</ul>';
  return join("\n", $html);
}