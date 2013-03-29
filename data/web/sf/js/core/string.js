/*
 * This file is part of Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if(typeof String.prototype.trim !== 'function')
{
  /**
   * Add string.trim() function since IE doesn't support this
   * Trim function is used in logParams function
   *
   * @link http://stackoverflow.com/questions/2308134/trim-in-javascript-not-working-in-ie
   */
  String.prototype.trim = function()
  {
    return this.replace(/^\s+|\s+$/g, '');
  };
}
