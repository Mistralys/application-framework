<?php

declare(strict_types=1);

namespace Application\TimeTracker\TimeSpans\SpanTypes;

use AppUtils\Interfaces\StringPrimaryRecordInterface;

interface TimeSpanTypeInterface extends StringPrimaryRecordInterface
{
    public function getLabel(): string;
}
