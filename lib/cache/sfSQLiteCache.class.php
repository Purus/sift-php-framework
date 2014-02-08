<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that stores cached content in a SQLite database.
 *
 * Available options:
 *
 * * database: File where to put the cache database (or :memory: to store cache in memory)
 * * see sfCache for options available for all drivers
 *
 * @package    Sift
 * @subpackage cache
 */
class sfSQLiteCache extends sfCache
{
  /**
   * Database handler
   *
   * @var PDO
   */
  protected $dbh = null;

  /**
   * Required options
   *
   * @var array
   */
  protected $requiredOptions = array(
    'database'
  );

  /**
   * Valid options for the cache
   *
   * @var array
   */
  protected $validOptions = array(
    'database'
  );

  /**
   *
   * @see sfCache
   */
  public function setup()
  {
    if(!extension_loaded('pdo_SQLite'))
    {
      throw new sfConfigurationException('sfSQLiteCache class needs "pdo_sqlite" extension to be loaded.');
    }

    $this->setDatabase($this->getOption('database'));
  }

  /**
   * @see sfCache
   */
  public function getCacheBackend()
  {
    return $this->dbh;
  }

  /**
   * @see sfCache
   */
  public function get($key, $default = null)
  {
    $stmt = $this->dbh->prepare('SELECT data FROM cache WHERE key = ? AND timeout > ?');
    $stmt->execute(array(
      $key, time()
    ));
    $data = $stmt->fetchColumn();

    return $data !== false ? $data : $default;
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    $stmt = $this->dbh->prepare('SELECT COUNT(key) FROM cache WHERE key = ? AND timeout > ?');
    $stmt->execute(array(
      $key, time()
    ));

    return $stmt->fetchColumn() > 0;
  }

  /**
   * @see sfCache
   */
  public function set($key, $data, $lifetime = null)
  {
    if($this->getOption('automatic_cleaning_factor') > 0 &&
        rand(1, $this->getOption('automatic_cleaning_factor')) == 1)
    {
      $this->clean(self::MODE_OLD);
    }

    $stmt = $this->dbh->prepare('INSERT OR REPLACE INTO cache (key, data, timeout, last_modified) VALUES (?, ?, ?, ?)');
    $stmt->execute(array(
      $key, $data, time() + $this->getLifetime($lifetime), time()
    ));

    return (boolean)$stmt->rowCount();
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    $stmt = $this->dbh->prepare('DELETE FROM cache WHERE key = ?');
    $stmt->execute(array(
      $key
    ));

    return (boolean)$stmt->rowCount();
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    $stmt = $this->dbh->prepare('DELETE FROM cache WHERE REGEXP(?, key)');
    $stmt->execute(array(
      $this->patternToRegexp($pattern)
    ));

    return (boolean)$stmt->rowCount();
  }

  /**
   * @see sfCache
   */
  public function clean($mode = self::MODE_ALL)
  {
    switch($mode)
    {
      case self::MODE_ALL:
        $stmt = $this->dbh->query('DELETE FROM cache');

        return $stmt->rowCount();
      break;

      case self::MODE_OLD:
        $stmt = $this->dbh->prepare('DELETE FROM cache WHERE timeout < ?');
        $stmt->execute(array(time()));

        return $stmt->rowCount();
      break;
    }
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    $stmt = $this->dbh->prepare('SELECT timeout FROM cache WHERE key = ? AND timeout > ?');
    $stmt->execute(array(
      $key, time()
    ));
    $result = $stmt->fetch(PDO::FETCH_COLUMN);

    return ($result !== false) ? intval($result) : 0;
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    $stmt = $this->dbh->prepare('SELECT last_modified FROM cache WHERE key = ? AND timeout > ?');
    $stmt->execute(array(
        $key, time()
    ));
    $result = $stmt->fetch(PDO::FETCH_COLUMN);

    return ($result !== false) ? intval($result) : 0;
  }

  /**
   * Sets the database name.
   *
   * @param string $database The database name where to store the cache
   */
  protected function setDatabase($database)
  {
    $new = false;
    if(':memory:' == $database)
    {
      $new = true;
    }
    else if(!is_file($database))
    {
      $new = true;
      // create cache dir if needed
      $dir = dirname($database);
      $current_umask = umask(0000);
      if (!is_dir($dir))
      {
        @mkdir($dir, 0777, true);
      }
      touch($database);
      umask($current_umask);
    }

    if(!$this->dbh = new sfPDO(sprintf('sqlite:%s', $this->getOption('database'))))
    {
      throw new sfCacheException(sprintf('Unable to connect to SQLite database: %s.'));
    }

    $this->dbh->sqliteCreateFunction('regexp', array($this, 'removePatternRegexpCallback'), 2);

    if($new)
    {
      $this->createSchema();
    }
  }

  /**
   * Callback used when deleting keys from cache.
   */
  public function removePatternRegexpCallback($regexp, $key)
  {
    return preg_match($regexp, $key);
  }

  /**
   * @see sfCache
   */
  public function getMany($keys)
  {
    $stmt = $this->dbh->prepare(sprintf('SELECT key, data FROM cache WHERE key IN(%s) AND timeout > ?',
        join(',', array_fill(0, count($keys), '?'))
    ));

    $params = $keys;
    array_push($params, time());

    $stmt->execute($params);

    $data = array();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      $data[$row['key']] = $row['data'];
    }

    return $data;
  }

  /**
   * Creates the database schema.
   *
   * @throws sfCacheException
   */
  protected function createSchema()
  {
    $statements = array(
      'CREATE TABLE [cache] (
        [key] VARCHAR(255),
        [data] LONGVARCHAR,
        [timeout] TIMESTAMP,
        [last_modified] TIMESTAMP
      )',
      'CREATE UNIQUE INDEX [cache_unique] ON [cache] ([key])',
    );

    foreach($statements as $statement)
    {
      if(!$this->dbh->query($statement))
      {
        throw new sfCacheException($this->dbh->lastError());
      }
    }
  }

  /**
   * Close connection to database
   */
  public function __destruct()
  {
    $this->dbh = null;
  }

}
