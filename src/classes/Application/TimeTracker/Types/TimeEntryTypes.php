<?php

declare(strict_types=1);

namespace Application\TimeTracker\Types;

use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * @method TimeEntryType getByID(string $id)
 * @method TimeEntryType[] getAll()
 * @method TimeEntryType getDefault()
 */
class TimeEntryTypes extends BaseStringPrimaryCollection
{
    public const TYPE_MEETING = 'meeting';
    public const TYPE_DEVELOPMENT = 'development';
    public const TYPE_MANAGEMENT = 'management';
    public const TYPE_ABSENCE_GENERAL = 'absence';
    public const TYPE_ABSENCE_HEALTH = 'health';
    public const DEFAULT_TYPE = self::TYPE_MANAGEMENT;

    private static ?TimeEntryTypes $instance = null;

    public static function getInstance(): TimeEntryTypes
    {
        if (!isset(self::$instance)) {
            self::$instance = new TimeEntryTypes();
        }

        return self::$instance;
    }

    public function getDefaultID(): string
    {
        return self::DEFAULT_TYPE;
    }

    protected function sortItems(StringPrimaryRecordInterface $a, StringPrimaryRecordInterface $b): int
    {
        return 0; // We want to keep the order as it is
    }

    protected function registerItems(): void
    {
        $this->registerItem(new TimeEntryType(self::TYPE_DEVELOPMENT, t('Development')));
        $this->registerItem(new TimeEntryType(self::TYPE_MEETING, t('Meeting')));
        $this->registerItem(new TimeEntryType(self::TYPE_MANAGEMENT, t('Management')));
        $this->registerItem(new TimeEntryType(self::TYPE_ABSENCE_GENERAL, (string)sb()->t('Absence:')->t('General')));
        $this->registerItem(new TimeEntryType(self::TYPE_ABSENCE_HEALTH, (string)sb()->t('Absence:')->t('Health-related')));
    }
}
