<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Html validate debug panel
 *
 * @package     Sift
 * @subpackage  debug
 */
class sfWebDebugPanelHtmlValidate extends sfWebDebugPanel
{
    /**
     * The response object
     *
     * @var sfWebResponse
     */
    protected $response;

    /**
     * Has the response been validated?
     *
     * @var boolean
     */
    protected $toBeValidated = false;

    /**
     * Array of validation errors
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     *
     * @param sfWebDebug $webDebug
     */
    public function __construct(sfWebDebug $webDebug, $options = array())
    {
        parent::__construct($webDebug, $options);

        $this->webDebug->getEventDispatcher()->connect(
            'response.pre_send',
            array(
                $this,
                'listenToResponsePreSendEvent'
            ),
            -98
        ); // this must be connected with priority greater than the sfWebDebugLogger
    }

    /**
     * Listens to response.pre_send event
     *
     * @param sfEvent $event
     */
    public function listenToResponsePreSendEvent(sfEvent $event)
    {
        $this->response = $event['response'];

        // we have a response we can validate
        if (strpos($this->response->getContentType(), 'text/html') !== false
            && is_string($this->response->getContent())
        ) {
            $this->toBeValidated = true;
        }
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getTitle()
    {
        return $this->toBeValidated ? '<span class="web-debug-loader" title="Loading..."></span>' : 'n/a';
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getPanelTitle()
    {
        return 'HTML validator';
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getPanelContent()
    {
        if (!$this->toBeValidated) {
            return;
        }

        return $this->webDebug->render(
            $this->getOption('template_dir')
            . '/panel/html_validate.php',
            array(
                'content' => $this->response->getContent(),
                'content_highlighted' =>
                    sfSyntaxHighlighter::factory('html', $this->response->getContent())->getHtml(true),
                // with line numbers
                'content_type' => $this->response->getContentType()
            )
        );

    }

}
