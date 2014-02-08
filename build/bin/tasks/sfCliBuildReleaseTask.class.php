<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Builds a release of the Sift PHP frameworkd
 *
 * @package    Sift
 * @subpackage build
 */
class sfCliBuildReleaseTask extends sfCliBaseBuildTask
{

    protected $version, $stability;

    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->addArguments(
            array(
                new sfCliCommandArgument('version', sfCliCommandArgument::REQUIRED, 'The release version'),
                new sfCliCommandArgument('stability', sfCliCommandArgument::REQUIRED, 'The release stability'),
            )
        );

        $this->addOptions(
            array(
                new sfCliCommandOption('non-interactive', null, sfCliCommandOption::PARAMETER_NONE, 'Skip interactive prompts'),
                new sfCliCommandOption('nocompress', null, sfCliCommandOption::PARAMETER_NONE, 'Do not compress the package'),
                new sfCliCommandOption('skip-tests', 'st', sfCliCommandOption::PARAMETER_NONE, 'Skip tests'),
                new sfCliCommandOption('exclude-ip-database', null, sfCliCommandOption::PARAMETER_NONE, 'Exclude ip2country database')
            )
        );

        $this->aliases = array();
        $this->namespace = '';
        $this->name = 'release';
        $this->briefDescription = 'Builds a release for distribution';

        $this->detailedDescription
            = <<<EOF
The [release|INFO] task builds a release for distribution using PEAR channel

EOF;
    }

    /**
     * @see sfCliTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $this->interactive = !$options['non-interactive'];

        $stability = $arguments['stability'];
        $version = $arguments['version'];

        // FIXME: some troubles with this
        /*
        if(($stability == 'beta' || $stability == 'alpha'))
        {
          list($latest) = $this->getFilesystem()->execute('git rev-parse --verify --short HEAD');
          if(!isset($latest))
          {
            throw new sfCliCommandException('Unable to find last revision!');
          }
          // make a PEAR compatible version
          $version = $version . '-dev'. trim($latest);
          // $version = $version . '.'.$stability;
        }
        */

        $this->version = $version;
        $this->stability = $stability;

        $this->logSection($this->getFullName(), sprintf('Preparing the release "%s".', $version));

        if (!$options['skip-tests']) {
            $result = $this->runTests();
            if (!$result) {
                $this->logSection($this->getFullName(), 'Release process aborted. Some tests failed.');

                return false;
            }
        } else {
            $this->logSection($this->getFullName(), 'Skipped tests.');
        }

        $this->build($arguments, $options);
    }

    protected function runTests()
    {
        require_once($this->environment->get('sf_sift_lib_dir') . '/vendor/lime/lime.php');

        $h = new lime_harness(array(
            'output'          => new lime_output_color(),
            'error_reporting' => true,
            'verbose'         => false
        ));

        $h->base_dir = $this->environment->get('sf_sift_test_dir');

        // unit tests
        $h->register_glob($h->base_dir . '/unit/*/*Test.php');

        // functional tests
        $h->register_glob($h->base_dir . '/functional/*Test.php');
        $h->register_glob($h->base_dir . '/functional/*/*Test.php');

        $this->logSection($this->getFullName(), 'Running all tests');

        return $h->run();
    }

    protected function build($arguments, $options)
    {
        $packageXml = $this->environment->get('project_root_dir') . '/package.xml';

        $filesystem = $this->getFilesystem();

        if (is_file($packageXml)) {
            $filesystem->remove($packageXml);
        }

        $filesystem->copy(
            $this->environment->get('project_root_dir') . '/package.xml.tmpl',
            $this->environment->get('project_root_dir') . '/package.xml'
        );

        // add class files
        $finder = sfFinder::type('file')->ignoreVersionControl()->relative();

        $xml_classes = '';

        $dirs = array(
            'lib'  => 'php',
            'data' => 'data'
        );

        // skip files
        $skipFiles = array(
            'sift',
            'sift.bat',
            'build.php'
        );

        if ($options['exclude-ip-database']) {
            $skipFiles[] = 'ip2country.db';
        }

        // exclude directories
        $skipDirs = array(
            'data/bin/tasks'
        );

        foreach ($dirs as $dir => $role) {
            $class_files = $finder->in($dir);

            foreach ($class_files as $file) {
                foreach ($skipDirs as $skipDir) {
                    if (strpos($dir . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $file), $skipDir) !== false) {
                        continue 2;
                    }
                }

                // skip files
                if (in_array(basename($file), $skipFiles)) {
                    continue;
                }

                $xml_classes
                    .= '<file role="' . $role . '" baseinstalldir="Sift" install-as="' . $file . '" name="' . $dir . '/'
                    . $file . '" />' . "\n";
            }
        }

        // replace tokens
        $filesystem->replaceTokens(
            $this->environment->get('project_root_dir') . '/package.xml',
            '##',
            '##',
            array(
                'SIFT_VERSION' => $this->version,
                'CURRENT_DATE' => date('Y-m-d'),
                'CLASS_FILES'  => $xml_classes,
                'STABILITY'    => $this->stability,
            )
        );

        passthru('pear package');

        $filesystem->remove($this->environment->get('project_root_dir') . '/package.xml');

        if (is_file($this->environment->get('project_root_dir') . '/Sift-' . $this->version . '.tgz')) {
            $filesystem->rename(
                $this->environment->get('project_root_dir') . '/Sift-' . $this->version . '.tgz',
                $this->environment->get('project_root_dir') . '/dist/Sift-' . $this->version . '.tgz'
            );

            $filesystem->copy(
                $this->environment->get('project_root_dir') . '/dist/Sift-' . $this->version . '.tgz',
                $this->environment->get('project_root_dir') . '/dist/Sift-latest.tgz'
            );
        }
    }

}
