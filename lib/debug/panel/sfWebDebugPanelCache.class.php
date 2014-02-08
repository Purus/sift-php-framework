<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelCache adds a panel to the web debug toolbar with a link to ignore the cache
 * on the next request.
 *
 * @package    Sift
 * @subpackage debug_panel
 */
class sfWebDebugPanelCache extends sfWebDebugPanel
{
    /**
     * Array of data about the cached content
     *
     * @var array
     */
    protected $cached = array();

    /**
     * Is cache ignored?
     *
     * @var boolean
     */
    protected $cacheIgnored = false;

    /**
     * @var string
     */
    protected $ignoreUrl;

    /**
     * @var string
     */
    protected $enableUrl;

    /**
     * Constructor
     *
     * @param sfWebDebug $webDebug
     * @param array      $options
     */
    public function __construct(sfWebDebug $webDebug, $options = array())
    {
        parent::__construct($webDebug, $options);

        $this->webDebug->getEventDispatcher()->connect(
            'view.cache.filter_content',
            array($this, 'listenToViewCacheFilterContentEvent'),
            -98
        );

        parse_str(parse_url(@$_SERVER['REQUEST_URI'], PHP_URL_QUERY), $query);

        // we are ignoring the cache
        if (isset($query['_sf_ignore_cache'])
            && $query['_sf_ignore_cache']
        ) {
            $this->cacheIgnored = true;
        } else {
            $this->cacheIgnored = false;
        }

        $query['_sf_ignore_cache'] = 1;
        $this->ignoreUrl = '?' . http_build_query($query, '', '&');
        unset($query['_sf_ignore_cache']);
        $this->enableUrl = '?' . http_build_query($query, '', '&');
    }

    /**
     * Listens to the view.cache.filter_content and saves information about
     * the cache fragment
     *
     * @param sfEvent $event   The event
     * @param string  $content The content to be filtered
     *
     * @return string $content The content
     */
    public function listenToViewCacheFilterContentEvent(sfEvent $event, $content)
    {
        $cache = $event['view_cache_manager'];

        if (!$content) {
            return '';
        }

        $this->cached[] = array(
            'content_type'  => $event['response']->getContentType(),
            'with_layout'   => isset($event['with_layout']),
            'content'       => $content,
            'uri'           => $event['uri'],
            'id'            => dechex(crc32($event['uri'])),
            'new'           => $event['new'],
            'lifetime'      => $cache->getLifeTime($event['uri'], true),
            'last_modified' => $cache->getLastModified($event['uri'], true, time())
        );

        return $content;
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getTitle()
    {
        return 'cache';
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getTitleUrl()
    {
        return $this->cacheIgnored ? $this->enableUrl : $this->ignoreUrl;
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getPanelTitle()
    {
        return 'Cache information';
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getPanelContent()
    {
        return $this->webDebug->render(
            $this->getOption('template_dir') . '/panel/cache.php',
            array(
                'ignore_url'    => $this->ignoreUrl,
                'enable_url'    => $this->enableUrl,
                'cache_ignored' => $this->cacheIgnored,
                'cached'        => $this->cached
            )
        );
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getPanelJavascript()
    {
        return file_get_contents($this->getOption('template_dir') . '/panel/cache.min.js');
    }

}
