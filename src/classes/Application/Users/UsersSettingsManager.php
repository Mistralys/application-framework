<?php

declare(strict_types=1);

namespace Application\Users;

use Application\AppFactory;
use Application_Formable;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_Setting;
use Application_Formable_RecordSettings_ValueSet;
use Application_Users;
use Application_Users_User;
use DBHelper\Interfaces\DBHelperRecordInterface;
use HTML_QuickForm2_Node;
use UI;
use UI\CSSClasses;

class UsersSettingsManager extends Application_Formable_RecordSettings_Extended
{
    public const string SETTING_FIRSTNAME = 'firstname';
    public const string SETTING_LASTNAME = 'lastname';
    public const string SETTING_EMAIL = 'email';
    public const string SETTING_FOREIGN_ID = 'foreign_id';
    public const string SETTING_FOREIGN_NICKNAME = 'foreign_nickname';
    public const string SETTING_NICKNAME = 'nickname';

    public function __construct(Application_Formable $formable, ?Application_Users_User $record = null)
    {
        parent::__construct($formable, AppFactory::createUsers(), $record);

        $this->setDefaultsUseStorageNames(true);
    }

    protected function processPostCreateSettings(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function getCreateData(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function updateRecord(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function registerSettings(): void
    {
        $group = $this->addGroup(t('User details'))
            ->expand()
            ->setIcon(UI::icon()->user());

        $group->registerSetting(self::SETTING_FIRSTNAME)
            ->setStorageName(Application_Users::COL_FIRSTNAME)
            ->makeRequired()
            ->setCallback($this->inject_firstname(...));

        $group->registerSetting(self::SETTING_LASTNAME)
            ->setStorageName(Application_Users::COL_LASTNAME)
            ->makeRequired()
            ->setCallback($this->inject_lastname(...));

        $group->registerSetting(self::SETTING_NICKNAME)
            ->setStorageName(Application_Users::COL_NICKNAME)
            ->setCallback($this->inject_nickname(...));

        $group->registerSetting(self::SETTING_EMAIL)
            ->setStorageName(Application_Users::COL_EMAIL)
            ->makeRequired()
            ->setCallback($this->inject_email(...));

        $group = $this->addGroup(t('Foreign identity'))
            ->setAbstract(t(
                'Details on how the system that handles the user\'s rights (%1$s) identifies the user.',
                t('for example, a company SSO')
            ))
            ->setIcon(UI::icon()->options());

        $group->registerSetting(self::SETTING_FOREIGN_ID)
            ->setStorageName(Application_Users::COL_FOREIGN_ID)
            ->setCallback($this->inject_foreign_id(...));

        $group->registerSetting(self::SETTING_FOREIGN_NICKNAME)
            ->setStorageName(Application_Users::COL_FOREIGN_NICKNAME)
            ->setCallback($this->inject_foreign_nickname(...));
    }

    private function inject_nickname(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('Nickname'));

        $el->setComment(t(
            'If specified, the nickname will be used instead of the first and lastname wherever the user name is displayed in the %1$s interface.',
            AppFactory::createDriver()->getAppNameShort()
        ));

        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->addFilterTrim();

        $this->makeLengthLimited($el, 0, Application_Users::COL_NICKNAME_MAX_LENGTH);

        return $el;
    }

    private function inject_foreign_nickname(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('Foreign Nickname'));

        $el->setComment(sb()
            ->t('If the foreign system has a nickname for the user, specify it here.')
            ->t('Some integrations may need this value.')
            ->t('When in doubt, check with your %1$s administrator.', AppFactory::createDriver()->getAppNameShort())
        );

        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->addFilterTrim();

        $this->makeLengthLimited($el, 0, Application_Users::COL_FOREIGN_NICKNAME_MAX_LENGTH);

        return $el;
    }


    private function inject_foreign_id(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('Foreign ID'));
        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->addFilterTrim();

        $this->makeLengthLimited($el, 0, Application_Users::COL_FOREIGN_ID_MAX_LENGTH);

        return $el;
    }

    private function inject_email(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('Email Address'));
        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->addFilterTrim();

        $this->makeLengthLimited($el, 0, 500);
        $this->addRuleEmail($el);

        return $el;
    }

    private function inject_lastname(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('Last Name'));
        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->addFilterTrim();

        $this->makeLengthLimited($el, 0, 160);

        return $el;
    }

    private function inject_firstname(Application_Formable_RecordSettings_Setting $setting) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($setting->getName(), t('First Name'));
        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->addFilterTrim();

        $this->makeLengthLimited($el, 0, 160);

        return $el;
    }

    public function getDefaultSettingName(): string
    {
        return self::SETTING_FIRSTNAME;
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->canEditUsers();
    }
}
