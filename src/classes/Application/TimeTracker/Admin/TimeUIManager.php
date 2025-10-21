<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\AppFactory;
use Application\TimeTracker\Admin\Screens\BaseAutoFillScreen;
use AppUtils\ArrayDataCollection;
use AppUtils\Microtime;
use UI\AdminURLs\AdminURLInterface;

class TimeUIManager
{
    public const LIST_SCREEN_GLOBAL = 'global';
    public const LIST_SCREEN_DAY = 'day';
    public const SETTING_PREFIX = 'time_tracker_';
    public const SETTING_LAST_USED_LIST = self::SETTING_PREFIX.'last_used_list';
    public const SETTING_LAST_USED_DATE = self::SETTING_PREFIX.'last_used_date';
    public const SETTING_BASE_TICKET_URL = self::SETTING_PREFIX.'base_ticket_url';
    public const string SETTING_AUTOFILL_PREFERENCES = self::SETTING_PREFIX . 'autofill_prefs';

    public static function setLastUsedList(string $listType) : void
    {
        AppFactory::createDriver()->getSettings()->set(self::SETTING_LAST_USED_LIST, $listType);
    }

    public static function getLastUsedList() : string
    {
        $value = AppFactory::createDriver()->getSettings()->get(self::SETTING_LAST_USED_LIST);
        if(!empty($value)) {
            return $value;
        }

        return self::LIST_SCREEN_GLOBAL;
    }

    public static function getLastUsedDate() : Microtime
    {
        $stored = AppFactory::createDriver()->getSettings()->get(self::SETTING_LAST_USED_DATE);
        if(!empty($stored)) {
            return Microtime::createFromString($stored);
        }

        return Microtime::createNow();
    }

    public static function setLastUsedDate(Microtime $date) : void
    {
        AppFactory::createDriver()->getSettings()->set(self::SETTING_LAST_USED_DATE, $date->getISODate());
    }

    public static function getAutoFillPreferences() : ArrayDataCollection
    {
        $prefs = AppFactory::createDriver()->getSettings()->getArray(self::SETTING_AUTOFILL_PREFERENCES);
        $defaults = BaseAutoFillScreen::getDefaultPreferences();

        foreach($defaults as $key => $value) {
            if(!array_key_exists($key, $prefs)) {
                $prefs[$key] = $value;
            }
        }

        return new ArrayDataCollection($prefs);
    }

    public static function setAutoFillPreferences(ArrayDataCollection $prefs) : void
    {
        AppFactory::createDriver()->getSettings()->setArray(self::SETTING_AUTOFILL_PREFERENCES, $prefs->getData());
    }

    public static function getBackToListURL() : AdminURLInterface
    {
        if(self::getLastUsedList() === self::LIST_SCREEN_DAY) {
            return AppFactory::createTimeTracker()->adminURL()->dayList();
        }

        return AppFactory::createTimeTracker()->adminURL()->list();
    }

    public static function setBaseTicketURL(string $url) : void
    {
        AppFactory::createDriver()->getSettings()->set(self::SETTING_BASE_TICKET_URL, $url);
    }

    private static ?string $baseTicketURL = null;

    public static function getBaseTicketURL() : string
    {
        if(!isset(self::$baseTicketURL)) {
            self::$baseTicketURL = AppFactory::createDriver()->getSettings()->get(self::SETTING_BASE_TICKET_URL);
        }

        return self::$baseTicketURL;
    }
}
