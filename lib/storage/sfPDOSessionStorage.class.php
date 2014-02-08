<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides support for session storage using a PDO database abstraction layer.
 *
 * # database     - [default]   - The database connection to use (see databases.yml).
 * # db_table     - [session]   - The database table in which session data will be stored.
 * # db_id_col    - [sess_id]   - The database column in which the session id will be stored.
 * # db_data_col  - [sess_data] - The database column in which the session data will be stored.
 * # db_time_col  - [sess_time] - The database column in which the session timestamp will be stored.
 *
 * @package    Sift
 * @subpackage storage
 */
class sfPDOSessionStorage extends sfSessionStorage
{
  /**
   * Default options
   *
   * @var array
   */
  protected $defaultOptions = array(
    'db_id_col'   => 'id',
    'db_data_col' => 'blob_data',
    'db_time_col' => 'expire',
    'database'   => 'default',
    'db_table' => 'session',
    'use_transaction' => false
  );

  /**
   * PDO connection
   *
   * @var sfPdo
   */
  protected $db;

  /**
   * Database manager instance
   *
   * @var sfDatabaseManager
   */
  protected $manager;

  /**
   *
   * @var boolean
   */
  protected $transactionStarted = false;

  /**
   * Constructs the storage
   *
   * @param sfDatabaseManager $manager The database manager
   * @param array $options Array of options
   */
  public function __construct(sfDatabaseManager $manager, $options = array())
  {
    $this->manager = $manager;
    parent::__construct($options);
  }

  /**
   * Initializes this Storage instance.
   *
   * @return boolean true, if initialization completes successfully, otherwise false
   * @throws sfDatabaseException If an error occurs
   */
  public function setup()
  {
    // use this object as the session handler
    session_set_save_handler(
        array($this, 'sessionOpen'),
        array($this, 'sessionClose'),
        array($this, 'sessionRead'),
        array($this, 'sessionWrite'),
        array($this, 'sessionDestroy'),
        array($this, 'sessionGC'));

    parent::setup();
  }

  /**
   * Closes a session.
   *
   * @return boolean true, if the session was closed, otherwise false
   */
  public function sessionClose()
  {
    // manually call garbage collector
    // 10% chance
    if (rand(1, 100) < 10) {
      try {
        // manually call garbage collection
        $this->sessionGC(ini_get('session.gc_maxlifetime'));
      } catch (sfDatabaseException $e) {
      }
    }
    // do nothing
    return true;
  }

  /**
   * Destroys a session.
   *
   * @param string A session ID
   *
   * @return boolean true, if the session was destroyed, otherwise an exception is thrown
   *
   * @throws sfDatabaseException If the session cannot be destroyed
   */
  public function sessionDestroy($id)
  {
    // get table/column
    $db_table = $this->getOption('db_table');
    $db_id_col = $this->getOption('db_id_col');

    // delete the record associated with this id
    $sql = 'DELETE FROM ' . $db_table . ' WHERE ' . $db_id_col . '= ?';

    try {
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(1, $id, PDO::PARAM_STR); // setString(1, $id);
      $stmt->execute();
    } catch (PDOException $e) {
      throw new sfDatabaseException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
    }
  }

  /**
   * Cleans up old sessions.
   *
   * @param int The lifetime of a session
   *
   * @return boolean true, if old sessions have been cleaned, otherwise an exception is thrown
   *
   * @throws sfDatabaseException If any old sessions cannot be cleaned
   */
  public function sessionGC($lifetime)
  {
    // determine deletable session time
    $time = time() - $lifetime;

    // get table/column
    $db_table = $this->getOption('db_table');
    $db_time_col = $this->getOption('db_time_col');

    // delete the record associated with this id
    $sql = 'DELETE FROM ' . $db_table . ' WHERE ' . $db_time_col . ' < ' . $time;

    try {
      $this->db->query($sql);

      return true;
    } catch (PDOException $e) {
      throw new sfDatabaseException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
    }
  }

  /**
   * Opens a session.
   *
   * @param string $path
   * @param string $name
   *
   * @return boolean true, if the session was opened, otherwise an exception is thrown
   * @throws sfDatabaseException If a connection with the database does not exist or cannot be created
   */
  public function sessionOpen($path, $name)
  {
    $e = null;
    try {
      $this->db = $this->manager->getDatabase($this->getOption('database'))->getConnection();
    } catch (Exception $e) {
    }

    if ($this->db == null || !$this->db instanceof PDO) {
      throw new sfDatabaseException(
          sprintf('PDO dabatase connection "%s" doesn\'t exist. Unable to open session.', $this->getOption('database')), sfDatabaseException::SESSION_ERROR);
    }

    return true;
  }

