<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Documentation Web Debug Panel
 *
 * @package     Sift
 * @subpackage  debug
 */
class sfWebDebugPanelDocumentation extends sfWebDebugPanel
{
    /**
     * Array of documentation links
     *
     * @var array
     */
    protected $links
        = array(
            'Sift Wiki on Bitbucket' => 'https://bitbucket.org/mishal/sift-php-framework/wiki/Home'
        );

    /**
     * @see sfWebDebugPanel
     */
    public function getTitle()
    {
        return 'docs';
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getPanelTitle()
    {
        return 'Documentation';
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getPanelContent()
    {
        return $this->webDebug->render(
            $this->getOption('template_dir') . '/panel/documentation.php',
            array(
                'links' => $this->getLinks()
            )
        );
    }

    /**
     * Returns links to documentation
     *
     * @return array
     */
    protected function getLinks()
    {
        return $this->webDebug->getEventDispatcher()->filter(
            new sfEvent('web_debug.filter_documentation_links'),
            $this->links
        )->getReturnValue();
    }

}
