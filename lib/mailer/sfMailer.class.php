<?php
/*
 * This file is part of the Sift PHP framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// load swift mailer
require_once dirname(__FILE__).'/../vendor/swift/swift_required.php';
require_once dirname(__FILE__).'/../vendor/swift/swift_init.php';

/**
 * sfMailer class provides a wrapper around SwiftMailer
 *
 * @package    Sift
 * @subpackage mailer
 */
class sfMailer extends Swift_Mailer implements sfIConfigurable
{
  /**
   * Event dispatcher
   *
   * @var sfEventDispatcher
   */
  protected $dispatcher;

  /**
   * Realtime transport
   *
   * @var Swift_Transport
   */
  protected $realtimeTransport;

  /**
   * Mail spool
   *
   * @var sfMailerSpool|Swift_Spool
   */
  protected $spool = null;

  /**
   * Logger instance
   */
  protected $logger = null;

  /**
   * Force flag to send without using queue
   *
   * @var boolean
   */
  protected $force = false;

  /**
   * Array of options
   *
   * @var array
   */
  protected $options = array();

  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    // deliver the mail?
    'deliver' => true,
    // mail charset
    'charset' => 'utf-8',
    // maximum line length
    'line_length' => 80,
    // mail encoding
    // available types: base64, 8-bit, 7-bit, quoted-printable
    'encoding' => '8-bit',
    // logging enabled
    'log' => array(
      'enabled' => true
    ),
    // antiflood feature
    'anti_flood' => array(
      'enabled' => true,
      // limit 100 emails
      'limit' => 100,
      // sleep 10 seconds after sending 100 emails
      'sleep' => 10,
    ),
    // Throttler feature
    'cache' => array(
      'enabled' => false,
      'param' => array(
        'dir' => false
      )
    ),
    // which transport to use?
    'transport_type' => 'failover',
    // transports
    'transports' => array(
      'default' => array(
        'type' => 'mail'
      )
    ),
    // plugins
    'plugins' => array(
      'sfMailerNotificationPlugin' => array(),
      'sfMailerHtml2TextPlugin' => array()
    ),
    'spool' => array(
      'enabled' => false,
      'class' => 'Swift_FileSpool',
      'arguments' => array()
    )
  );

  /**
   * Constructs the mailer
   *
   * @param sfEventDispatcher $dispatcher The event dispatcher
   * @param array $options Array of options
   * @throws InvalidArgumentException
   * @inject event_dispatcher
   */
  public function __construct(sfEventDispatcher $dispatcher, $options = array())
  {
    $this->dispatcher = $dispatcher;
    $this->options = array_merge($this->defaultOptions, count($options) ? $options : $this->loadOptions());

    $this->dispatcher->notify(new sfEvent('mailer.pre_configure', array(
        'mailer' => $this, 'options' => $this->options
    )));

    // setup the mailer
    $this->setup();

    $this->dispatcher->notify(new sfEvent('mailer.configure', array(
      'mailer' => $this, 'options' => $this->options
    )));
  }

  /**
   * Setup the mailer object
   *
   * @throws InvalidArgumentException
   */
  protected function setup()
  {
    $this->setupPreferences();
    $this->setupTransportAndSpool();
    $this->setupAntiFlood();
    $this->setupPlugins();
    $this->setupLogging();
    $this->setupDelivery();
  }

  /**
   * Setup preferences
   *
   */
  protected function setupPreferences()
  {
    // preferences for all messages!
    Swift_Preferences::getInstance()->setCharset($this->getOption('charset'));
  }

  /**
   * Setups the delivery. When delivery is disabled, registers the blackhole plugin,
   * so the messages are sucked to the blackhole instead of sending them
   *
   * @link https://github.com/swiftmailer/swiftmailer/commit/d4e5e63f077d74080919521f786138a3b27d556e#lib/classes/Swift/Plugins
   */
  protected function setupDelivery()
  {
    if ($this->getOption('deliver')) {
      return;
    }
    $this->getTransport()->registerPlugin(new sfMailerBlackholePlugin($this->dispatcher));
  }

  /**
   * Setup transports from configuration
   *
   * @return array Array of transport instances
   */
  protected function setupTransports()
  {
    $transports = array();
    $configured = $this->getOption('transports', array());

    foreach ($configured as $transportName => $transportOptions) {
      if (isset($transportOptions['class'])) {
        $class = $transportOptions['class'];
        if (!class_exists($class)) {
          throw new sfConfigurationException(sprintf('Invalid mailer transport class "%s" given for "%s" transport.', $class, $transportName));
        }
        $transport = new $class();
        if (isset($transportOptions['param'])) {
          foreach ($transportOptions['param'] as $key => $value) {
            $method = 'set'.ucfirst($key);
            if (method_exists($transport, $method)) {
              $transport->$method($value);
            } elseif (method_exists($transport, 'getExtensionHandlers')) {
              foreach ($transport->getExtensionHandlers() as $handler) {
                if(in_array(strtolower($method), array_map('strtolower',
                  (array) $handler->exposeMixinMethods())))
                {
                  $transport->$method($value);
                }
              }
            }
          }
        }
      }
      // the type is specified
      elseif (isset($transportOptions['type'])) {
        switch (strtolower($transportOptions['type'])) {
          case 'sendmail':
            $transport = Swift_SendmailTransport::newInstance();
            if (isset($transportOptions['command'])) {
              $transport->setCommand($transportOptions['command']);
            }
          break;

          case 'mail':
            $transport = Swift_MailTransport::newInstance();
            if (isset($transportOptions['params'])) {
              $transport->setExtraParams($transportOptions['params']);
            }
          break;

          case 'smtp':
            $transport = Swift_SmtpTransport::newInstance();

            if (isset($transportOptions['host'])) {
              $transport->setHost($transportOptions['host']);
            } elseif (isset($transportOptions['hostname'])) {
              $transport->setHost($transportOptions['hostname']);
            }

            if (isset($transportOptions['port'])) {
              $transport->setPort($transportOptions['port']);
            }

            if (isset($transportOptions['username'])) {
              $transport->setUsername($transportOptions['username']);
            }

            if (isset($transportOptions['password'])) {
              $transport->setPassword($transportOptions['password']);
            }

            if (isset($transportOptions['encryption'])) {
              $transport->setEncryption($transportOptions['encryption']);
            }

          break;

          case 'null':
            $transport = Swift_NullTransport::newInstance();
          break;

          default:
            throw new LogicException(sprintf('Cannot setup mail transport type "%s".', $transport['type']));
          break;
        }
      }

      $transports[$transportName] = $transport;
    }

    return $transports;
  }

  /**
   * Setups the transport(s) to use when sending emails
   *
   * @throws InvalidArgumentException
   */
  protected function setupTransportAndSpool()
  {
    $type = $this->getOption('transport_type');

    $transports = $this->setupTransports();

    switch (strtolower($type)) {
      case 'failover':
        $transport = new Swift_Transport_FailoverTransport();
        $transport->setTransports($transports);
      break;

      case 'load_balanced':
        $transport = new Swift_Transport_LoadBalancedTransport();
        $transport->setTransports($transports);
      break;

      // this is a transport name
      default:

        if (!isset($transports[$type])) {
          throw new sfConfigurationException(sprintf('The configuration specifies "%s" to use as transport, but the transport is not configured.', $type));
        }
        $transport = $transports[$type];
      break;
    }

    $this->realtimeTransport = $transport;

    // spool enabled
    if ($this->getOption('spool.enabled')) {
      $class = $this->getOption('spool.class');

      if (!$class) {
        throw new sfConfigurationException('For the spool mail delivery strategy, you must also define a spool class option.');
      }

      if (!class_exists($class)) {
        throw new sfConfigurationException(sprintf('The mailer spool class "%s" does not exist.', $class));
      }

      $arguments = $this->getOption('spool.arguments', array());

      if ($arguments) {
        $r = new sfReflectionClass($class);
        // FIXME: resolve arguments?
        $this->spool = $r->newInstanceArgs($arguments);
      } else {
        $this->spool = new $class();
      }

      $transport = new Swift_SpoolTransport($this->spool);
    }

    parent::__construct($transport);
  }

  /**
   * Setups antiflood feature
   *
   */
  protected function setupAntiFlood()
  {
    $antiflood = $this->getOption('anti_flood');
    if ($antiflood['enabled']) {
      $limit = $antiflood['limit'] > 0 ? $antiflood['limit'] : 100;
      $sleep = $antiflood['sleep'] > 0 ? $antiflood['sleep'] : 10;
      $this->realtimeTransport->registerPlugin(new Swift_Plugins_AntiFloodPlugin($limit, $sleep));
    }
  }

  /**
   * Setups logging
   */
  protected function setupLogging()
  {
    if ($this->getOption('log.enabled')) {
      $this->logger = new sfMailerLoggerPlugin($this->getEventDispatcher());
      $this->realtimeTransport->registerPlugin($this->logger);
    }
  }

  /**
   * Setup plugins
   *
   * @throws sfConfigurationException
   */
  protected function setupPlugins()
  {
    // register mailer plugins
    $plugins = $this->getOption('plugins');

    foreach ($plugins as $pluginName => $pluginOptions) {
      $class = $pluginName;
      if (isset($pluginOptions['class'])) {
        $class = $pluginOptions['class'];
        unset($pluginOptions['class']);
      }

      if (!class_exists($class)) {
        throw new sfConfigurationException(sprintf('Mailer plugin class "%s" does not exist.', $class));
      }

      $reflection = new sfReflectionClass($class);

      $arguments = array();
      if (isset($pluginOptions['arguments'])) {
        $arguments = $pluginOptions['arguments'];
      }

      if (isset($arguments[0]) && $arguments[0] == 'event_dispatcher') {
        $arguments[0] = $this->getEventDispatcher();
      }

      if ($reflection->isSubclassOf('sfMailerPlugin')) {
        if ($arguments) {
          $plugin = $reflection->newInstanceArgs($arguments);
        } else {
          $plugin = new $class($this->getEventDispatcher(), (array) $pluginOptions);
        }
      } else {
        $plugin = $reflection->newInstanceArgs($arguments);
      }

      $this->realtimeTransport->registerPlugin($plugin);

      if ($this->getTransport() instanceof Swift_SpoolTransport) {
        $this->getTransport()->registerPlugin($plugin);
      }
    }
  }

  /**
   * Get an option value by name
   *
   * If the option is empty or not set a NULL value will be returned.
   *
   * @param string $name
   * @param mixed $default Default value if confiuration of $name is not present
   * @return mixed
   */
  public function getOption($name, $default = null)
  {
    if (isset($this->options[$name])) {
      return $this->options[$name];
    }
    // no dot found
    if (strpos($name, '.') === false) {
      return $default;
    }
    // allow for groups and multi-dimensional arrays
    return sfArray::get($this->options, $name, $default);
  }

  /**
   * Set an option
   * Returns Configurable for chaining
   *
   * @param string $name
   * @param mixed $value
   * @return sfMailer
   */
  public function setOption($name, $value)
  {
    // not dot syntax
    if (strpos($name, '.') === false) {
      $this->options[$name] = $value;
    } else {
      sfArray::set($this->options, $name, sfToolkit::getValue($value));
    }

    return $this;
  }

  /**
   * Checks is the object has option with given $name
   *
   * @param string $name
   * @return boolean
   */
  public function hasOption($name)
  {
    if (strpos($name, '.') === false) {
      return isset($this->options[$name]);
    }

    return sfArray::keyExists($this->options, $name);
  }

  /**
   * Get all options
   *
   * @return array
   */
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Load options from config/mail.yml config file
   *
   * @return array
   */
  protected function loadOptions()
  {
    // load yaml configuration
    return include sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_config_dir_name').'/mail.yml');
  }

  /**
   * Returns new myMailerMessage
   *
   * @param string $subject The email subject
   * @param string $body The email body
   * @param string $contentType The content type
   * @param string $charset The charser
   * @return myMailerMessage
   */
  public function getNewMessage($subject = null, $body = null, $contentType = null, $charset = null)
  {
    return new sfMailerMessage($this->dispatcher, $subject, $body, $contentType, $charset ? $charset : $this->getOption('charset'),
        $this->getOption('encoding'), $this->getOption('line_length'));
  }

  /**
   * Sets template variables which will be replaced in the message body
   *
   * The $replacements can either be an associative array, or an implementation
   * of {@link Swift_Plugins_Decorator_Replacements}.
   *
   * @param array $replacements array of replacements
   */
  public function setTemplateVars($replacements)
  {
    $plugin = new Swift_Plugins_DecoratorPlugin($replacements);
    $this->mailer->registerPlugin($plugin);
  }

  /**
   * Sends the given message.
   *
   * @param Swift_Transport $transport A transport instance
   * @param array &$failedRecipients An array of failures by-reference
   *
   * @return integer|false The number of sent emails
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    if ($this->force) {
      $this->force = false;

      if (!$this->realtimeTransport->isStarted()) {
        $this->realtimeTransport->start();
      }

      return $this->realtimeTransport->send($message, $failedRecipients);
    }

    return parent::send($message, $failedRecipients);
  }

  /**
   * Forces the next call to send() to use the realtime strategy.
   *
   * @return sfMailer The current sfMailer instance
   */
  public function sendNextImmediately()
  {
    $this->force = true;

    return $this;
  }

  /**
   * Sends the email (in batch)
   *
   * @param Swift_Mime_Message $message The message to send
   * @param array $failedRecipients Array of failed recipients
   * @param Swift_Mailer_RecipientIterator $it Iterator to get addresses from
   * @return integer (number of emails sent)
   */
  public function batchSend(Swift_Mime_Message $message, &$failedRecipients = null, Swift_Mailer_RecipientIterator $it = null)
  {
    $failedRecipients = (array) $failedRecipients;

    $sent = 0;
    $to = $message->getTo();
    $cc = $message->getCc();
    $bcc = $message->getBcc();

    if (!empty($cc)) {
      $message->setCc(array());
    }
    if (!empty($bcc)) {
      $message->setBcc(array());
    }

    // Use an iterator if set
    if (isset($it)) {
      while ($it->hasNext()) {
        $message->setTo($it->nextRecipient());
        $sent += $this->send($message, $failedRecipients);
      }
    } else {
      foreach ($to as $address => $name) {
        if (is_int($address)) {
          $message->setTo($name);
        } else {
          $message->setTo(array($address => $name));
        }

        $sent += $this->send($message, $failedRecipients);
      }
    }

    $message->setTo($to);
    if (!empty($cc)) {
      $message->setCc($cc);
    }

    if (!empty($bcc)) {
      $message->setBcc($bcc);
    }

    return $sent;
  }

  /**
   * Sends the current messages in the spool.
   *
   * The return value is the number of recipients who were accepted for delivery.
   *
   * @param array &$failedRecipients An array of failures by-reference
   * @return integer The number of sent emails
   */
  public function flushQueue(&$failedRecipients = null)
  {
    return $this->getSpool()->flushQueue($this->realtimeTransport, $failedRecipients);
  }

  /**
   * Returns spool instance
   *
   * @throws LogicException if spool is disabled in configuration
   * @return Swift_Spool|sfMailerSpool
   */
  public function getSpool()
  {
    if (!$this->spool) {
      throw new LogicException('You can send messages to the spool only if spool is enabled in your configuration.');
    }

    return $this->spool;
  }

  /**
   * Gets the realtime transport instance.
   *
   * @return Swift_Transport The realtime transport instance.
   */
  public function getRealtimeTransport()
  {
    return $this->realtimeTransport;
  }

  /**
   * Sets the realtime transport instance.
   *
   * @param Swift_Transport $transport The realtime transport instance.
   * @return sfMailer
   */
  public function setRealtimeTransport(Swift_Transport $transport)
  {
    $this->realtimeTransport = $transport;

    return $this;
  }

  /**
   * Gets the logger instance.
   *
   * @return Swift_Plugins_MessageLogger The logger instance.
   */
  public function getLogger()
  {
    return $this->logger;
  }

  /**
   * Returns the dispatcher
   *
   * @return sfEventDispatcher
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  /**
   * Sets the logger instance.
   *
   * @param Swift_Plugins_MessageLogger $logger The logger instance.
   * @return sfMailer
   */
  public function setLogger(Swift_Events_SendListener $logger)
  {
    $this->logger = $logger;

    return $this;
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   * @return mixed The returned value of the called method
   * @throws sfException If called method is undefined
   */
  public function __call($method, $arguments)
  {
    $event = $this->dispatcher->notifyUntil(
                new sfEvent('mailer.method_not_found', array(
                        'method' => $method,
                        'arguments' => $arguments,
                        'mailer' => $this
              )));
    if (!$event->isProcessed()) {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }

}
