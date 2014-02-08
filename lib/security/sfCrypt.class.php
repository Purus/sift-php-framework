<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCrypt class provides an abstraction layer to the PHP module mcrypt. Provides encryption and decryption.
 *
 * @package    Sift
 * @subpackage security
 */
class sfCrypt {

  // child classes should use ::getInstance()
  private static $instance;

  protected
    $cryptModule = false,
    $mode,
    $algorithm,
    $key,
    // md5 hash size
    $keyHashSize = 32;

  /**
   * Constructs a with the given mode, algorithm, key as a parameter.
   *
   * <code>
   *  $sfCrypt = new sfCrypt();
   *  $encrypted_text = $sfCrypt->encrypt('something_secret');
   *  $plain_text = $sfCrypt->decrypt($encrypted_text);
   * </code>
   *
   * @param string $key Path to the key file
   * @param string $mode Crypt mode
   * @param string $algorithm
   * @throws sfInitializationException
   * @throws InvalidArgumentException
   * @throws UnexpectedValueException
   * @link http://www.php.net/mcrypt
   */
  public function __construct($key = null, $mode = 'ecb',
          $algorithm = 'tripledes')
  {
    if(!extension_loaded('mcrypt'))
    {
      throw new sfInitializationException('{sfCrypt} You must install the php mcrypt module (http://www.php.net/mcrypt)');
    }

    $this->mode = ($mode == null) ? sfConfig::get('sf_crypt_mode', 'ecb') : $mode;
    $this->algorithm = ($algorithm == null) ? sfConfig::get('sf_crypt_algorithm', 'tripledes') : $algorithm;

    if($key == null)
    {
      $key = sfConfig::get('sf_crypt_key');
    }

    if(sfToolkit::isPathAbsolute($key))
    {
      $key = $this->loadKeyFromFile($key);
    }

    $this->key = $key;

    if(empty($this->key))
    {
      throw new InvalidArgumentException('{sfCrypt} Encryption key is missing');
    }

    $this->cryptModule = mcrypt_module_open($this->algorithm, '', $this->mode, '');
    $this->ivSize  = mcrypt_enc_get_iv_size($this->cryptModule);
    $this->keySize = mcrypt_enc_get_key_size($this->cryptModule);

    if($this->cryptModule === false)
    {
      throw new sfInitializationException(sprintf('{sfCrypt} Cannot load encryption module "%s"', $this->algorithm));
    }

    $this->key = substr($this->key, 0, $this->keySize);

    if(strlen($this->key) > 2048)
    {
      throw new UnexpectedValueException('Requested key is too large, use 2048 bytes or less.');
    }

  }

  /**
   * Returns the global sfCrypt instance.
   *
   * @return sfCrypt
   */
  public static function getInstance()
  {
    if(!isset(self::$instance))
    {
      self::$instance = new sfCrypt();
    }
    return self::$instance;
  }

  /**
   *  Loads the main cryptographic key from the crypt.key
   *
   * @param string $file Path to the file (crypt.key)
   *
   * @return string The decoded key string
   * @throws sfFileException If there are problems during the key loading process
   */
  protected function loadKeyFromFile($file)
  {
    //load key from file
    $key = @file_get_contents($file);

    if($key === false)
    {
      throw new sfFileException(sprintf('Could not read key for cryptography from file "%s"', $file));
    }

    //decode key if possible
    $decodedKey = base64_decode($key);
    if($decodedKey === false)
    {
      throw new sfFileException('Invalid key for cryptography defined. Generate new one!');
    }

    return $decodedKey;
  }

  /**
   * Returns the encryption mode
   *
   * @return string
   * @access public
   */
  public function getMode()
  {
    return $this->mode;
  }

  /**
   * Returns the encryption algorithm
   *
   * @return string
   * @access public
   */
  public function getAlgorithm()
  {
    return $this->algorithm;
  }

  /**
   * Returns the encryption key
   *
   * @return string
   * @access public
   */
  public function getKey()
  {
    return $this->key;
  }

  /**
   * Encrypts an arbitrary variable, usually a string.
   * Returns a string with the ciphertext
   * (base64 encoded, optionally url-safe).
   *
   * @param string string
   * @return string encrypted for $string - (url-safe) base64 encoded
   * @access public
   */
  public function encrypt($string, $urlSafe = false)
  {
    if(empty($string))
    {
      throw new sfException('{sfCrypt} You can not encrypt an empty string.');
    }

    // create random IV
    $iv = mcrypt_create_iv($this->ivSize, strstr(PHP_OS, 'WIN') ?
            MCRYPT_RAND : MCRYPT_DEV_RANDOM);

    mcrypt_generic_init($this->cryptModule, $this->key, $iv);

    // encrypt string and prepend IV
    $encrypted = mcrypt_generic($this->cryptModule, $string);
    $encrypted = $iv . $encrypted;

    // generate hash and append to message (EtA, encrypt-then-authenticate))
    $hash = md5($encrypted);
    $encrypted .= $hash;

    return ($urlSafe) ? sfSafeUrl::encode($encrypted) :
      base64_encode($encrypted);
  }

  /**
   * Returns the decrypted string
   *
   * @param string string
   * @return string
   * @access public
   */
  public function decrypt($string, $safeUrl = false)
  {
    if(empty($string))
    {
      throw new sfException('{sfCrypt} You can not decrypt an empty string.');
    }

    $string = $safeUrl ? sfSafeUrl::decode($string) : base64_decode($string);

    // the string length has to be at least ivSize + hashSize + 1
    if(strlen($string) < ($this->ivSize + $this->keyHashSize + 1))
    {
      throw new UnexpectedValueException('Ciphertext is too small.');
    }

    // extract and remove hmac from end of ciphertext
    $hashGiven = substr($string, (-1) * $this->keyHashSize);
    $string    = substr($string, 0, (-1) * $this->keyHashSize);

    // generate real hash ..., 32 chars long
    $hashReal  = md5($string);
    // ... and compare with given one for authentication
    if($hashGiven !== $hashReal)
    {
      throw new sfSecurityException('Invalid hash received.');
    }

    // extract IV from ciphertext
    $iv = substr($string, 0, $this->ivSize);

    // init crypt. module with key and IV
    mcrypt_generic_init($this->cryptModule, $this->key, $iv);

    // remove IV from ciphertext and decrypt
    $string = substr($string, $this->ivSize);

    $decrypted = mdecrypt_generic($this->cryptModule, $string);

    // right trim zero-padding (caused by CBC mode)
    $decrypted = rtrim($decrypted, "\0");
    return $decrypted;
  }

  /**
   * Deconstructs a td.
   *
   * @return void
   * @access public
   */
  public function __destruct()
  {
    if($this->cryptModule)
    {
      mcrypt_generic_deinit($this->cryptModule);
      mcrypt_module_close($this->cryptModule);
    }
  }

}
