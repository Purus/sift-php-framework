<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfZipArchive provides creating and unzipping zip archives.
 *
 *  - adds a functionality to report the ZIP file address
 *  (I needed it and I could not find out how in ZipArchive documentation).
 *  - resolve the problem of adding so many files to the archive
 *  due file descriptor limit. the sfZipArchive::addFile() handles this.
 *
 * @package    Sift
 * @subpackage archive
 * @link       http://www.php.net/manual/en/function.ziparchive-addfile.php#88266
 */
class sfZipArchive extends ZipArchive {

  protected $_archiveFileName = null;
  protected $_newAddedFilesCounter = 0;
  protected $_newAddedFilesSize = 100;

  /**
   *  Extracts a ZIP archive to the specified extract path
   *
   *  @param string $file The ZIP archive to extract (including the path)
   *  @param string $extractPath The path to extract the ZIP archive to
   *
   *  @return boolean TURE if the ZIP archive is successfully extracted, FALSE if there was an errror
   *
   */
  public static function extract($file, $extractPath)
  {
    $zip = new self;
    try
    {
      $zip->open($file);
      $result = $zip->extractTo($extractPath);
      $zip->close();
      return $result;
    }
    catch(sfPhpErrorException $e)
    {
      return false;
    }
  }

  /**
   * returns the name of the archive file.
   *
   * @return string
   */
  public function getArchiveFileName()
  {
    return $this->_archiveFileName;
  }

  /**
   * returns the number of files that are going to be added to ZIP
   * without reopenning the stream to file.
   *
   * @return int
   */
  public function getNewAddedFilesSize()
  {
    return $this->_newAddedFilesSize;
  }

  /**
   * sets the number of files that are going to be added to ZIP
   * without reopenning the stream to file. if no size is specified, default is 100.
   *
   * @param int
   * @return ZipArchiveImproved self reference
   */
  public function setNewlyAddedFilesSize($size = 100)
  {
    if(empty($size) || !is_int($size) || $size < 1)
    {
      $size = 100;
    }
    $this->_newAddedFilesSize = $size;
    return $this;
  }

  /**
   * opens a stream to a ZIP archive file. calls the ZipArchive::open() internally.
   * overwrites ZipArchive::open() to add the archiveFileName functionality.
   *
   * @param string $fileName
   * @param int $flags
   * return mixed
   */
  public function open($fileName, $flags = null)
  {
    $this->_archiveFileName = $fileName;
    $this->_newAddedFilesCounter = 0;
    $r = parent::open($fileName, $flags);
    if($r !== true)
    {
      throw new sfPhpErrorException(sprintf('{sfZipArchive} Cannot open file "%s", error "%s"', $fileName, $r));
    }
    return $r;
  }

  /**
   * Creates new zip archive (alias for open() with sfZipArchive::CREATE flag)
   *
   * @param string $fileName
   * @return mixed
   */
  public function create($fileName, $fileMode = 0666, $flags = self::CREATE)
  {
    try
    {
      $result = $this->open($fileName, $flags);
      // chmod our file
      @chmod($fileName, $fileMode);
      return $result;
    }
    catch(sfPhpException $e)
    {
      throw $e;
    }
  }

  /**
   * Closes the stream to ZIP archive file. calls the ZipArchive::close() internally.
   * overwrites ZipArchive::close() to add the archiveFileName functionality.
   *
   * @return bool
   */
  public function close()
  {
    $this->_archiveFileName = null;
    $this->_newAddedFilesCounter = 0;
    return parent::close();
  }

  /**
   * closes the connection to ZIP file and openes the connection again.
   *
   * @return bool
   */
  public function reopen()
  {
    $archiveFileName = $this->_archiveFileName;
    if(!$this->close())
    {
      return false;
    }
    return $this->open($archiveFileName, self::CREATE);
  }

  /**
   * Adds a file to a ZIP archive from the given path. calls the ZipArchive::addFile() internally.
   * overwrites ZipArchive::addFile() to handle maximum file connections in operating systems.
   *
   * @param string $fileName The path to file to be added to archive
   * @param string $localName If supplied, this is the local name inside the ZIP archive that will override the filename.
   * @param integer $start Parameter is not used
   * @param integer $lentg Parameter is not used
   * @return bool
   */
  public function addFile($fileName, $localName = null, $start = 0, $length = 0)
  {
    if(!is_readable($fileName))
    {
      throw new InvalidArgumentException(sprintf('The file "%s" is not readable or does not exist.', $fileName));
    }

    if($this->_newAddedFilesCounter >= $this->_newAddedFilesSize)
    {
      $this->reopen();
    }
    $added = parent::addFile($fileName, $localName);
    if($added)
    {
      $this->_newAddedFilesCounter++;
    }
    return $added;
  }

  /**
   * Adds directory to archive
   *
   * @param string $path
   * @param string $newname
   */
  public function addDir($path, $newname)
  {
    $nodes = glob($path . DIRECTORY_SEPARATOR . "*");
    if(empty($nodes))
    {
      return false;
    }

    foreach($nodes as $node)
    {
      // exclude temporary files
      if(substr($node, -1) != "~")
      {
        $newnode = substr($node, strlen($path) + 1);
        $newnode = $newname . DIRECTORY_SEPARATOR . $newnode;
        if(is_dir($node))
        {
          $this->addDir($node, $newnode);
        }
        elseif(is_file($node))
        {
          $this->addFile($node, $newnode);
        }
      }
    }
  }

}
