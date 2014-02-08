<?php
/*
 * This file is part of the Sift PHP framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfException is the base class for all Sift related exceptions and
 * provides an additional method for printing up a detailed view of an
 * exception.
 *
 * @package    Sift
 * @subpackage exception
 */
class sfException extends Exception
{
    /**
     * Wrapped exception
     *
     * @var Exception
     */
    protected $wrappedException;

    /**
     * Last exception
     *
     * @var Exception
     */
    protected static $lastException = null;

    /**
     * Constructor
     *
     * @param string    $message  The exception message
     * @param int       $code     The exception code
     * @param Exception $previous The previous exception
     */
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        if (PHP_VERSION_ID < 50300) {
            $this->previous = $previous;
            parent::__construct($message, $code);
        } else {
            parent::__construct($message, $code, $previous);
        }
    }

    /**
     * Wraps an Exception.
     *
     * @param Exception $e An Exception instance
     *
     * @return sfException An sfException instance that wraps the given Exception object
     */
    public static function createFromException(Exception $e)
    {
        $exception = new sfException(sprintf('Wrapped %s: %s', get_class($e), $e->getMessage()));
        $exception->setWrappedException($e);
        self::$lastException = $e;

        return $exception;
    }

    /**
     * Sets the wrapped exception.
     *
     * @param Exception $e An Exception instance
     */
    public function setWrappedException(Exception $e)
    {
        $this->wrappedException = $e;
        self::$lastException = $e;
    }

    /**
     * Gets the last wrapped exception.
     *
     * @return Exception An Exception instance
     */
    public static function getLastException()
    {
        return self::$lastException;
    }

    /**
     * Clears the $lastException property
     *
     * @return void
     */
    public static function clearLastException()
    {
        self::$lastException = null;
    }

    /**
     * Running in cli?
     *
     * @return boolean
     */
    protected static function isInCli()
    {
        return 'cli' == PHP_SAPI;
    }

    /**
     * Prints the stack trace
     *
     * @param Exception $exception
     *
     * @return void
     */
    public function printStackTrace(Exception $exception = null)
    {
        if (!$exception) {
            if (null === $this->wrappedException) {
                $this->setWrappedException($this);
            }
            $exception = $this->wrappedException;
        }

        $dispatcher = $context = $request = null;

        // do we have context created?
        if (class_exists('sfContext', false)
            && sfContext::hasInstance()
        ) {
            $context = sfContext::getInstance();
            $dispatcher = $context->getEventDispatcher();
            $request = $context->getRequest();
        }

        // we have a dispatcher
        if ($dispatcher) {
            $event = $dispatcher->notifyUntil(
                new sfEvent('application.throw_exception', array('exception' => $exception))
            );
            if ($event->isProcessed()) {
                return;
            }
        }

        $catchedOutput = 'N/A';
        if (!sfConfig::get('sf_test') && !self::isInCli()) {
            // catchedOutput
            $catchedOutput = ob_get_contents();
            if (!$catchedOutput) {
                $catchedOutput = 'N/A';
            }
            // clean current output buffer
            while (@ob_end_clean()) {
                ;
            }
            ob_start();
        }

        // create the backtrace for logging
        $backtrace = new sfDebugBacktrace($exception->getTrace(), array(
            'file_excerpt' => false,
        ));

        if (sfConfig::get('sf_logging_enabled')
            && !($this instanceof sfStopException)
        ) {
            $logContext = array(
                'name'    => get_class($exception),
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode()
            );

            $uri = $method = 'n/a';

            // log the current uri
            if ($request) {
                $uri = $request->getUri();
                $method = $request->getMethod();
            }

            $decorator = new sfDebugBacktraceLogDecorator($backtrace);

            // extra contextual, will be logged
            $logContext[sfILogger::CONTEXT_EXTRA]['url'] = $uri;
            $logContext[sfILogger::CONTEXT_EXTRA]['method'] = $method;
            $logContext[sfILogger::CONTEXT_EXTRA]['debug_backtrace'] = $backtrace;

            sfLogger::getInstance()->critical(sprintf("{%s} {message}", get_class($exception)), $logContext);
        }

        $headers = true;
        if (self::isInCli()) {
            $format = 'plain';
            $headers = false;
        } else {
            $format = 'html';
            if ($request
                && ((method_exists($request, 'isAjax') && $request->isAjax())
                    || (method_exists(
                            $request,
                            'isXmlHttpRequest'
                        )
                        && $request->isXmlHttpRequest()))
            ) {
                $format = 'json';
            }
        }

        // send an error 500 if not in debug mode
        if (!sfConfig::get('sf_debug')) {
            if (ini_get('log_errors')
                && sfToolkit::isCallable('error_log')
            ) {
                // FIXME: include backtrace?
                // we need more accurate log here!
                error_log($exception->getMessage());
            }

            // we need to display the error
            $this->displayErrorPage($format);

            return;
        }

        if ($context && (is_object($response = $context->getResponse()))) {
            if ($response->getStatusCode() < 300) {
                // status code has already been sent, but is included here for the purpose of testing
                $response->setStatusCode(500);
            }
        }

        // backtrace with file excerpt
        $backtrace = new sfDebugBacktrace($exception->getTrace(), array(
            'file_excerpt' => true,
        ));

        // send the 500 header
        $headers ? @header('HTTP/1.0 500 Internal Server Error') : null;

        $charset = class_exists('sfConfig', false) ? sfConfig::get('sf_charset') : 'UTF-8';
        switch ($format) {
            case 'html':
                $headers ? @header(sprintf('Content-Type: text/html;charset=%s', sfConfig::get('sf_charset'))) : null;
                $decorator = new sfDebugBacktraceHtmlDecorator($backtrace, array(
                    'template_dir' => sfConfig::get('sf_sift_data_dir') . '/web_debug/backtrace'
                ));
                break;

            case 'json':

                if ($headers) {
                    @header(sprintf('Content-Type: application/json;charset=%s', $charset));
                    @header('X-Content-Type-Options: nosniff');
                }
                $decorator = new sfDebugBacktraceLogDecorator($backtrace);

                break;

            case 'plain':
                $decorator = new sfDebugBacktraceLogDecorator($backtrace);
                break;
        }

        $result = sfLimitedScope::render(
            sprintf('%s/web_debug/exception/%s.php', sfConfig::get('sf_sift_data_dir'), $format),
            array(
                'debug_backtrace' => $decorator->toString(),
                'output'          => $catchedOutput,
                'name'            => get_class($exception),
                'charset'         => $charset,
                'message'         => null !== $exception->getMessage() ? $exception->getMessage() : 'n/a'
            )
        );

        $notify = true;
        if ($exception instanceof sfDatabaseException
            && $exception->getCode() === sfDatabaseException::SESSION_ERROR
        ) {
            $notify = false;
        }

        if ($dispatcher && $notify) {
            $result = $dispatcher->filter(
                new sfEvent('application.render_exception', array(
                    'exception' => $exception,
                    'content'   => $result,
                    'format'    => $format
                )),
                $result
            )->getReturnValue();
        }

        echo $result;

        // if test, do not exit
        if (!sfConfig::get('sf_test')) {
            exit(1);
        }
    }

    /**
     * Displays production error page for the exception in given format
     *
     * @param Exception $exception
     * @param string    $format
     */
    protected function displayErrorPage($format = 'html')
    {
        return sfCore::displayErrorPage('error500', $format);
    }

}
