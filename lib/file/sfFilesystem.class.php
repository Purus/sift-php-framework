<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFilesystem provides basic utility to manipulate the file system.
 *
 * @package    Sift
 * @subpackage file
 */
class sfFilesystem
{
  /**
   * Directory separator constant
   */
  const DS = DIRECTORY_SEPARATOR;

  /**
   * Logger instance
   *
   * @var sfLogger
   */
  protected $logger = null;

  /**
   * Formatter instance
   *
   * @var sfCliFormatter
   */
  protected $formatter = null;

  /**
   *
   * Constructor
   *
   * @param sfLogger $logger
   */
  public function __construct(sfLogger $logger = null, sfCliFormatter $formatter = null)
  {
    $this->logger = $logger;
    $this->formatter = $formatter;
  }

  /**
   * Copies a file.
   *
   * This method only copies the file if the origin file is newer than the target file.
   *
   * By default, if the target already exists, it is not overriden.
   *
   * To override existing files, pass the "override" option.
   *
   * @param string $originFile  The original filename
   * @param string $targetFile  The target filename
   * @param array  $options     An array of options
   */
  public function copy($originFile, $targetFile, $options = array())
  {
    if (!array_key_exists('override', $options))
    {
      $options['override'] = false;
    }

    // we create target_dir if needed
    if (!is_dir(dirname($targetFile)))
    {
      $this->mkdirs(dirname($targetFile));
    }

    $mostRecent = false;
    if (file_exists($targetFile))
    {
      $statTarget = stat($targetFile);
      $stat_origin = stat($originFile);
      $mostRecent = ($stat_origin['mtime'] > $statTarget['mtime']) ? true : false;
    }

    if ($options['override'] || !file_exists($targetFile) || $mostRecent)
    {
      $this->logSection('file+', $targetFile);
      copy($originFile, $targetFile);
    }

    // fluent interface
    return $this;
  }

  /**
   * Checks if given directory does exist and is a directory
   *
   * @param string $directory
   * @return boolean Returns TRUE if the filename exists and is a directory, FALSE otherwise.
   */
  public function isDirectory($directory)
  {
    return is_dir($directory);
  }

  /**
   * Checks if given filename does exist and is a file
   *
   * @param string $filename
   * @return boolean Returns TRUE if the filename exists and is a regular file, FALSE  otherwise.
   */
  public function isFile($filename)
  {
    return is_file($filename);
  }

  /**
   * Creates a directory recursively.
   *
   * @param  string $path  The directory path
   * @param  int    $mode  The directory mode
   *
   * @return bool true if the directory has been created, false otherwise
   */
  public function mkdirs($path, $mode = 0777)
  {
    if (is_dir($path))
    {
      return true;
    }

    $this->logSection('dir+', $path);

    return @mkdir($path, $mode, true);
  }

  /**
   * Creates empty files.
   *
   * @param mixed $files  The filename, or an array of filenames
   */
  public function touch($files)
  {
    if (!is_array($files))
    {
      $files = array($files);
    }

    foreach ($files as $file)
    {
      $this->logSection('file+', $file);

      touch($file);
    }
  }

  /**
   * Removes files or directories.
   *
   * @param mixed $files  A filename or an array of files to remove
   */
  public function remove($files)
  {
    if (!is_array($files))
    {
      $files = array($files);
    }

    $files = array_reverse($files);
    foreach ($files as $file)
    {
      if (is_dir($file) && !is_link($file))
      {
        $this->logSection('dir-', $file);

        rmdir($file);
      }
      else
      {
        $this->logSection(is_link($file) ? 'link-' : 'file-', $file);

        unlink($file);
      }
    }
  }

  /**
   * Change mode for an array of files or directories.
   *
   * @param array   $files  An array of files or directories
   * @param integer $mode   The new mode
   * @param integer $umask  The mode mask (octal)
   */
  public function chmod($files, $mode, $umask = 0000)
  {
    $currentUmask = umask();
    umask($umask);

    if (!is_array($files))
    {
      $files = array($files);
    }

    foreach ($files as $file)
    {
      $this->logSection(sprintf('chmod %o', $mode), $file);
      chmod($file, $mode);
    }

    umask($currentUmask);
  }

  /**
   * Renames a file.
   *
   * @param string $origin  The origin filename
   * @param string $target  The new filename
   */
  public function rename($origin, $target)
  {
    // we check that target does not exist
    if (is_readable($target))
    {
      throw new sfException(sprintf('Cannot rename because the target "%" already exist.', $target));
    }

    $this->logSection('rename', $origin.' > '.$target);
    rename($origin, $target);
  }

