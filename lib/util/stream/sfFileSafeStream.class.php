<?php

/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides atomicity and isolation for thread safe file manipulation using stream safe://
 *
 * <code>
 * file_put_contents('safe://myfile.txt', $content);
 *
 * $content = file_get_contents('safe://myfile.txt');
 *
 * unlink('safe://myfile.txt');
 * </code>
 *
 * @author     David Grudl
 * @package    Sift
 * @subpackage util_stream
 */
class sfFileSafeStreamWrapper extends sfStreamWrapper
{
    /**
     * Name of stream protocol - safe://
     */
    const PROTOCOL = 'safe';

    /**
     * The orignal file handle
     *
     * @var resource
     */
    private $handle;

    /**
     * The temporary file handle
     *
     * @var resource
     */
    private $tempHandle;

    /**
     * The orignal file path
     *
     * @var string
     */
    private $file;

    /**
     * The temporary file path
     *
     * @var string
     */
    private $tempFile;

    /**
     * Detele flag
     *
     * @var boolean
     */
    private $deleteFile;

    /**
     * Error detection flag
     *
     * @var boolean
     */
    private $writeError = false;

    /**
     * @see sfIStreamWrapper
     */
    public static function register($protocol = null, $flags = null)
    {
        return stream_wrapper_register($protocol ? $protocol : self::PROTOCOL, __CLASS__, $flags);
    }

    /**
     * Opens file.
     *
     * @param  string    file name with stream protocol
     * @param  string    mode - see fopen()
     * @param  int       STREAM_USE_PATH, STREAM_REPORT_ERRORS
     * @param  string    full path
     *
     * @return bool      true on success or false on failure
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        // trim protocol safe://
        $path = substr($path, strlen(self::PROTOCOL) + 3);
        // text | binary mode
        $flag = trim($mode, 'crwax+');
        $mode = trim($mode, 'tb');
        // use include_path?
        $use_path = (bool)(STREAM_USE_PATH & $options);
        // open file
        if ($mode === 'r') { // provides only isolation

            return $this->checkAndLock($this->tempHandle = fopen($path, 'r' . $flag, $use_path), LOCK_SH);
        } elseif ($mode === 'r+') {
            if (!$this->checkAndLock($this->handle = fopen($path, 'r' . $flag, $use_path), LOCK_EX)) {
                return false;
            }
        } elseif ($mode[0] === 'x') {
            if (!$this->checkAndLock($this->handle = fopen($path, 'x' . $flag, $use_path), LOCK_EX)) {
                return false;
            }
            $this->deleteFile = true;
        } elseif ($mode[0] === 'w' || $mode[0] === 'a' || $mode[0] === 'c') {
            if ($this->checkAndLock($this->handle = @fopen($path, 'x' . $flag, $use_path), LOCK_EX)) {
                $this->deleteFile = true;
            } elseif (!$this->checkAndLock($this->handle = fopen($path, 'a+' . $flag, $use_path), LOCK_EX)) {
                return false;
            }
        } else {
            trigger_error(sprintf('Unknown file mode "%s" given.', $mode), E_USER_WARNING);

            return false;
        }

        // create temporary file in the same directory to provide atomicity
        $tmp = '~~' . lcg_value() . '.tmp';
        if (!$this->tempHandle = fopen($path . $tmp, (strpos($mode, '+') ? 'x+' : 'x') . $flag, $use_path)) {
            $this->clean();

            return false;
        }
        $this->tempFile = realpath($path . $tmp);
        $this->file = substr($this->tempFile, 0, -strlen($tmp));

        // copy to temporary file
        if ($mode === 'r+' || $mode[0] === 'a' || $mode[0] === 'c') {
            $stat = fstat($this->handle);
            fseek($this->handle, 0);
            if ($stat['size'] !== 0 && stream_copy_to_stream($this->handle, $this->tempHandle) !== $stat['size']) {
                $this->clean();

                return false;
            }

            if ($mode[0] === 'a') { // emulate append mode
                fseek($this->tempHandle, 0, SEEK_END);
            }
        }

        return true;
    }

    /**
     * Checks handle and locks file.
     *
     * @return boolean
     */
    private function checkAndLock($handle, $lock)
    {
        if (!$handle) {
            return false;
        } elseif (!flock($handle, $lock)) {
            fclose($handle);

            return false;
        }

        return true;
    }

    /**
     * Error destructor.
     */
    private function clean()
    {
        flock($this->handle, LOCK_UN);
        fclose($this->handle);
        if ($this->deleteFile) {
            unlink($this->file);
        }
        if ($this->tempHandle) {
            fclose($this->tempHandle);
            unlink($this->tempFile);
        }
    }

    /**
     * Closes file.
     *
     * @return void
     */
    public function stream_close()
    {
        if (!$this->tempFile) { // 'r' mode
            flock($this->tempHandle, LOCK_UN);
            fclose($this->tempHandle);

            return;
        }

        flock($this->handle, LOCK_UN);
        fclose($this->handle);
        fclose($this->tempHandle);

        if ($this->writeError || !rename($this->tempFile, $this->file)) { // try to rename temp file
            unlink($this->tempFile); // otherwise delete temp file
            if ($this->deleteFile) {
                unlink($this->file);
            }
        }
    }

    /**
     * Reads up to length bytes from the file.
     *
     * @param integer $length The length
     *
     * @return string
     */
    public function stream_read($length)
    {
        return fread($this->tempHandle, $length);
    }

    /**
     * Writes the string to the file.
     *
     * @param  string $data The data to write
     *
     * @return integer The number of bytes that were successfully stored
     */
    public function stream_write($data)
    {
        $len = strlen($data);
        $res = fwrite($this->tempHandle, $data, $len);
        if ($res !== $len) {
            // disk full?
            $this->writeError = true;
        }

        return $res;
    }

    /**
     * Returns the position of the file.
     *
     * @return integer
     */
    public function stream_tell()
    {
        return ftell($this->tempHandle);
    }

    /**
     * Returns true if the file pointer is at end-of-file.
     *
     * @return boolena
     */
    public function stream_eof()
    {
        return feof($this->tempHandle);
    }

    /**
     * Sets the file position indicator for the file.
     *
     * @param integer $offest The offset position
     * @param integer $whence see fseek()
     *
     * @return integer Return true on success
     */
    public function stream_seek($offset, $whence)
    {
        return fseek($this->tempHandle, $offset, $whence) === 0; // ???
    }

    /**
     * Gets information about a file referenced by $this->tempHandle.
     *
     * @return array
     */
    public function stream_stat()
    {
        return fstat($this->tempHandle);
    }

    /**
     * Gets information about a file referenced by filename.
     *
     * @param  string    file name
     * @param  int       STREAM_URL_STAT_LINK, STREAM_URL_STAT_QUIET
     *
     * @return array
     */
    public function url_stat($path, $flags)
    {
        // This is not thread safe
        $path = substr($path, strlen(self::PROTOCOL) + 3);

        return ($flags & STREAM_URL_STAT_LINK) ? @lstat($path) : @stat($path); // intentionally @
    }

    /**
     * Deletes a file. On Windows unlink is not allowed till file is opened
     *
     * @param  string $path The file name with stream protocol
     *
     * @return boolean True on success or false on failure
     */
    public function unlink($path)
    {
        $path = substr($path, strlen(self::PROTOCOL) + 3);

        return unlink($path);
    }

}
