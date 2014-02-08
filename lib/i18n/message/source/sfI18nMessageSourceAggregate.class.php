<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfI18nMessageSourceAggreagate class aggregates more message sources
 *
 * @package Sift
 * @subpackage i18n
 */
class sfI18nMessageSourceAggregate extends sfI18nMessageSource
{
  /**
   * Array of message sources
   *
   * @var array
   */
  protected $messageSources = array();

  /**
   * Constructor.
   *
   * @param array $messageSources Array of sources
   */
  public function __construct($messageSources)
  {
    $this->messageSources = $messageSources;
  }

  /**
   * Sets culture
   *
   * @param string $culture
   */
  public function setCulture($culture)
  {
    parent::setCulture($culture);

    foreach ($this->messageSources as $messageSource) {
      $messageSource->setCulture($culture);
    }
  }

  /**
   * Loads the messages from a XLIFF file.
   *
   * @param string XLIFF file.
   * @return array of messages.
   */
  public function &loadData($sources)
  {
    $messages = array();
    foreach ($sources as $source) {
      if (false === $source[0]->isValidSource($source[1])) {
        continue;
      }
      $data = $source[0]->loadData($source[1]);
      if (is_array($data)) {
        $messages = array_merge($data, $messages);
      }
    }

    return $messages;
  }

  /**
   * Gets the source for a specific message catalogue and cultural variant.
   *
   * @param string message catalogue
   * @return array Array of source paths
   */
  public function getSource($variant)
  {
    $sources = array();
    foreach ($this->messageSources as $messageSource) {
      $sources[] = array($messageSource, $messageSource->getSource(str_replace($messageSource->getId(), '', $variant)));
    }

    return $sources;
  }

  /**
   * Returns original source
   *
   * @see sfII18nMessageSource
   */
  public function getOriginalSource()
  {
    throw new BadMethodCallException('Not implemented for this source');
  }

  /**
   * Determines if the file source is valid.
   *
   * @param string XLIFF file
   * @return boolean true if valid, false otherwise.
   */
  public function isValidSource($sources)
  {
    foreach ($sources as $source) {
      if (false === $source[0]->isValidSource($source[1])) {
        continue;
      }

      return true;
    }

    return false;
  }

  /**
   * Gets all the variants of a particular catalogue.
   *
   * @param string catalogue name
   * @return array list of all variants for this catalogue.
   */
  public function getCatalogueList($catalogue = null)
  {
    $variants = array();
    foreach ($this->messageSources as $messageSource) {
      foreach ($messageSource->getCatalogueList($catalogue) as $variant) {
        $variants[] = $messageSource->getId().$variant;
      }
    }

    return $variants;
  }

  /**
   * Returns last modified timestamp
   *
   * @param array $sources
   * @return integer
   */
  protected function getLastModified($sources)
  {
    $lastModified = time();
    foreach ($sources as $source) {
      if (0 !== $sourceLastModified = $source[0]->getLastModified($source[1])) {
        $lastModified = min($lastModified, $sourceLastModified);
      }
    }

    return $lastModified;
  }

  /**
   * Returns an id of this source
   *
   * @return string
   */
  public function getId()
  {
    $id = '';
    foreach ($this->messageSources as $messageSource) {
      $id .= $messageSource->getId();
    }

    return md5($id);
  }

  /**
   * @see sfII18nMessageSource
   * @throws BadMethodCallException
   */
  public function catalogues()
  {
    throw new BadMethodCallException('Not implemented for this source');
  }

  /**
   * @see sfII18nMessageSource
   */
  public function save($catalogue = 'messages')
  {
    throw new BadMethodCallException('Not implemented for this source');
  }

  /**
   * @see sfII18nMessageSource
   */
  public function update($text, $target, $comments = '', $catalogue = 'messages')
  {
    throw new BadMethodCallException('Not implemented for this source');
  }

  /**
   * @see sfII18nMessageSource
   */
  public function delete($message, $catalogue = 'messages')
  {
    throw new BadMethodCallException('Not implemented for this source');
  }

}
