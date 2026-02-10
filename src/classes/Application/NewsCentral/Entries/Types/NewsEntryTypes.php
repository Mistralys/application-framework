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
    public const string NEWS_TYPE_ALERT = 'alert';
    public const string NEWS_TYPE_ARTICLE = 'article';
    public const string DEFAULT_TYPE = self::NEWS_TYPE_ARTICLE;

    private static ?NewsEntryTypes $instance = null;

    public static function getInstance() : NewsEntryTypes
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getTypeArticle() : NewsEntryType
    {
        return $this->getByID(self::NEWS_TYPE_ARTICLE);
    }

    public function getTypeAlert() : NewsEntryType
    {
        return $this->getByID(self::NEWS_TYPE_ALERT);
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
