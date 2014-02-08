<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelLogs adds a panel to the web debug toolbar with log messages.
 *
 * @package    Sift
 * @subpackage debug_panel
 */
class sfWebDebugPanelLogs extends sfWebDebugPanel
{
    /**
     * Array of default options
     *
     * @var array
     */
    protected $defaultOptions
        = array(
            // include debug backtrace information?
            'with_debug_backtrace' => false
        );

    /**
     * Array of logs to display
     *
     * @var array
     */
    protected $logs = array();

    /**
     * Array of log types
     *
     * @var array
     */
    protected $types = array();

    /**
     * Array of log level counts
     *
     * @var array
     */
    protected $counts = array();

    /**
     * @see sfWebDebugPanel
     */
    public function getTitle()
    {
        return 'logs';
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getPanelTitle()
    {
        return 'Logs';
    }

    /**
     * Prepares the logs
     *
     */
    public function beforeRender()
    {
        $this->logs = $this->types = array();

        $event = $this->webDebug->getEventDispatcher()->filter(
            new sfEvent('web_debug.filter_logs', array('panel' => $this)),
            $this->webDebug->getLogger()->getLogs()
        );

        $allLogs = $event->getReturnValue();
        $withTrace = $this->getOption('with_debug_backtrace');

        $types = $counts = array();
        foreach ($allLogs as $log) {
            if ($log['level'] < $this->getStatus()) {
                $this->setStatus($log['level']);
            }

            if (!isset($counts[$log['level_name']])) {
                $counts[$log['level_name']] = 0;
            }

            $counts[$log['level_name']]++;

            $this->logs[] = array(
                'level'           => $log['level'],
                'level_name'      => $log['level_name'],
                'time'            => $log['time'],
                'type'            => $log['type'],
                'message'         => $this->formatMessage($log['message'], $log['context']),
                'debug_backtrace' => $withTrace ? $this->getDebugStack($log['debug_backtrace']) : null
            );

            $types[] = $log['type'];
        }

        $this->types = array_unique($types);
        $this->counts = $counts;

        asort($this->types);
    }

    /**
     * @see sfWebDebugPanel
     */
    public function getPanelContent()
    {
        return $this->webDebug->render(
            $this->getOption('template_dir') . '/panel/logs.php',
            array(
                'logs'   => $this->logs,
                'types'  => $this->types,
                'counts' => $this->counts
            )
        );
    }

    /**
     * Formats context values into the message placeholders.
     *
     * @param string $message The message
     * @param array  $context Array of context
     *
     * @return string
     */
    protected function formatMessage($message, array $context = array())
    {
        static $constants;

        if (!$constants) {
            foreach (array('sf_app_dir', 'sf_root_dir', 'sf_sift_lib_dir') as $constant) {
                $constants[realpath(sfConfig::get($constant)) . DIRECTORY_SEPARATOR] = $constant . DIRECTORY_SEPARATOR;
            }
        }

        // escape HTML
        $message = htmlspecialchars($message, ENT_QUOTES, sfConfig::get('sf_charset'));

        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = sprintf('<span class="keyword">%s</span>', $val);
        }

        // interpolate replacement values into the message and return
        $message = strtr((string)$message, $replace);

        // replace constants value with constant name
        $message = str_replace(array_keys($constants), array_values($constants), $message);

        // remove username/password from DSN
        if (strpos($message, 'DSN') !== false) {
            $message = preg_replace("/=&gt;\s+'?[^'\s,]+'?/", "=&gt; '****'", $message);
        }

        return $message;
    }
}
