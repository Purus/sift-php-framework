<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCollectionSorter is a collection sorter
 *
 * @package    Sift
 * @subpackage collection
 */
class sfCollectionSorter  {

  /**
   * Ascending sorting
   *
   */
  const DIRECTION_ASC = 'ASC';

  /**
   * Descending sorting
   *
   */
  const DIRECTION_DESC = 'DESC';

  /**
   * Sorting direction
   *
   * @var string
   */
  protected $direction;

  /**
   * Sorting strategy
   *
   * @var sfICollectionSortStrategy
   */
  protected $strategy;

  /**
   * Constructor
   *
   * @param sfICollectionSorterStrategy $strategy
   */
  public function __construct(sfICollectionSorterStrategy $strategy)
  {
    $this->strategy = $strategy;
    $this->direction = self::DIRECTION_ASC;
  }

  /**
   * Get an instance of a sorter
   *
   * @param string $strategy Strategy (suffix for builting strategies like "callback" of class name for custom)
   * @param array $strategyArguments Array of arguments for the stragegy instance
   * @return sfCollectionSorter
   * @throws InvalidArgumentException
   */
  public static function factory($strategy, $strategyArguments = array())
  {
    switch($strategy)
    {
      // known strategies
      case 'callback':
        $strategyClass = sprintf('sfCollectionSorterStrategy%s', ucfirst($strategy));
      break;

      default:
        $strategyClass = $strategy;
      break;
    }

    if(!class_exists($strategyClass))
    {
      throw new InvalidArgumentException(sprintf('Sorting strategy "%s", class "%s" does not exist.', $strategy, $strategyClass));
    }

    $reflection = new sfReflectionClass($strategyClass);

    if(!$reflection->isInstantiable()
        || !$reflection->isSubclassOf('sfICollectionSorterStrategy'))
    {
      throw new InvalidArgumentException(sprintf('Sorting stragery "%s" is not valid. It is not instantiable or does not implement sfICollectionSorterStrategy interface.', $strategy));
    }

    return new self($reflection->newInstanceArgs((array)$strategyArguments));
  }

  /**
   * Cmparison callback that is used by usort()
   * returns 0 if the items are equal. returns 1 if the strategy
   * determines that $a > $b, or -1 if $a < $b. if the sort direction
   * is descending, a -1 augmenter is applied
   *
   * @param mixed $a
   * @param mixed $b
   * @return int
   */
  public function compareTo($a, $b)
  {
    if($a === $b)
    {
      return 0;
    }

    $augmenter = ($this->direction == self::DIRECTION_DESC) ? -1 : 1;
    return $augmenter * $this->strategy->compareTo($a, $b);
  }

  /**
   * Return the name of the callback back function to be used
   *
   * @return string
   */
  public function getCallback()
  {
    return 'compareTo';
  }

  /**
   * Set the direction to sort
   *
   * @param string $dir
   * @return sfCollectionSorter
   */
  public function setDirection($dir)
  {
    $this->direction = $dir;
    return $this;
  }

  /**
   * Return direction of sorting
   *
   * @return string
   */
  public function getDirection()
  {
    return $this->direction;
  }

}
