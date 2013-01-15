<?php

class TestSpool implements Swift_Spool
{
  protected
    $messages = array();

  public function __construct()
  {
  }

  public function isStarted()
  {
    return true;
  }

  public function start()
  {
  }

  public function stop()
  {
  }

  public function queueMessage(Swift_Mime_Message $message)
  {
    $this->messages[] = $message;

    return 0;
  }

  public function flushQueue(Swift_Transport $transport, &$failedRecipients = null)
  {
    foreach ($this->messages as $message)
    {
      $transport->send($message);
    }

    $this->messages = array();
  }

  public function getMessages()
  {
    return $this->messages;
  }

  public function getQueuedCount()
  {
    return count($this->messages);
  }

  public function reset()
  {
    $this->messages = array();
  }
}
