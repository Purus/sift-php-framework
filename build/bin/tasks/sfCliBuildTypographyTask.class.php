<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds typography data files
 *
 * @package    Sift
 * @subpackage build
 */
class sfCliBuildTypographyTask extends sfCliBaseBuildTask
{

    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->aliases = array('typo');
        $this->namespace = '';
        $this->name = 'typography';
        $this->briefDescription = 'Builds typography data files';

        $scriptName = $this->environment->get('script_name');

        $this->detailedDescription
            = <<<EOF
The [typography|INFO] task converts typographic information from Tex format to Sift internal format

EOF;
    }

    /**
     * @see sfCliTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        // load tex parser
        require_once dirname(__FILE__) . '/lib/sfTexHyphenationParser.class.php';

        $this->build();

        $this->logSection($this->getFullName(), 'Done.');
    }

    protected function build()
    {
        $dataSourceDir = $this->environment->get('build_data_dir') . '/typo';
        $i18nDir = $this->environment->get('i18n_data_dir') . '/typo';

        $files = glob($dataSourceDir . '/*.tex');

        // invalid!
        $invalidLocales = array();
        foreach ($files as $file) {
            $fileName = basename($file);

            preg_match('/hyph-([a-z_-]+)\.tex/i', $fileName, $matches);
            list(, $locale) = $matches;

            $subLocale = explode('_', str_replace('-', '_', $locale));
            if (isset($subLocale[1])) {
                $locale = $subLocale[0] . '_' . strtoupper($subLocale[1]);
            }

            if (in_array($locale, $invalidLocales)) {
                continue;
            }

            $this->logSection(
                $this->getFullName(),
                sprintf('Parsing text file "%s" for locale "%s"', basename($file), $locale)
            );

            $parser = new sfTexParser();
            $parser->parseTexFile($file);

            $data = array();

            $data = array_merge(
                $data,
                array(
                    'patterns' => $parser->patterns,
                    'hyphenation' => $parser->hyphenation,
                )
            );

            $array = array();

            if (is_readable($dataSourceDir . '/' . $locale . '.php')) {
                $array = include $dataSourceDir . '/' . $locale . '.php';
            }

            if (!isset($array['abbreviations'])) {
                $array['abbreviations'] = array();
            }

            if (!isset($array['prepositions'])) {
                $array['prepositions'] = array();
            }

            if (!isset($array['conjunctions'])) {
                $array['conjunctions'] = array();
            }

            $data = array_merge($data, $array);

            file_put_contents($i18nDir . '/' . $locale . '.dat', serialize($data));
        }

    }

}
