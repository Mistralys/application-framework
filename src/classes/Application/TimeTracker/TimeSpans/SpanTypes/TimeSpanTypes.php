<?php

declare(strict_types=1);

namespace Application\TimeTracker\TimeSpans\SpanTypes;

use Application\TimeTracker\TimeSpans\SpanTypes\Type\HolidayTimeSpan;
use Application_Interfaces_Formable;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\Collections\BaseClassLoaderCollection;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * @method TimeSpanTypeInterface getByID(string $id)
 * @method TimeSpanTypeInterface[] getAll()
 * @method TimeSpanTypeInterface[] getDefault()
 */
class TimeSpanTypes extends BaseClassLoaderCollection
{
    private static ?TimeSpanTypes $instance = null;

    /**
     * @return TimeSpanTypes
     */
    public static function getInstance(): TimeSpanTypes
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param string $class
     * @return TimeSpanTypeInterface
     * @throws BaseClassHelperException
     */
    protected function createItemInstance(string $class): StringPrimaryRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            TimeSpanTypeInterface::class,
            new $class()
        );
    }

    /**
     * @return class-string<TimeSpanTypeInterface>
     */
    public function getInstanceOfClassName(): string
    {
        return TimeSpanTypeInterface::class;
    }

    public function isRecursive(): bool
    {
        return false;
    }

    public function getClassesFolder(): FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/Type');
    }

    public function getDefaultID(): string
    {
        return HolidayTimeSpan::TYPE_ID;
    }

    public function createSelector(Application_Interfaces_Formable $formable) : TimeSpanTypeSelector
    {
        return new TimeSpanTypeSelector($formable);
    }
}
