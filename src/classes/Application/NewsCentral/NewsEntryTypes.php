<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use AppUtils\Collections\BaseStringPrimaryCollection;
use NewsCentral\NewsEntryType;
use UI;

/**
 * @method NewsEntryType getByID(string $id)
 * @method NewsEntryType[] getAll()()
 * @method NewsEntryType getDefault()
 */
class NewsEntryTypes extends BaseStringPrimaryCollection
{
    public const NEWS_TYPE_ALERT = 'alert';
    public const NEWS_TYPE_ARTICLE = 'article';
    public const DEFAULT_TYPE = self::NEWS_TYPE_ARTICLE;

    private static ?NewsEntryTypes $instance = null;

    public static function getInstance() : NewsEntryTypes
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getDefaultID(): string
    {
        return self::DEFAULT_TYPE;
    }

    protected function registerItems(): void
    {
        $this->registerItem(new NewsEntryType(
            self::NEWS_TYPE_ARTICLE,
            t('News article'),
            UI::icon()->news()
        ));

        $this->registerItem(new NewsEntryType(
            self::NEWS_TYPE_ALERT,
            t('News alert'),
            UI::icon()->warning()->makeWarning()
        ));
    }
}
