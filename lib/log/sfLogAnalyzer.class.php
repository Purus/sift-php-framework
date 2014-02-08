<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Analyzes the log file
 *
 * @package    Sift
 * @subpackage log
 */
class sfLogAnalyzer extends sfConfigurable implements Countable {

  /**
   * Array of default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'line_format' => '%time% %type% [%level%] %message% [%extra%]%EOL%',
    'time_format' => 'M d H:i:s',
    // The database to store the logs
    // Every :memory: database is distinct from every other. So, opening two
    // database connections each with the filename ":memory:" will
    // create two independent in-memory databases.
    'database' => ':memory:'
  );

  /**
   * Database handler
   *
   * @var PDO
   */
  protected $dbh = null;

  /**
   * Processed flag
   *
   * @var boolean
   */
  protected $processed = false;

  /**
   * Skipped entries
   *
   * @var array
   */
  protected $skipped = array();

  /**
   * Constructor
   *
   * @param string $file The absolute path to the file which will be analyzed
   * @param array $options Array of options for the analyzer
   * @throws InvalidArgumentException If the file is not readable or does not exist
   */
  public function __construct($file, $options = array())
  {
    if(!is_readable($file))
    {
      throw new InvalidArgumentException(sprintf('The file "%s" is not readable or does not exist', $file));
    }

    $this->file = $file;
    parent::__construct($options);
  }

  /**
   * Setups the analyzer
   *
   * @throws sfConfigurationException
   */
  public function setup()
  {
    if(!extension_loaded('pdo_SQLite'))
    {
      throw new sfConfigurationException('sfLogAnalyzer class needs "pdo_sqlite" extension to be loaded.');
    }
  }

  /**
   * Analyzes the file
   *
   * @return sfLogAnalyzer
   */
  public function process()
  {
    if($this->processed)
    {
      return;
    }
    $this->setDatabase($this->getOption('database'));
    $this->doProcess();
    $this->processed = true;

    return $this;
  }

  /**
   * Has the file been processed?
   *
   * @return boolean
   */
  public function isProcessed()
  {
    return $this->processed;
  }

  /**
   * Returns the regular expresion (part) for log level names
   *
   * @return string
   */
  protected function getLevelsRegex()
  {
    return join('|', array_values($this->getLogLevelMap()));
  }

  /**
   * Does the hard work
   *
   */
  protected function doProcess()
  {
    $regexGenerator = sfDateFormatRegexGenerator::getInstance();
    $timeRegex = $regexGenerator->generateRegex($this->getOption('time_format'));

    // prepare the regex
    $pattern = sprintf('/%s/', strtr(preg_quote($this->getOption('line_format'), '/'), array(
      '%time%' => '(?<time>' . trim($timeRegex, '/') . ')',
      '%type%' => '(?<type>[a-zA-Z0-9]+)', // Sift by default
      '%level%' => sprintf('(?<level>%s+)', $this->getLevelsRegex()),
      '%message%' => '(?<message>.*)',
      '%extra%' => '(?<extra>.*)',
      '%EOL%' => '(?<eol>[\r\n|\r|\n])'
    )));

    // clean up the database
    $this->dbh->query('DELETE FROM log_analyzed');

    // optimize for speed
    $this->dbh->query('PRAGMA synchronous = 0');
    $this->dbh->query('PRAGMA journal_mode=MEMORY');
    $this->dbh->query('PRAGMA default_cache_size=10000');
    $this->dbh->query('PRAGMA locking_mode=EXCLUSIVE');

    // open the file
    $handle = fopen($this->file, 'r');
    // while it's not at the end...
    $i = 0;
    while(!feof($handle))
    {
      $i++;

      // read the line
      $line = fgets($handle);

      if(trim($line) == '')
      {
        continue;
      }

      if(!preg_match($pattern, $line, $matches))
      {
        $this->skipped[] = array(
          'line' => $i,
          'content' => $line
        );
        continue;
      }

      if($pregError = preg_last_error())
      {
        $this->skipped[] = array(
          'line' => $i,
          'content' => $line
        );
        continue;
      }

      // insert to database
      $stmt = $this->dbh->prepare('INSERT INTO log_analyzed (id, type, level, message, extra, created_at) VALUES (?, ?, ?, ?, ?, ?)');
      $stmt->execute(array(
        $i, $matches['type'], $matches['level'], trim($matches['message']), $matches['extra'] ? $matches['extra'] : null, date('Y-m-d H:i:s', strtotime($matches['time']))
      ));
    }

    fclose($handle);
  }

  /**
   * Returns unix timestamp
   *
   * @param sfDate|DateTime $datetime
   * @return integer
   */
  protected function getTimestamp($datetime)
  {
    if($datetime instanceof DateTime)
    {
      $datetime = $datetime->format('U');
    }
    elseif($datetime instanceof sfDate)
    {
      $datetime = $datetime->getTS();
    }

    return date('Y-m-d H:i:s', $datetime);
  }

  /**
   * Get logs with given level. Can be more levels at once
   *
   * @param string|array $level The log level
   * @param integer|sfDate|DateTime $start The start timestamp
   * @param integer|sfDate|DateTime $end The end timestamp
   */
  public function getLogs($level, $start = null, $end = null)
  {
    $this->process();

    if(!is_array($level))
    {
      $level = array($level);
    }

    $where = array();
    $where[] = join(' OR ', array_fill(0, count($level), 'level = ?'));
    $params = array_merge(array(), $level);

    if($start)
    {
      $where[] = 'created_at >= ?';
      $params[] = $this->getTimestamp($start);
    }

    if($end)
    {
      $where[] = 'created_at <= ?';
      $params[] = $this->getTimestamp($end);
    }

    return $this->queryLogs($where, $params);
  }

