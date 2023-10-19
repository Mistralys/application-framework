<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use AppUtils\Collections\BaseStringPrimaryCollection;
use NewsCentral\NewsEntryStatus;
use UI;

/**
 * @method NewsEntryStatus getByID(string $id)
 * @method NewsEntryStatus getDefault()(string $label)
 * @method NewsEntryStatus[] getAll()
 */
class NewsEntryStatuses extends BaseStringPrimaryCollection
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public const DEFAULT_STATUS = self::STATUS_DRAFT;

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if(self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getDefaultID(): string
    {
        return self::DEFAULT_STATUS;
    }

    public function getPublished() : NewsEntryStatus
    {
        return $this->getByID(self::STATUS_PUBLISHED);
    }

    public function getDraft() : NewsEntryStatus
    {
        return $this->getByID(self::STATUS_DRAFT);
    }

    protected function registerItems(): void
    {
        $this->registerItem(new NewsEntryStatus(
            self::STATUS_DRAFT,
            t('Draft'),
            UI::label('')->makeWarning(),
            UI::icon()->draft()->makeWarning()
        ));

        $this->registerItem(new NewsEntryStatus(
            self::STATUS_PUBLISHED,
            t('Published'),
            UI::label('')->makeSuccess(),
            UI::icon()->published()->makeSuccess()
        ));
    }
}
