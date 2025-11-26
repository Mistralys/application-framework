<?php

declare(strict_types=1);

namespace Application\Renamer\Admin\Screens\Submode;

use Application\AppFactory;
use Application\Development\DevScreenRights;
use Application\Renamer\RenamerSettingsManager;
use Application\Renamer\RenamingManager;
use Application\Traits\AllowableMigrationTrait;
use Application_Admin_Area_Mode_Submode;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\Microtime;
use Maileditor\Renamer\RenamerConfig;
use UI;
use UI_Themes_Theme_ContentRenderer;

abstract class BaseConfigurationSubmode extends Application_Admin_Area_Mode_Submode
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'configuration';
    public const string CONFIG_VAR_NAME = 'renamer_configuration';
    public const string REQUEST_PARAM_RESET = 'reset';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_RENAMER_CONFIGURATION;
    }

    public function getNavigationTitle(): string
    {
        return t('Configuration');
    }

    public function getTitle(): string
    {
        return t('Configuration');
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    public static function clearConfig() : void
    {
        AppFactory::createDriver()->getSettings()->delete(self::CONFIG_VAR_NAME);
    }

    protected function _handleActions(): bool
    {
        $manager = RenamingManager::getInstance();
        $settings = $manager->createSettingsForm($this);

        if($this->request->getBool(self::REQUEST_PARAM_RESET)) {
            self::clearConfig();
        }

        // Redirect to results if settings already exist
        $existing = self::getConfig();
        if($existing !== null) {
            $this->redirectTo($manager->adminURL()->results());
        }

        $settings->inject();

        $this->addFormablePageVars();

        if($this->isFormValid()) {
            $values = $this->getFormValues();
            $this->handleCreateConfig($values);
        }

        return true;
    }

    private function handleCreateConfig(array $formValues) : void
    {
        $manager = RenamingManager::getInstance();
        $storage = AppFactory::createDriver()->getSettings();

        $config = array(
            RenamerConfig::KEY_DATE => Microtime::createNow()->getISODate(true),
            RenamerConfig::KEY_SEARCH => $formValues[RenamerSettingsManager::SETTING_SEARCH],
            RenamerConfig::KEY_COLUMN_IDS => $formValues[RenamerSettingsManager::SETTING_COLUMNS],
            RenamerConfig::KEY_CASE_SENSITIVE => $formValues[RenamerSettingsManager::SETTING_CASE_SENSITIVE] === 'true',
        );

        $storage->set(self::CONFIG_VAR_NAME, JSONConverter::var2json($config, JSON_PRETTY_PRINT));
        $this->redirectTo($manager->adminURL()->search());
    }

    public static function getConfig() : ?RenamerConfig
    {
        $configValue = AppFactory::createDriver()->getSettings()->get(self::CONFIG_VAR_NAME);

        if(!empty($configValue)) {
            return new RenamerConfig(ArrayDataCollection::create(JSONConverter::json2var($configValue)));
        }

        return null;
    }

    public static function requireConfig() : RenamerConfig
    {
        $config = self::getConfig();

        if($config !== null) {
            return $config;
        }

        AppFactory::createDriver()->redirectWithInfoMessage(
            t('Please configure the search and replace settings first.'),
            RenamingManager::getInstance()->adminURL()->configuration()
        );
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setAbstract(t('Please choose what to search for, and in which database columns to search.'));
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('search', t('Run search'))
            ->makePrimary()
            ->setIcon(UI::icon()->search())
            ->makeClickableSubmit($this);

        $this->sidebar->addInfoMessage(
            t('The search is likely to take a long time, as all columns use a full table scan.'),
            true
        )
            ->makeSlimLayout();
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }
}
