<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfIRequest is an interface for request objects
 *
 * @package    Sift
 * @subpackage request
 */
interface sfIRequest extends sfIService {

  /**
   * GET
   */
  const GET = 'GET';

  /**
   * POST
   */
  const POST = 'POST';

  /**
   * PUT
   */
  const PUT = 'PUT';

  /**
   * DELETE
   */
  const DELETE = 'DELETE';

  /**
   * HEAD
   */
  const HEAD = 'HEAD';

  /**
   * Returns the request method
   */
  public function getMethod();

  /**
   * Sets the request method
   *
   * @param string $methodCode
   */
  public function setMethod($methodCode);

  /**
   * Returns the raw content of the request
   */
  public function getContent();

  /**
   * Returns the parameter
   *
   * @param string $name The parameter name
   * @param mixed $default The default value
   */
  public function getParameter($name, $default = null);

}
