<?php

declare(strict_types=1);

namespace Application\NewsCentral;

class NewsEntryStatuses
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public const DEFAULT_STATUS = self::STATUS_DRAFT;
}
