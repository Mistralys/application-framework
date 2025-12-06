<?php

declare(strict_types=1);

namespace Application\TimeTracker\TimeSpans\SpanTypes\Type;

use Application\TimeTracker\TimeSpans\SpanTypes\BaseTimeSpanType;

class SickLeaveTimeSpan extends BaseTimeSpanType
{
    public const TYPE_ID = 'sick_leave';

    public function getID(): string
    {
        return self::TYPE_ID;
    }

    public function getLabel(): string
    {
        return t('Sick Leave');
    }
}

