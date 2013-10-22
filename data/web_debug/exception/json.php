<?php
/*
 * This file is part of the Sift PHP framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

echo json_encode(array(
  'result' => false,
  'html' => sprintf('<strong>%s</strong> %s', $name, $message),
  'debug_backtrace' => $debug_backtrace
));
