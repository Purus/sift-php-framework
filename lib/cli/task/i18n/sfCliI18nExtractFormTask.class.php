<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extracts i18n strings from php files.
 *
 * @package    Sift
 * @subpackage cli_task
 */
class sfCliI18nExtractFormTask extends sfCliBaseTask
{
    protected $formFiles = array();

    /**
     * @see sfCliTask
     */
    protected function configure()
    {
        $this->addArguments(
            array(
                new sfCliCommandArgument('form', sfCliCommandArgument::REQUIRED, 'The form class name'),
                new sfCliCommandArgument('culture', sfCliCommandArgument::REQUIRED, 'The target culture'),
            )
        );

        $this->addOptions(
            array(
                new sfCliCommandOption('display-new', null, sfCliCommandOption::PARAMETER_NONE, 'Output all new found strings'),
                new sfCliCommandOption('display-old', null, sfCliCommandOption::PARAMETER_NONE, 'Output all old strings'),
                new sfCliCommandOption('auto-save', null, sfCliCommandOption::PARAMETER_NONE, 'Save the new strings'),
                new sfCliCommandOption('auto-delete', null, sfCliCommandOption::PARAMETER_NONE, 'Delete old strings'),
                new sfCliCommandOption('connection', null, sfCliCommandOption::PARAMETER_OPTIONAL, 'Connection name', 'mock'),
            )
        );

        $this->namespace = 'i18n';
        $this->name = 'extract-form';
        $this->briefDescription = 'Extracts i18n strings from a form';

        $scriptName = $this->environment->get('script_name');

        $this->detailedDescription
            = <<<EOF
The [i18n:extract|INFO] task extracts i18n strings from a form:

  [{$scriptName} i18n:extract-form myForm cs_CZ|INFO]

By default, the task only displays the number of new and old strings
it found in the form.

If you want to display the new strings, use the [--display-new|COMMENT] option:

  [{$scriptName} i18n:extract-form --display-new myForm cs_CZ|INFO]

To save them in the i18n message catalogue, use the [--auto-save|COMMENT] option:

  [{$scriptName} i18n:extract-form --auto-save myForm cs_CZ|INFO]

If you want to display strings that are present in the i18n messages
catalogue but are not found in the application (or plugin), use the
[--display-old|COMMENT] option:

  [{$scriptName} i18n:extract-form --display-old myForm cs_CZ|INFO]

To automatically delete old strings, use the [--auto-delete|COMMENT]

  [{$scriptName} i18n:extract-form --auto-delete myForm cs_CZ|INFO]
EOF;
    }

    /**
     * @see sfCliTask
     */
    public function execute($arguments = array(), $options = array())
    {
        $form = $arguments['form'];

        // form name does not end with "Form"
        if (!preg_match('/Form$/i', $form)) {
            $form .= 'Form';
        }

        $this->getDatabase($options['connection']);
        $this->createContextInstance($this->getFirstApplication());

        $extract = new sfI18nFormExtract(array(
            'culture' => $arguments['culture'],
            'form'    => $form
        ));

        $extract->extract();

        $this->logSection(
            $this->getFullName(),
            sprintf('Found "%d" new i18n strings', $extract->getNewMessagesCount())
        );
        $this->logSection(
            $this->getFullName(),
            sprintf('Found "%d" old i18n strings', $extract->getOldMessagesCount())
        );

        if ($options['display-new']) {
            $this->logSection(
                $this->getFullName(),
                sprintf('Display new i18n strings', $extract->getNewMessagesCount())
            );
            foreach ($extract->getNewMessages() as $domain => $messages) {
                foreach ($messages as $message) {
                    $this->log('               ' . $message . "\n");
                }
            }
        }

        if ($options['auto-save']) {
            $this->logSection($this->getFullName(), 'Saving new i18n strings');

            $extract->saveNewMessages();
        }

        if ($options['display-old']) {
            $this->logSection(
                $this->getFullName(),
                sprintf('Display old i18n strings', $extract->getOldMessagesCount())
            );
            foreach ($extract->getOldMessages() as $domain => $messages) {
                foreach ($messages as $message) {
                    $this->log('               ' . $message . "\n");
                }
            }
        }

        if ($options['auto-delete']) {
            $this->logSection($this->getFullName(), 'Deleting old i18n strings');

            $extract->deleteOldMessages();
        }
    }

}
