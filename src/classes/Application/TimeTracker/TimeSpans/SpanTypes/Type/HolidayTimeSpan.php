<?php

declare(strict_types=1);

namespace Application\TimeTracker\TimeSpans\SpanTypes\Type;

use Application\TimeTracker\TimeSpans\SpanTypes\BaseTimeSpanType;

class HolidayTimeSpan extends BaseTimeSpanType
{
    public const string TYPE_ID = 'holiday';

    public function getID(): string
    {
        return self::TYPE_ID;
    }

    public function getLabel(): string
    {
        return t('Holiday');
    }
}