  /**
   * Returns the start date of logs
   *
   * @return integer The unix timestamp
   */
  public function getStart()
  {
    $this->process();
    $stmt = $this->dbh->query('SELECT MIN(created_at) FROM log_analyzed LIMIT 1');
    $result = $stmt->fetchColumn();

    return strtotime($result);
  }

  /**
   * Returns the end date of logs
   *
   * @return integer The unix timestamp
   */
  public function getEnd()
  {
    $this->process();
    $stmt = $this->dbh->query('SELECT MAX(created_at) FROM log_analyzed LIMIT 1');
    $result = $stmt->fetchColumn();

    return strtotime($result);
  }

  /**
   * Returns an array of levels which are present in the log file.
   * Result is ordered by the count of logs for given level.
   *
   * The array looks like:
   * <pre>
   * $levels = array(0 => array(
   *   'count' => 12,
   *   'level' => 0,
   *   'level_name' => 'emergency'
   * ));
   * </pre>
   *
   * The key of the array is the level constant.
   *
   * @return array
   */
  public function getLevels()
  {
    $this->process();
    $stmt = $this->dbh->query('SELECT COUNT(id) AS count, level AS level FROM log_analyzed GROUP BY level ORDER BY count DESC');
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $levelMap = $this->getLogLevelMap();
    $levels = array();
    foreach($result as $level)
    {
      $key = array_search($level['level'], $levelMap);
      $levels[$key] = array(
        'count' => $level['count'],
        'level' => $key,
        'level_name' => $level['level']
      );
    }

    return $levels;
  }

  /**
   * Returns an array of types which are present in the log file
   *
   * @return array
   */
  public function getTypes()
  {
    $this->process();
    $stmt = $this->dbh->query('SELECT COUNT(id) AS count, type AS type FROM log_analyzed GROUP BY type ORDER BY count DESC');

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Returns log level map
   *
   * @return array
   */
  protected function getLogLevelMap()
  {
    return sfFileLogger::getLogLevelMap();
  }

  /**
   * Query the database
   *
   * @param array $where
   * @param array $params
   * @return array
   */
  protected function queryLogs($where = array(), $params = array())
  {
    $query = sprintf('SELECT * FROM log_analyzed%sORDER BY created_at DESC',
                count($where) ? ' WHERE ' . (join(' AND ', $where)) . ' ' : ' ');

    $stmt = $this->dbh->prepare($query);
    $stmt->execute($params);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($result as &$item)
    {
      $item['message'] = new sfLogAnalyzerMessage($item['message'], $item['extra']);
    }

    return $result;
  }

  /**
   * Returns the database handle
   *
   * @return PDO
   */
  public function getDatabaseHandle()
  {
    if(!$this->dbh)
    {
      $this->setDatabase($this->getOption('database'));
    }

    return $this->dbh;
  }

  /**
   * Return all skipped lines
   *
   * @return array
   */
  public function getSkipped()
  {
    $this->process();

    return $this->skipped;
  }

  /**
   * Sets the database name. Creates the database if needed.
   *
   * @param string $database The database name where to store the cache
   * @throws sfDatabaseException If the connection could not be extablished
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

    if(!$this->dbh = new PDO(sprintf('sqlite:%s', $this->getOption('database'))))
    {
      throw new sfDatabaseException(sprintf('Unable to connect to SQLite database: %s.'));
    }

    if($new)
    {
      $this->createSchema();
    }
  }

  /**
   * Creates the database schema.
   *
   * @throws sfDatabaseException
   */
  protected function createSchema()
  {
    $statements = array(
      'CREATE TABLE [log_analyzed] (
        [id] INTEGER PRIMARY KEY,
        [type] VARCHAR(64),
        [level] VARCHAR(64),
        [message] LONGVARCHAR,
        [extra] LONGVARCHAR,
        [created_at] TIMESTAMP
      )',
      'CREATE INDEX [type_idx] ON [log_analyzed] ([type])',
      'CREATE INDEX [level_idx] ON [log_analyzed] ([level])',
      'CREATE INDEX [created_at_idx] ON [log_analyzed] ([created_at])'
    );

    foreach($statements as $statement)
    {
      if(!$this->dbh->query($statement))
      {
        throw new sfDatabaseException($this->dbh->lastError());
      }
    }
  }

  /**
   * Returns number of all logs (except the skipped)
   *
   * @return integer
   */
  public function count()
  {
    $this->process();
    $stmt = $this->dbh->prepare('SELECT COUNT(id) FROM log_analyzed');
    $stmt->execute();

    return (integer)$stmt->fetchColumn(0);
  }

  /**
   * Magic call method allows to provide magical methods like:
   *
   * getErrorLogs() -> retrieve errors
   * getNoticeLogs() -> retrieve notices
   * getCriticalLogs() -> retrieve criticals
   * getEmergencyLogs() -> retrieve emergency logs
   *
   * @param string $method
   * @param array $arguments Array of arguments
   * @throws BadMethodCallException If the method is not valid
   */
  public function __call($method, $arguments)
  {
    if(preg_match(sprintf('/^get(%s+)Logs$/i', $this->getLevelsRegex()), $method, $matches))
    {
      $level = strtolower($matches[1]);
      array_unshift($arguments, $level);

      return call_user_func_array(array($this, 'getLogs'), $arguments);
    }

    throw new BadMethodCallException(sprintf('Unknown method "%s"', $method));
  }

  /**
   * Close connection to database
   */
  public function __destruct()
  {
    $this->dbh = null;
  }

}
