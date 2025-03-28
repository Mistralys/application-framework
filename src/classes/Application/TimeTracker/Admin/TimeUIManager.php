<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin;

use Application\AppFactory;
use AppUtils\Microtime;
use UI\AdminURLs\AdminURLInterface;

class TimeUIManager
{
    public const LIST_SCREEN_GLOBAL = 'global';
    public const LIST_SCREEN_DAY = 'day';
    public const SETTING_LAST_USED_LIST = 'time_tracker_last_used_list';
    public const SETTING_LAST_USED_DATE = 'last_used_date';

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

    public static function getBackToListURL() : AdminURLInterface
    {
        if(self::getLastUsedList() === self::LIST_SCREEN_DAY) {
            return AppFactory::createTimeTracker()->adminURL()->dayList();
        }

        return AppFactory::createTimeTracker()->adminURL()->list();
    }
}
