<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
/**
 * @see sfSearchTools::highlight()
 */
function search_highlight($text, sfSearchQueryExpression $expression)
{
  return sfSearchTools::highlight($text, $expression);
}

/**
 * @see sfSearchTools::truncateUrl()
 * 
 */
function search_truncate_url($url)
{
  return sfSearchTools::truncateUrl($url);
}

/**
 * @see sfSearchTools::formatRelevancy()
 */
function search_relevancy_percentage($relevancy)
{
  return sfSearchTools::formatRelevancy($relevancy);
}

function search_add_open_search_link()
{
  $config = mySearchTools::getOpenSearchConfig();
  add_auto_discovery_link(search_open_search_route(), 'application/opensearchdescription+xml',
          array('rel' => 'search', 'title' => $config['name']));
}

/**
 * Returns <script> tags for all javascripts associated with the given search source.
 *
 * @return string <script> tags
 */
function get_javascripts_for_search_source(sfISearchSource $source)
{
  $html = '';
  foreach($source->getJavascripts() as $file)
  {
    $html .= javascript_include_tag($file);
  }  
  return $html;
}

function use_javascripts_for_search_source(sfISearchSource $source)
{
  foreach($source->getJavascripts() as $file)
  {
    use_javascript($file);
  }
}

/**
 * Prints <script> tags for all javascripts associated with the given search source.
 *
 * @see get_javascripts_for_form()
 */
function include_javascripts_for_search_source(sfISearchSource $source)
{
  echo get_javascripts_for_search_source($source);
}

/**
 * Returns <link> tags for all stylesheets associated with the given form.
 *
 * @return string <link> tags
 */
function get_stylesheets_for_search_source(sfISearchSource $source)
{
  $html = '';
  foreach($source->getStylesheets() as $file => $media)
  {
    $html .= stylesheet_tag($file, array('media' => $media));
  }
  return $html;
}

/*
 * Returns <link> tags for all stylesheets associated with the given form.
 *
 * @return string <link> tags
 */
function use_stylesheets_for_search_source(sfISearchSource $source)
{
  foreach($source->getStylesheets() as $file => $media)
  {
    use_stylesheet($file, array('media' => $media));
  }
}

/**
 * Prints <link> tags for all stylesheets associated with the given search source.
 *
 * @see get_stylesheets_for_search_source()
 */
function include_stylesheets_for_search_source(sfISearchSource $source)
{
  echo get_stylesheets_for_form($source);
}
