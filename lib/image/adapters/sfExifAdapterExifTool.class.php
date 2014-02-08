<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Exif reader / writer using Exiftool utility
 *
 * @package    Sift
 * @subpackage image
 * @link       http://www.sno.phy.queensu.ca/~phil/exiftool
 */
class sfExifAdapterExifTool extends sfExifAdapter
{
    /**
     * Array of default options
     *
     * @var array
     */
    protected $defaultOptions
        = array(
            'exiftool_executable' => 'exiftool'
        );

    /**
     *
     * @see sfExifAdapter
     */
    public function supportedCategories()
    {
        return array('EXIF', 'IPTC', 'XMP', 'COMPOSITE');
    }

    /**
     * Reads exif data from the file
     *
     * @param string $file Path to a file
     *
     * @return array
     * @throws sfFileException When file is not readable
     * @throws RuntimeException
     */
    public function getData($file)
    {
        if (!is_readable($file)) {
            throw new sfFileException(sprintf('File "%s" is not readable', $file));
        }

        // Request the full stream of meta data in JSON format.
        // -j option outputs in JSON, appending '#' to the -TAG prevents
        // screen formatting.
        $categories = sfExif::getCategories();
        $tags = '';

        foreach (array('EXIF', 'IPTC', 'XMP') as $category) {
            foreach ($categories[$category] as $field => $value) {
                $tags .= ' -' . $field . '#';
            }
        }

        foreach ($categories['COMPOSITE'] as $field => $value) {
            $tags .= ' -' . $field;
        }

        $command = '-j' . $tags . ' ' . $file;
        $results = json_decode($this->execute($command));

        if (is_array($results)) {
            return $this->processData((array)array_pop($results));
        }

        throw new RuntimeException('Unknown error running exiftool command');
    }

    /**
     * Executes a exiftool command.
     *
     * @param string $command The command to run
     *
     * @return mixed  The result of the command.
     */
    protected function execute($command)
    {
        $output = array();
        $retval = null;
        exec($this->getOption('exiftool_executable') . ' ' . escapeshellcmd($command), $output, $retval);

        if ($retval) {
            $this->log(sprintf("Error running command: %s", $command . "\n" . implode("\n", $output)));
        }

        if (is_array($output)) {
            $output = implode('', $output);
        }

        return $output;
    }

}
