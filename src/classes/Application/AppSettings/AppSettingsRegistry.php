<?php

declare(strict_types=1);

namespace Application\AppSettings;

use Application\AppFactory;
use Application\AppSettings\Admin\Screens\AppSettingsDevelMode;
use Application\OfflineEvents\RegisterAppSettingsEvent;
use Application_Driver;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\FileHelper\FolderInfo;
use UI_MarkupEditorInfo;

/**
 * Registry for application settings definitions.
 *
 * @package Application
 * @subpackage AppSettings
 *
 * @method AppSettingDef[] getAll()
 */
class AppSettingsRegistry extends BaseStringPrimaryCollection
{
    private static ?AppSettingsRegistry $instance = null;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getAdminScreensFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__ . '/Admin/Screens')->requireExists();
    }

    public function getDefaultID(): string
    {
        return $this->getAutoDefault();
    }

    public function getByID(string $id): AppSettingDef
    {
        return ClassHelper::requireObjectInstanceOf(
            AppSettingDef::class,
            parent::getByID($id)
        );
    }

    protected function registerItems(): void
    {
        $this->registerItem(new AppSettingDef(
            UI_MarkupEditorInfo::SETTING_NAME_MARKUP_EDITOR_ID,
            AppSettingsDevelMode::SETTING_TYPE_STRING,
            t('The ID of the markup editor to use.')
        ));

        $this->registerItem(new AppSettingDef(
            Application_Driver::APP_SETTING_KEEP_ALIVE_INTERVAL,
            AppSettingsDevelMode::SETTING_TYPE_STRING,
            (string)sb()
                ->t('The interval in which the authentication keep-alive process is run.')
                ->t(
                    'Accepts a duration parseable by PHP\'s %1$s function, e.g. %2$s.',
                    sb()->link('strtotime', 'https://www.php.net/manual/en/function.strtotime.php', true),
                    sb()->code('20 seconds')
                )
        ));

        // Allow the application to register any custom settings
        AppFactory::createOfflineEvents()->triggerEvent(
                RegisterAppSettingsEvent::EVENT_NAME,
                array($this),
                RegisterAppSettingsEvent::class
            );
    }

    public function addSetting(string $name, string $type, string $description): void
    {
        $this->registerItem(new AppSettingDef(
            $name,
            $type,
            $description
        ));
    }
}