  /**
   * Reads a session.
   *
   * @param string A session ID
   *
   * @return boolean true, if the session was read, otherwise an exception is thrown
   *
   * @throws sfDatabaseException If the session cannot be read
   */
  public function sessionRead($id)
  {
    // get table/columns
    $db_table = $this->getOption('db_table');
    $db_data_col = $this->getOption('db_data_col');
    $db_id_col = $this->getOption('db_id_col');
    $db_time_col = $this->getOption('db_time_col');

    // prevent starting new transaction when session_start()
    // has been called from $this->regenerateId()
    if($this->getOption('use_transaction')
        && !$this->transactionStarted)
    {
      $this->transactionBegin();
      $this->transactionStarted = true;
    }

    try {
      $sql = 'SELECT ' . $db_data_col . ' FROM ' . $db_table . ' WHERE ' . $db_id_col . '=?';
      // for update is not supported by sqlite
      if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
        $sql .= ' FOR UPDATE';
      }

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(1, $id, PDO::PARAM_STR, 255);

      $stmt->execute();
      // it is recommended to use fetchAll so that PDO can close the DB cursor
      // we anyway expect either no rows, or one row with one column. fetchColumn, seems to be buggy #4777
      $sessionRows = $stmt->fetchAll(PDO::FETCH_NUM);

      if (count($sessionRows) == 1) {
        return base64_decode($sessionRows[0][0]);
      } else {
        // session does not exist, create it
        $sql = 'INSERT INTO ' . $db_table . '(' . $db_id_col . ', ' . $db_data_col . ', ' . $db_time_col . ') VALUES (?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_STR); // setString(1, $id);
        $stmt->bindValue(2, '', PDO::PARAM_STR); // setString(2, '');
        $stmt->bindValue(3, time(), PDO::PARAM_INT); // setInt(3, time());
        $stmt->execute();

        return '';
      }
    } catch (PDOException $e) {
      // If the insertion fails, it may be due to a race condition that
      // exists between multiple instances of this session handler in the
      // case where a new session is created by multiple script instances
      // at the same time (as can occur when Asynchronous Ajax Requests
      // or multiple session-aware frames exist).
      //
      // In this case, we attempt another SELECT operation which will
      // hopefully retrieve the session data inserted by the competing
      // FIXME: to do second session select
      throw new sfDatabaseException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
    }
  }

  /**
   * Writes session data.
   *
   * @param string A session ID
   * @param string A serialized chunk of session data
   *
   * @return boolean true, if the session was written, otherwise an exception is thrown
   *
   * @throws sfDatabaseException If the session data cannot be written
   */
  public function sessionWrite($id, $data)
  {
    // get table/column
    $db_table = $this->getOption('db_table');
    $db_data_col = $this->getOption('db_data_col');
    $db_id_col = $this->getOption('db_id_col');
    $db_time_col = $this->getOption('db_time_col');

    $sql = 'UPDATE ' . $db_table . ' SET ' . $db_data_col . ' = ?, ' . $db_time_col . ' = ' . time() . ' WHERE ' . $db_id_col . '= ?';

    try {
      $stmt = $this->db->prepare($sql);
      $data = base64_encode($data);
      $stmt->bindParam(1, $data, PDO::PARAM_STR); // setString(1, $data);
      $stmt->bindParam(2, $id, PDO::PARAM_STR); // setString(2, $id);
      $stmt->execute();

      return true;
    } catch (PDOException $e) {
      throw new sfDatabaseException(sprintf('PDOException was thrown when trying to manipulate session data. Message: %s', $e->getMessage()));
    }

    return false;
  }

  /**
   * Regenerates id that represents this storage.
   *
   * @param boolean $destroy Destroy session when regenerating?
   * @return boolean True if session regenerated, false if error
   */
  public function regenerate($destroy = false)
  {
    if (self::$sessionIdRegenerated) {
      return;
    }

    $currentId = session_id();
    $data = $this->sessionRead($currentId);
    parent::regenerate($destroy);
    $newId = session_id();
    $this->sessionRead($newId);

    return $this->sessionWrite($newId, $data);
  }

  /**
   * Begins the transaction
   *
   * @return boolean Returns true on success or false on failure.
   */
  protected function transactionBegin()
  {
    return $this->db->beginTransaction();
  }

  /**
   * Commits the transaction
   *
   * @return boolean Returns true on success or false on failure.
   */
  protected function transactionCommit()
  {
    return $this->db->commit();
  }

  /**
   * Executes the shutdown procedure.
   *
   */
  public function shutdown()
  {
    // commit transaction
    if($this->getOption('use_transaction') &&
        $this->transactionStarted)
    {
      $this->transactionCommit();
      // reset semaphore
      $this->transactionStarted = false;
    }
    parent::shutdown();
  }

}
