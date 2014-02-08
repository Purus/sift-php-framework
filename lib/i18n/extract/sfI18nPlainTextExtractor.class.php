<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Ini extractor class
 *
 * @package    Sift
 * @subpackage i18n_extract
 */
class sfI18nPlainTextExtractor extends sfConfigurable implements sfII18nExtractor
{
    /**
     * Array of default options
     *
     * @var array
     */
    protected $defaultOptions
        = array(
            // encoding
            'input_encoding'  => false,
            'output_encoding' => 'UTF-8'
        );

    /**
     * Extracts i18n strings.
     *
     * @param string $content Content of the file
     */
    public function extract($content)
    {
        // convert if configured
        if ($encoding = $this->getOption('input_encoding')) {
            $content = iconv($encoding, $this->getOption('output_encoding', 'UTF-8'), $content);
        }

        $lines = explode("\n", $content);
        $result = array();

        foreach ($lines as $line) {
            $line = trim($line);

            // this is a comment
            if (preg_match('/^\s?#/', $line)) {
                continue;
            }

            if (empty($line)) {
                continue;
            }
            $result[] = $line;
        }

        return $result;
    }

}
