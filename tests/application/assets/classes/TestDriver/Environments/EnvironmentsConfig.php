<?php

declare(strict_types=1);

namespace application\assets\classes\TestDriver\Environments;

use Application\ConfigSettings\BaseConfigRegistry;
use Application\Environments\BaseEnvironmentsConfig;
use Application\Environments\Environment;
use Application\SourceFolders\SourceFoldersManager;
use AppLocalize\Localization\Locale\de_DE;
use AppLocalize\Localization\Locale\en_GB;
use TestDriver\CustomConfigRegistry;
use TestDriver\Environments\LocalEnvironment;
use const TESTS_SYSTEM_EMAIL_RECIPIENTS;

class EnvironmentsConfig extends BaseEnvironmentsConfig
{
    protected function getClassName(): string
    {
        return 'TestDriver';
    }

    protected function getCompanyName(): string
    {
        return 'Mistralys';
    }

    protected function getDummyEmail(): string
    {
        return 'someone@app-framework.ui';
    }

    protected function getSystemEmail(): string
    {
        return 'system@app-framework.ui';
    }

    protected function getSystemName(): string
    {
        return 'Application Framework';
    }

    protected function getSystemEmailRecipients(): string
    {
        return TESTS_SYSTEM_EMAIL_RECIPIENTS;
    }

    protected function getContentLocales(): array
    {
        return array(
            de_DE::LOCALE_NAME,
            en_GB::LOCALE_NAME
        );
    }

    protected function getUILocales(): array
    {
        return array(
            de_DE::LOCALE_NAME,
            en_GB::LOCALE_NAME
        );
    }

    protected function createCustomSettings(): BaseConfigRegistry
    {
        return new CustomConfigRegistry();
    }

    protected function configureDefaultSettings(Environment $environment): void
    {
        $this->config
            ->setURL(TESTS_BASE_URL.'/tests/application')
            ->setVendorURL(TESTS_BASE_URL.'/vendor')
            ->setInstanceID('')
            ->setSimulateSession(false)
            ->setLoggingEnabled(true)
            ->setJavascriptMinified(false)
            ->setShowQueries(true)
            ->setTrackQueries(true)
            ->setAuthSalt('dummy_salt')

            ->setDBHost(TESTSUITE_DB_HOST)
            ->setDBName(TESTSUITE_DB_NAME)
            ->setDBUser(TESTSUITE_DB_USER)
            ->setDBPassword(TESTSUITE_DB_PASSWORD)
            ->setDBPort(TESTSUITE_DB_PORT ?? 3306)

            ->setDBTestsHost(TESTSUITE_DB_HOST)
            ->setDBTestsName(TESTSUITE_DB_NAME)
            ->setDBTestsUser(TESTSUITE_DB_USER)
            ->setDBTestsPassword(TESTSUITE_DB_PASSWORD)
            ->setDBTestsPort(TESTSUITE_DB_PORT ?? 3306)

            ->setRequestLogPassword('unit-tests');
    }

    protected function _registerClassSourceFolders(SourceFoldersManager $manager): void
    {
        $externalFolder = __DIR__.'/../ExternalSources';

        $manager->choose()->API()->addFolder($externalFolder.'/API');
        $manager->choose()->AJAX()->addFolder($externalFolder.'/AJAX');
        $manager->choose()->deploymentTasks()->addFolder($externalFolder.'/DeploymentTasks');
    }

    public function getDefaultEnvironmentID(): string
    {
        return LocalEnvironment::ENVIRONMENT_ID;
    }

    protected function getEnvironmentClasses(): array
    {
        return array(
            LocalEnvironment::class
        );
    }

    protected function getRequiredSettingNames(): array
    {
        return array();
    }
}
