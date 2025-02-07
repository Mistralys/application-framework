<?php

declare(strict_types=1);

namespace Application\TimeTracker\Types;

use AppUtils\Collections\BaseStringPrimaryCollection;
use Application\TimeTracker\Types\TimeEntryType;

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

    protected function registerItems(): void
    {
        $this->registerItem(new TimeEntryType(self::TYPE_MEETING, t('Meeting')));
        $this->registerItem(new TimeEntryType(self::TYPE_MANAGEMENT, t('Management')));
        $this->registerItem(new TimeEntryType(self::TYPE_DEVELOPMENT, t('Development')));
    }
}
