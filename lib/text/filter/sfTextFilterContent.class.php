<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTextFilterContent represents a content to which are text filters applied.
 * Each filter can cancel the bubbling of next registered text filters.
 *
 * @package    Sift
 * @subpackage text_filter
 */
class sfTextFilterContent {

  /**
   * Bubble flag
   *
   * @var boolean
   */
  protected $bubble = true;

  /**
   * Constructor
   *
   * @param string $text The text content
   */
  public function __construct($text)
  {
    $this->setText($text);
  }

  /**
   * Sets the text
   *
   * @param string|object $text The text content or object with __toString() method
   * @return sfTextFilterContent
   */
  public function setText($text)
  {
    $this->text = (string)$text;
    return $this;
  }

  /**
   * Returns the text
   *
   * @return string
   */
  public function getText()
  {
    return $this->text;
  }

  /**
   * Cancel bubbling
   *
   * @param boolean $flag Cancel bubble?
   * @return boolean|sfTextFilterContent Boolean when acting like getter (without the argument), self when acting like setter
   */
  public function cancelBubble($flag = null)
  {
    if(is_null($flag))
    {
      return !$this->bubble;
    }
    $this->bubble = !(boolean)$flag;
    return $this;
  }

  /**
   * __toString() magic method
   */
  public function __toString()
  {
    return $this->text;
  }

}
