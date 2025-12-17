<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

namespace Application\Environments\Admin\Screens;

use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\AppFactory;
use Application\ConfigSettings\BaseConfigRegistry;
use Application\Development\Admin\DevScreenRights;
use Application\OfflineEvents\DisplayAppConfigEvent;
use AppUtils\ConvertHelper;
use AppUtils\Interfaces\StringableInterface;
use UI_PropertiesGrid;
use UI_PropertiesGrid_Property;
use UI_PropertiesGrid_Property_Boolean;
use UI_PropertiesGrid_Property_Regular;
use UI_Themes_Theme_ContentRenderer;

/**
 * Screen that displays all current application configuration settings.
 *
 * ## Adding custom app settings
 *
 * 1. Create an offline event listener in the folder {@see DisplayAppConfigEvent::EVENT_NAME}.
 * 2. Extend the base listener class {@see \Application\Environments\Events\BaseDisplayAppConfigListener}.
 * 3. Implement the abstract methods to add your own settings to the screen.
 *
 * @package Application
 * @subpackage Administration
 */
class AppConfigMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'appconfig';

    private UI_PropertiesGrid $grid;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_APP_CONFIG;
    }

    public function getNavigationTitle(): string
    {
        return t('Configuration');
    }

    public function getTitle(): string
    {
        return t('Application configuration');
    }

    public function getDevCategory(): string
    {
        return t('Settings');
    }

    protected function _handleActions(): bool
    {
        $this->grid = $this->ui->createPropertiesGrid();

        return true;
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendContent($this->renderSettingsList())
            ->makeWithoutSidebar();
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->getURL());
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->setTitle($this->getTitle())
            ->setAbstract(sb()
                ->t('This shows the essential application configuration settings, as defined by the environment that was detected.')
                ->note()
                ->t('These settings are immutable at runtime.')
            );
    }

    private function renderSettingsList() : string
    {
        $this->addEnvironment();
        $this->addDatabase();
        $this->addCAS();
        $this->addLDAP();
        $this->addDeepL();
        $this->addPreferences();
        $this->addSettings();
        $this->addLocalization();

        // Let the application add its own settings as needed
        AppFactory::createOfflineEvents()->triggerEvent(
            DisplayAppConfigEvent::EVENT_NAME,
            array($this),
            DisplayAppConfigEvent::class
        );

        return (string)$this->grid;
    }

    private function addPreferences() : void
    {
        $this->addHeader(t('Preferences'));

        $this->addConstantBool(t('Simulate session?'), BaseConfigRegistry::SIMULATE_SESSION)->makeEnabledDisabled();
        $this->addConstantBool(t('Minify JavaScript?'), BaseConfigRegistry::JAVASCRIPT_MINIFIED)->makeEnabledDisabled();
        $this->addConstantBool(t('Demo mode?'), BaseConfigRegistry::DEMO_MODE)->makeEnabledDisabled();
        $this->addConstantBool(t('Show queries?'), BaseConfigRegistry::SHOW_QUERIES)->makeEnabledDisabled();
        $this->addConstantBool(t('Track queries?'), BaseConfigRegistry::TRACK_QUERIES)->makeEnabledDisabled();
    }

    private function addSettings() : void
    {
        $this->addHeader(t('Settings'));

        $this->addConstant(t('Company name'), BaseConfigRegistry::COMPANY_NAME);
        $this->addConstant(t('Company homepage'), BaseConfigRegistry::COMPANY_HOMEPAGE);
        $this->addConstant(t('Stub email'), BaseConfigRegistry::DUMMY_EMAIL);
        $this->addConstant(t('System email'), BaseConfigRegistry::SYSTEM_EMAIL);
        $this->addConstant(t('System name'), BaseConfigRegistry::SYSTEM_NAME);
        $this->addConstant(t('System email recipients'), BaseConfigRegistry::SYSTEM_EMAIL_RECIPIENTS);
        $this->addConstant(t('Class name'), BaseConfigRegistry::CLASS_NAME);
    }

    private function addEnvironment() : void
    {
        $this->addHeader(t('Environment'));

        $this->addConstant(t('Name'), BaseConfigRegistry::ENVIRONMENT);
        $this->addConstant(t('Instance ID'), BaseConfigRegistry::INSTANCE_ID);
        $this->addConstant(t('Appset'), BaseConfigRegistry::APPSET);
        $this->addConstant(t('Install URL'), BaseConfigRegistry::URL);
        $this->addConstant(t('Install folder'), BaseConfigRegistry::ROOT);
        $this->addConstant(t('Framework folder'), BaseConfigRegistry::INSTALL_FOLDER);
        $this->addConstant(t('Vendor folder'), BaseConfigRegistry::VENDOR_PATH);
        $this->addConstant(t('Vendor URL'), BaseConfigRegistry::VENDOR_URL);
    }

    private function addDatabase() : void
    {
        $this->addHeader(t('Database'));

        $this->addConstant(t('Host'), BaseConfigRegistry::DB_HOST);
        $this->addConstant(t('Name'), BaseConfigRegistry::DB_NAME);
        $this->addConstant(t('User'), BaseConfigRegistry::DB_USER);
        $this->addConstant(t('Port'), BaseConfigRegistry::DB_PORT);
    }

    private function addCAS() : void
    {
        $this->addHeader(t('CAS'));

        $this->addConstant(t('Host'), BaseConfigRegistry::CAS_SERVER_URI);
        $this->addConstant(t('Port'), BaseConfigRegistry::CAS_PORT);
        $this->addConstant(t('Logout URL'), BaseConfigRegistry::CAS_LOGOUT_URL);
        $this->addConstant(t('Service Name'), BaseConfigRegistry::CAS_NAME);
    }

    private function addLDAP() : void
    {
        $this->addHeader(t('LDAP'));

        $this->addConstant(t('Host'), BaseConfigRegistry::LDAP_HOST);
        $this->addConstant(t('Port'), BaseConfigRegistry::LDAP_PORT);
        $this->addConstantBool(t('SSL enabled?'), BaseConfigRegistry::LDAP_SSL_ENABLED)->makeEnabledDisabled();
        $this->addConstant(t('User'), BaseConfigRegistry::LDAP_USERNAME);
        $this->addConstant(t('Base DN'), BaseConfigRegistry::LDAP_DN);
        $this->addConstant(t('Member suffix'), BaseConfigRegistry::LDAP_MEMBER_SUFFIX);
    }

    private function addDeepL() : void
    {
        $this->addHeader(t('DeepL'));

        $this->addConstant(t('API Key'), BaseConfigRegistry::DEEPL_API_KEY);
        $this->addConstantBool(t('Proxy enabled?'), BaseConfigRegistry::DEEPL_PROXY_ENABLED)->makeEnabledDisabled();
        $this->addConstant(t('Proxy host'), BaseConfigRegistry::DEEPL_PROXY_URL);
    }

    private function addLocalization() : void
    {
        $this->addHeader(t('Localization'));

        $this->addConstant(t('UI locales'), BaseConfigRegistry::UI_LOCALES);
        $this->addConstant(t('Content locales'), BaseConfigRegistry::CONTENT_LOCALES);
    }

    // region: Add helpers

    public function getGrid() : UI_PropertiesGrid
    {
        return $this->grid;
    }

    public function addValue(string $label, string|int|float|bool|StringableInterface $value) : UI_PropertiesGrid_Property
    {
        return $this->grid->add($label, $value);
    }

    public function addConstant(string $label, string $name) : UI_PropertiesGrid_Property_Regular
    {
        return $this->grid->add($label, boot_constant($name));
    }

    public function addConstantBool(string $label, string $name) : UI_PropertiesGrid_Property_Boolean
    {
        return $this->grid->addBoolean($label, boot_constant($name) ?? false);
    }

    /**
     * @param string|StringableInterface $label
     * @param string|null $anchorID
     * @return void
     */
    public function addHeader(string|StringableInterface $label, ?string $anchorID=null) : void
    {
        $label = toString($label);

        if(empty($anchorID)) {
            $anchorID = ConvertHelper::transliterate($label);
        }

        $this->grid->addHeader(sprintf(
            '<a id="%s"></a>%s',
            $anchorID,
            $label
        ));
    }

    // endregion
}
