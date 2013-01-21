<?php
/*
 * This file is part of the Sift PHP framework
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// load swift mailer
require_once sfConfig::get('sf_sift_lib_dir').'/vendor/swift/swift_required.php';

// modify dependecies
Swift_DependencyContainer::getInstance()
  ->register('message.message')
  ->asNewInstanceOf('myMailerMessage')
  ->register('message.mimepart')
  ->asNewInstanceOf('Swift_MimePart'); 

/**
 * sfMailer class provides a wrapper around SwiftMailer
 *
 * @package    Sift
 * @subpackage mailer
 */
class sfMailer extends Swift_Mailer {

  protected static $instance;
  
  protected
    $config            = array(),          
    $spool             = null,
    $logger            = null,
    $strategy          = 'realtime',
    $address           = '',
    $realtimeTransport = null,
    $force             = false,
    $redirectingPlugin = null;
  
  protected
    $addToQueue = false;
  
  public static function getInstance()
  {
    if(!self::$instance)
    {
      self::$instance = new myMailer();
    }
    return self::$instance;
  }

  public function reset()
  {
    self::$instance = null;
    return self::getInstance();
  }

  public function __construct($config = null)
  {
    is_null($config) ? $this->loadConfig() : $this->config = $config;
    
    $transport  = $this->getConfig('transport_type', 'default');
    $transports = $this->getConfig('transports', array());

    // we have transport type specified as one from transports
    if(count($transports) && array_key_exists($transport, $transports))
    {
      $t = $transports[$transport];
      switch($t['type'])
      {
        case 'mail':
          $transport = Swift_MailTransport::newInstance();
        break;

        case 'smtp':
          
          $transport = Swift_SmtpTransport::newInstance($t['hostname'], $t['port']);

          if(isset($t['username']))
          {
            $transport->setUsername($t['username']);
          }
          if(isset($t['password']))
          {
            $transport->setPassword($t['password']);
          }
          if(isset($t['encryption']))
          {
            $transport->setEncryption($t['encryption']);
          }

        break;
      }
    }
    elseif($transportOptions = $this->getConfig('transport'))
    {
      if(is_array($transportOptions))
      {
        // transport
        $class = $transportOptions['class'];
        if(!class_exists($class))
        {
          throw new InvalidArgumentException(sprintf('Invalid transport class "%s" given.', $class));
        }
        $transport = new $class();
        if(isset($transportOptions['param']))
        {
          foreach($transportOptions['param'] as $key => $value)
          {
            $method = 'set'.ucfirst($key);
            if(method_exists($transport, $method))
            {
              $transport->$method($value);
            }
            elseif(method_exists($transport, 'getExtensionHandlers'))
            {
              foreach($transport->getExtensionHandlers() as $handler)
              {
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
      elseif(is_string($transportOptions))
      {
        $class = $transportOptions;
        if(!class_exists($class))
        {
          throw new InvalidArgumentException(sprintf('Invalid transport class "%s" given.', $class));
        }
      }
    }
    else // default transport
    {
      $transport = Swift_MailTransport::newInstance();
    }
    
    $this->realtimeTransport = $transport;
    
    $spool = $this->getConfig('spool');
    
    // spool enabled
    if(isset($spool['enabled']) && $spool['enabled'])
    {     
      if(!isset($spool['class']))
      {
        throw new InvalidArgumentException('For the spool mail delivery strategy, you must also define a spool_class option');
      }
      
      $arguments = isset($spool['arguments']) ? $spool['arguments'] : array();
      
      if($arguments)
      {
        $r = new ReflectionClass($spool['class']);
        $this->spool = $r->newInstanceArgs($arguments);
      }
      else
      {
        $this->spool = new $spool['class'];
      }
      
      $transport = new Swift_SpoolTransport($this->spool);
    }
    
    parent::__construct($transport);

    $antiflood = $this->getConfig('anti_flood');
    if($antiflood['enabled'])
    {
      $limit = $antiflood['limit'] > 0 ? $antiflood['limit'] : 100;
      $sleep = $antiflood['sleep'] > 0 ? $antiflood['sleep'] : 10;
      $this->realtimeTransport->registerPlugin(new Swift_Plugins_AntiFloodPlugin($limit, $sleep));
    }

    $log = $this->getConfig('log');
    if($log['enabled'])
    {      
      $this->logger = new sfMailerLogger();
      $this->realtimeTransport->registerPlugin($this->logger);      
    }

    // preferences for all messages!
    $charset = $this->getConfig('charset', sfConfig::get('sf_charset'));    
    Swift_Preferences::getInstance()->setCharset($charset);
    
    // register mailer plugins
    $plugins = (array)$this->getConfig('plugins');    
    foreach($plugins as $p)
    {  
      $plugin = new $p();    
      $this->realtimeTransport->registerPlugin($plugin);
      if($this->getTransport() instanceof Swift_SpoolTransport)
      {
        $this->getTransport()->registerPlugin($plugin);
      }
    }
    
    // FIXME: this has been removed from Swift mailer
    // https://github.com/swiftmailer/swiftmailer/commit/d4e5e63f077d74080919521f786138a3b27d556e#lib/classes/Swift/Plugins
    if(!$this->getConfig('deliver'))
    {
      $this->getTransport()->registerPlugin(new sfMailerBlackholePlugin());
    }
    
    sfCore::getEventDispatcher()->notify(new sfEvent('mailer.configure', array(
        'mailer' => &$this, 'config' => $this->config)));    
  }

  /**
   * Load configuration from config/mail.yml config file
   
   * @return void
   */
  protected function loadConfig()
  {
    // load yaml configuration
    $config           = sfConfigCache::getInstance()->checkConfig('config/mail.yml');
    // load config from yaml
    $this->config     = include($config);
  }
  
  /**
   * Returns new myMailerMessage
   * 
   * @param string $subject
   * @param string $body
   * @param string $contentType
   * @param string $charset
   * @return myMailerMessage 
   */
  public function getNewMessage($subject = null, $body = null, $contentType = null, $charset = null)
  {
    return new myMailerMessage($subject, $body, $contentType, $charset);
  }
  
  /**
   * Returns configuration option
   * 
   * @param string $name
   * @param mixed $default
   * @return mixed 
   */
  public function getConfig($name, $default = null)
  {
    return isset($this->config[$name]) ? $this->config[$name] : $default;
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
   * @param Swift_Transport $transport         A transport instance
   * @param string[]        &$failedRecipients An array of failures by-reference
   *
   * @return int|false The number of sent emails
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    // deliver is disabled!
    if(!$this->getConfig('deliver'))
    {
      // return true;
    }
    
    if(!$this->addToQueue)
    {
      $this->sendNextImmediately();
    }
    
    if($this->force)
    {
      $this->force = false;

      if (!$this->realtimeTransport->isStarted())
      {
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
   * Sends the given message with spooling
   * 
   * @param $message
   * @param $failedRecipients
   */
  public function sendQueue(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $this->addToQueue = true;
    return $this->send($message, $failedRecipients);
  }
  
  /**
   * Sends the email (in batch)
   *
   * @return integer (number of emails sent)
   */
  public function batchSend(Swift_Mime_Message $message,
    &$failedRecipients = null,
    Swift_Mailer_RecipientIterator $it = null)
  {
    // deliver is disabled!
    if(!$this->getConfig('deliver'))
    {
      return true;
    }
   
    $failedRecipients = (array) $failedRecipients;
    
    $sent = 0;
    $to = $message->getTo();
    $cc = $message->getCc();
    $bcc = $message->getBcc();
    if(!empty($cc))
    {
      $message->setCc(array());
    }
    if(!empty($bcc))
    {
      $message->setBcc(array());
    }
    
    // Use an iterator if set
    if(isset($it))
    {
      while($it->hasNext())
      {
        $message->setTo($it->nextRecipient());
        $sent += $this->send($message, $failedRecipients);
      }
    }
    else
    {
      foreach($to as $address => $name)
      {
        if(is_int($address)) 
        {
          $message->setTo($name);
        } 
        else 
        {
          $message->setTo(array($address => $name));
        }
        
        $sent += $this->send($message, $failedRecipients);
      }
    }

    $message->setTo($to);
    if(!empty($cc))
    {
      $message->setCc($cc);
    }
    
    if(!empty($bcc))
    {
      $message->setBcc($bcc);
    }
    
    return $sent;    
  }
  
  /**
   * Like batchSend, but put the messages into the spool queue
   * 
   * @param Swift_Mime_Message $message
   * @param array &$failedRecipients, optional
   * @param Swift_Mailer_RecipientIterator $it, optional
   * @return int
   * @see send()
   */
  public function batchSendQueue(
    Swift_Mime_Message $message,
    &$failedRecipients = null,
    Swift_Mailer_RecipientIterator $it = null
  )
  {
    $this->addToQueue = true;
    
    return $this->batchSend($message, $failedRecipients, $it);
  }  

  /**
   * Sends the current messages in the spool.
   *
   * The return value is the number of recipients who were accepted for delivery.
   *
   * @param string[] &$failedRecipients An array of failures by-reference
   *
   * @return int The number of sent emails
   */
  public function flushQueue(&$failedRecipients = null)
  {
    return $this->getSpool()->flushQueue($this->realtimeTransport, $failedRecipients);
  }

  /**
   * Returns spool instance 
   *
   * @throws LogicException if spool disabled in configuration
   */
  public function getSpool()
  {
    if(!$this->spool)
    {
      throw new LogicException('You can only send messages in the spool is spool is enabled in your configuration');
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
   */
  public function setRealtimeTransport(Swift_Transport $transport)
  {
    $this->realtimeTransport = $transport;
  }
  
  /**
   * Gets the logger instance.
   *
   * @return sfMailerLoggerPlugin The logger instance.
   */
  public function getLogger()
  {
    return $this->logger;
  }

  /**
   * Sets the logger instance.
   *
   * @param sfMailerLoggerPlugin $logger The logger instance.
   */
  public function setLogger($logger)
  {
    $this->logger = $logger;
  }
  
  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string $method The method name
   * @param array  $arguments The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @throws sfException If called method is undefined
   */
  public function __call($method, $arguments)
  {
    $event = sfCore::getEventDispatcher()->notifyUntil(
                    new sfEvent('mailer.method_not_found', array(
                        'method'      => $method,
                        'arguments'   => $arguments,
                        'mailer'      => &$this)));

    if(!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }  
  
}