  /**
   * Creates a symbolic link or copy a directory.
   *
   * @param string $originDir      The origin directory path
   * @param string $targetDir      The symbolic link name
   * @param bool   $copyOnWindows  Whether to copy files if on windows
   */
  public function symlink($originDir, $targetDir, $copyOnWindows = false)
  {
    if (!function_exists('symlink') && $copyOnWindows)
    {
      $finder = sfFinder::type('any');
      $this->mirror($originDir, $targetDir, $finder);
      return;
    }

    $ok = false;
    if (is_link($targetDir))
    {
      if (readlink($targetDir) != $originDir)
      {
        unlink($targetDir);
      }
      else
      {
        $ok = true;
      }
    }

    if (!$ok)
    {
      $this->logSection('link+', $targetDir);
      symlink($originDir, $targetDir);
    }
  }

  /**
   * Creates a symbolic link using a relative path if possible.
   *
   * @param string $originDir      The origin directory path
   * @param string $targetDir      The symbolic link name
   * @param bool   $copyOnWindows  Whether to copy files if on windows
   */
  public function relativeSymlink($originDir, $targetDir, $copyOnWindows = false)
  {
    if (function_exists('symlink') || !$copyOnWindows)
    {
      $originDir = $this->calculateRelativeDir($targetDir, $originDir);
    }
    $this->symlink($originDir, $targetDir, $copyOnWindows);
  }

  /**
   * Mirrors a directory to another.
   *
   * @param string   $originDir  The origin directory
   * @param string   $targetDir  The target directory
   * @param sfFinder $finder     An sfFinder instance
   * @param array    $options    An array of options (see copy())
   */
  public function mirror($originDir, $targetDir, $finder, $options = array())
  {
    foreach ($finder->relative()->in($originDir) as $file)
    {
      if (is_dir($originDir.DIRECTORY_SEPARATOR.$file))
      {
        $this->mkdirs($targetDir.DIRECTORY_SEPARATOR.$file);
      }
      else if (is_file($originDir.DIRECTORY_SEPARATOR.$file))
      {
        $this->copy($originDir.DIRECTORY_SEPARATOR.$file, $targetDir.DIRECTORY_SEPARATOR.$file, $options);
      }
      else if (is_link($originDir.DIRECTORY_SEPARATOR.$file))
      {
        $this->symlink($originDir.DIRECTORY_SEPARATOR.$file, $targetDir.DIRECTORY_SEPARATOR.$file);
      }
      else
      {
        throw new sfException(sprintf('Unable to guess "%s" file type.', $originDir . '/' . $file));
      }
    }
  }

  /**
   * Executes a shell command.
   *
   * @param string $cmd  The command to execute on the shell
   */
  public function sh($cmd)
  {
    $this->logSection('exec', $cmd);

    ob_start();
    passthru($cmd.' 2>&1', $return);
    $content = ob_get_contents();
    ob_end_clean();

    if ($return > 0)
    {
      throw new sfException(sprintf('Problem executing command %s', "\n".$content));
    }

    return $content;
  }

  /**
   * Executes a shell command.
   *
   * @param string $cmd            The command to execute on the shell
   * @param array  $stdoutCallback A callback for stdout output
   * @param array  $stderrCallback A callback for stderr output
   *
   * @return array An array composed of the content output and the error output
   */
  public function execute($cmd, $stdoutCallback = null, $stderrCallback = null)
  {
    $this->logSection('exec ', $cmd);

    $descriptorspec = array(
      1 => array('pipe', 'w'), // stdout
      2 => array('pipe', 'w'), // stderr
    );

    $process = proc_open($cmd, $descriptorspec, $pipes);
    if (!is_resource($process))
    {
      throw new RuntimeException('Unable to execute the command.');
    }

    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $output = '';
    $err = '';
    while (!feof($pipes[1]) || !feof($pipes[2]))
    {
      foreach ($pipes as $key => $pipe)
      {
        if (!$line = fread($pipe, 128))
        {
          continue;
        }

        if (1 == $key)
        {
          // stdout
          $output .= $line;
          if ($stdoutCallback)
          {
            call_user_func($stdoutCallback, $line);
          }
        }
        else
        {
          // stderr
          $err .= $line;
          if ($stderrCallback)
          {
            call_user_func($stderrCallback, $line);
          }
        }
      }

      usleep(100000);
    }

    fclose($pipes[1]);
    fclose($pipes[2]);

    if (($return = proc_close($process)) > 0)
    {
      throw new RuntimeException('Problem executing command.', $return);
    }

    return array($output, $err);
  }

