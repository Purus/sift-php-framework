<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Returns HTML code for rating stars.
 * 
 * @param float $avg_rating
 * @param array $options
 * @return string
 * @throws InvalidArgumentException 
 */
function rating_stars($avg_rating, $options = array())
{
  $options = _parse_attributes($options);

  if(!isset($options['size']))
  {
    $options['size'] = 'small';
  }

  if(!in_array($options['size'], array('small', 'big')))
  {
    throw new InvalidArgumentException(sprintf('{RatingHelper} rating_stars() "size" - "%s" option in not valid. Valid values are: "small, big"', $options['size']));
  }

  if(!isset($options['max']))
  {
    $options['max'] = 5;
  }
  
  if($options['max'] < 1 || $options['max'] > 10)
  {
    throw new InvalidArgumentException('{RatingHelper} rating_stars() max parameter is invalid. It should be an integer between 1 and 10');
  }

  $percent  = floor($avg_rating / $options['max'] * 100);
  $width    = $percent > 0 ? $percent : 0;
  
  $class    = sprintf('%s-stars', $options['size']);
  $html     = array();
  $html[]   = sprintf('<span class="star-rating-state%s" title="%s/%s">', $class ? (' ' . $class) : '', round($avg_rating, 1), $options['max']);
  $html[]   = sprintf('<span class="star-rating-current" style="width:%s%%"></span></span>', $width);
  return join('', $html);
}

/**
 * Returns rating stars with rating enabled for rating
 * 
 * @param $rating
 * @param array $options
 * @return string
 */
function rating_stars_votable($rating, $options = array())
{
  $options = _parse_attributes($options);
  $options = array_merge(array('max' => 5), $options);

  if(!$route = _get_option($options, 'route'))
  {
    throw new sfConfigurationException('{RatingHelper} rating_stars_votable() parameter "route" is missing.');
  }

  if($options['max'] < 1 || $options['max'] > 10)
  {
    throw new InvalidArgumentException('{RatingHelper} rating_stars() max parameter is invalid. It should be an integer between 1 and 10');
  }
  
  if(!isset($options['size']))
  {
    $options['size'] = 'small';
  }

  if(!in_array($options['size'], array('small', 'big')))
  {
    throw new sfConfigurationException(sprintf('{RatingHelper} rating_stars() "size" - "%s" option in not valid. Valid values are: "small, big"', $options['size']));
  }

  $percent  = floor($rating / $options['max'] * 100);
  $width    = $percent > 0 ? $percent : 0;

  if(!isset($options['id']))
  {
    $options['id'] = 'star-rating-';
  }

  $class  = sprintf('%s-stars', $options['size']);
  $max    = $options['max'];
  $html   = array();
  $html[] = sprintf('<ul class="star-rating%s">', $class ? (' ' . $class) : '');
  $html[] = sprintf('<li class="current-rating" style="width:%s%%">%s</li>', $width, rating_ratio($rating, $max));

  for($i = 1; $i <= $max; $i++)
  {
    $html[] = sprintf('<li><a id="%s%s" href="%s" title="Hodnotit %s" class="%s" rel="nofollow">%s</a></li>', $options['id'], $i, url_for(str_replace('%%VOTE%%', $i, $route)), $i, rating_class($i), $i);
  }

  $html[] = '</ul>';
  return join("\n", $html);  
}

/**
 * Returns rating ratio for given average rating
 * @param float $avg_rating
 * @param integer $max
 * @return string
 */
function rating_ratio($avg_rating, $max = 5)
{
  if($max > 10)
  {
    throw new sfException("{RatingHelper} Rating supports only max 10 stars!");
  }  
  return sprintf('%s/%s', round($avg_rating, 1), $max);
}

/**
 * Returns css class for given rating value
 * 
 * @staticvar array $nwords
 * @param  $i
 * @return string
 */
function rating_class($i)
{
  static $nwords = array('one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten');
  if($i > 10)
  {
    throw new sfException("{RatingHelper} Rating supports only max 10 stars!");
  }
  $suffix = $i > 1 ? 'stars' : 'star';
  return sprintf('star-rating-%s-%s', $nwords[$i-1], $suffix);
}
