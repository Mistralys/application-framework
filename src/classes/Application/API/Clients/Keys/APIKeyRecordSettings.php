<?php
/**
 * @package API
 * @subpackage API Keys
 */

declare(strict_types=1);

namespace Application\API\Clients\Keys;

use Application\API\Admin\APIScreenRights;
use Application\API\Clients\APIClientRecord;
use Application_Formable;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_Setting;
use Application_Formable_RecordSettings_ValueSet;
use DBHelper_BaseRecord;
use HTML_QuickForm2_Node;
use UI;
use UI\CSSClasses;

/**
 * @package API
 * @subpackage API Keys
 */
class APIKeyRecordSettings extends Application_Formable_RecordSettings_Extended
{
    public const string SETTING_LABEL = 'label';
    public const string SETTING_EXPIRY_DELAY = 'expiry_delay';
    public const string SETTING_EXPIRY_DATE = 'expiry_date';

    public function __construct(Application_Formable $formable, APIClientRecord $client, ?APIKeyRecord $record = null)
    {
        parent::__construct($formable, $client->createAPIKeys(), $record);

        $this->setDefaultsUseStorageNames(true);
    }

    protected function processPostCreateSettings(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
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
        $group = $this->addGroup(t('Settings'))
            ->expand()
            ->setIcon(UI::icon()->settings());

        $group->registerSetting(self::SETTING_LABEL)
            ->setStorageName(APIKeysCollection::COL_LABEL)
            ->makeRequired()
            ->setCallback($this->injectLabel(...));

        $group->registerSetting('comments')
            ->setStorageName(APIKeysCollection::COL_COMMENTS)
            ->setCallback($this->injectComments(...));

        $group = $this->addGroup(t('Options'))
            ->setIcon(UI::icon()->options());

        $group->registerSetting('grant_all')
            ->setDefaultValue('no')
            ->setStorageName(APIKeysCollection::COL_GRANT_ALL_METHODS)
            ->setCallback($this->injectGrantAllMethod(...));

        $group = $this->addGroup(t('Automatic expiry'))
            ->setAbstract(sb()
                ->t('This allows you to configure a time or delay after which the API key will automatically expire.')
                ->note()
                ->t('If both are specified, the expiry date will be used instead of the delay.')
            )
            ->setIcon(UI::icon()->time());

        $group->registerSetting(self::SETTING_EXPIRY_DATE)
            ->setStorageName(APIKeysCollection::COL_EXPIRY_DATE)
            ->setCallback($this->injectExpiryDate(...));

        $group->registerSetting(self::SETTING_EXPIRY_DELAY)
            ->setStorageName(APIKeysCollection::COL_EXPIRY_DELAY)
            ->setCallback($this->injectExpiryDelay(...));
    }

    private function injectGrantAllMethod(Application_Formable_RecordSettings_Setting $key) : HTML_QuickForm2_Node
    {
        $el = $this->addElementSwitch($key->getName(), t('Grant all?'));
        $el->setComment(sb()
            ->t('If enabled, this API key will always have access to all available API methods.')
            ->nl()
            ->noteBold()
            ->t('When new APIs are added to the system, this key will automatically have access to them as well.')
        );

        return $el;
    }

    private function injectComments(Application_Formable_RecordSettings_Setting $key) : HTML_QuickForm2_Node
    {
        $el = $this->addElementTextarea($key->getName(), t('Comments'));
        $el->setRows(3);
        $el->addClass(CSSClasses::INPUT_XXLARGE);
        $el->setComment(sb()
            ->t('Optional comments and/or documentation for the API key.')
            ->nl()
        );

        $this->addMarkdownSupport($el);
        $this->makeLengthLimited($el, 0, 20000);
        $this->addRuleNoHTML($el);

        return $el;
    }

    private function injectExpiryDate(Application_Formable_RecordSettings_Setting $key) : HTML_QuickForm2_Node
    {
        $el = $this->addElementDatepicker($key->getName(), t('Expiry Date'));
        $el->setTimeOptional(false);
        $el->setComment(sb()
            ->t('If set, the API key will automatically expire on the specified date and time.')
        );

        return $el;
    }

    private function injectExpiryDelay(Application_Formable_RecordSettings_Setting $key) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($key->getName(), t('Expiry Delay'));
        $el->setComment(sb()
            ->t('If set, the API key will automatically expire after the specified delay from the time of its creation.')
            ->nl()
            ->t('Specify the expiration delay in an english readable format.')
            ->nl()
            ->t('Examples:')
            ->ul(array(
                '2 days',
                '3 weeks',
                '1 year and 6 months',
            ))
        );

        return $el;
    }

    private function injectLabel(Application_Formable_RecordSettings_Setting $key) : HTML_QuickForm2_Node
    {
        $el = $this->addElementText($key->getName(), t('Label'));
        $el->addClass(CSSClasses::INPUT_XXLARGE);

        $this->makeLengthLimited($el, 2, 180);
        $this->addRuleNameOrTitle($el);

        return $el;
    }

    public function getDefaultSettingName(): string
    {
        return self::SETTING_LABEL;
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->can(APIScreenRights::SCREEN_API_KEYS_CREATE);
    }
}