  /**
   * Replaces tokens in an array of files.
   *
   * @param array  $files       An array of filenames
   * @param string $beginToken  The begin token delimiter
   * @param string $endToken    The end token delimiter
   * @param array  $tokens      An array of token/value pairs
   */
  public function replaceTokens($files, $beginToken, $endToken, $tokens)
  {
    if (!is_array($files))
    {
      $files = array($files);
    }

    foreach ($files as $file)
    {
      $content = file_get_contents($file);
      foreach ($tokens as $key => $value)
      {
        $content = str_replace($beginToken.$key.$endToken, $value, $content, $count);
      }

      $this->logSection('tokens', $file);

      file_put_contents($file, $content);
    }
  }

  /**
   * Logs a message in a section.
   *
   * @param string $section  The section name
   * @param string $message  The message
   */
  protected function logSection($section, $message)
  {
    if($this->logger)
    {
      $message = $this->formatter ? $this->formatter->formatSection($section, $message) : $section.' '.$message;
      $this->logger->log($message);
    }
  }

  /**
   * Calculates the relative path from one to another directory.
   * If they share no common path the absolute target dir is returned
   *
   * @param string $from directory from that the relative path shall be calculated
   * @param string $to target directory
   */
  protected function calculateRelativeDir($from, $to)
  {
    $commonLength = 0;
    $minPathLength = min(strlen($from), strlen($to));
    // count how many chars the strings have in common
    for ($i = 0; $i < $minPathLength; $i++)
    {
      if ($from[$i] != $to[$i]) break;
      if ($from[$i] == DIRECTORY_SEPARATOR) $commonLength = $i + 1;
    }
    if ($commonLength)
    {
      $levelUp = substr_count($from, DIRECTORY_SEPARATOR, $commonLength);
      // up that many level
      $relativeDir  = str_repeat("..".DIRECTORY_SEPARATOR, $levelUp);
      // down the remaining $to path
      $relativeDir .= substr($to, $commonLength);
      return $relativeDir;
    }
    return $to;
  }

  /**
   * Returns temporary directory
   *
   * @return string
   */
  public static function getTmpDir()
  {
    return sfToolkit::getTmpDir();
  }

  /**
   * Gets extension of filename
   *
   * @param string $filename Filename
   * @return string
   */
  public static function getFileExtension($filename)
  {
    // finds the last occurence of .
    $tmp = strrpos($filename, '.');
    if(!$tmp)
    {
      return '';
    }
    return substr($filename, $tmp + 1);
  }

  /**
   * Gets name of filename without extension
   *
   * @param string $filename Filename
   * @return string
   */
  public static function getFilename($filename)
  {
    $tmp = strrpos($filename, '.');
    if(!$tmp)
    {
      return $filename;
    }
    return substr($filename, 0, $tmp);
  }

  /**
   * Formats filesize
   *
   * @param integer $size Size of the file in bytes
   * @param integer $round Precision
   * @return string Formatted filesize (100 kB)
   */
  public static function formatFileSize($size, $round = 1)
  {
    static $a = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB');
    $pos = 0;
    while($size >= 1024)
    {
      $size /= 1024;
      $pos++;
    }
    return round($size, $round) . ' ' . $a[$pos];
  }

  /**
   * Returns filesize
   *
   * @param string $file Absolute path to a file
   * @param boolean $format Format the size?
   * @param integer $round Precision
   * @return string Filesize
   */
  public static function getFileSize($file, $format = false, $round = 1)
  {
    $size = sprintf('%u', filesize($file));
    if($format)
    {
      $size = self::formatFileSize($size, $round);
    }
    return $size;
  }

  public static function sanitizeFilename($name)
  {
    $extension = self::getFileExtension($name);
    $file = self::getFilename($name);
    $file = sfUtf8::lower(sfUtf8::clean($file));
    $file = sfUtf8::ascii($file);
    $file = str_replace(array(' ', '.', '(', ')', '-'), array('_', '', '', '', '_'), $file);
    $file = preg_replace('/_{2,}/', '_', $file);

    return sprintf('%s%s', $file, $extension ? ('.'. strtolower($extension)) : '');
  }

  /**
   * Returns mime type of the file
   *
   * @access public
   * @param string $file absolute path to a file
   * @return string
   */
  public static function getMimeType($file)
  {
    return sfMimeType::getTypeFromFile($file);
  }

}
