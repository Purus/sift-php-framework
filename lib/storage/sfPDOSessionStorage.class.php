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
class sfPDOSessionStorage extends sfSessionStorage {

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
    'db_table' => 'session'
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
   * @param sfContext A sfContext instance
   * @param array     An associative array of initialization parameters
   *
   * @return boolean true, if initialization completes successfully, otherwise false
   *
   * @throws InitializationException If an error occurs while initializing this Storage
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

    try
    {
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(1, $id, PDO::PARAM_STR); // setString(1, $id);
      $stmt->execute();
    }
    catch(PDOException $e)
    {
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

    try
    {
      $this->db->query($sql);
      return true;
    }
    catch(PDOException $e)
    {
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
    $this->db = $this->manager->getDatabase($this->getOption('database'))->getConnection();

    if($this->db == null || !$this->db instanceof PDO)
    {
      throw new sfDatabaseException(sprintf('PDO dabatase connection "%s" doesn\'t exist. Unable to open session.', $database));
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

    try
    {
      $sql = 'SELECT ' . $db_data_col . ' FROM ' . $db_table . ' WHERE ' . $db_id_col . '=?';

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(1, $id, PDO::PARAM_STR, 255);

      $stmt->execute();
      if($data = $stmt->fetchColumn())
      {
        return $data;
      }
      else
      {
        // session does not exist, create it
        $sql = 'INSERT INTO ' . $db_table . '(' . $db_id_col . ', ' . $db_data_col . ', ' . $db_time_col . ') VALUES (?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_STR); // setString(1, $id);
        $stmt->bindValue(2, '', PDO::PARAM_STR); // setString(2, '');
        $stmt->bindValue(3, time(), PDO::PARAM_INT); // setInt(3, time());
        $stmt->execute();

        return '';
      }
    }
    catch(PDOException $e)
    {
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

    try
    {
      $stmt = $this->db->prepare($sql);
      $stmt->bindParam(1, $data, PDO::PARAM_STR); // setString(1, $data);
      $stmt->bindParam(2, $id, PDO::PARAM_STR); // setString(2, $id);
      $stmt->execute();
      return true;
    }
    catch(PDOException $e)
    {
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
    if(self::$sessionIdRegenerated)
    {
      return;
    }

    $currentId = session_id();
    parent::regenerate($destroy);
    $newId = session_id();
    $this->sessionRead($newId);
    return $this->sessionWrite($newId, $this->sessionRead($currentId));
  }

  /**
   * Executes the shutdown procedure.
   *
   */
  public function shutdown()
  {
  }

}
