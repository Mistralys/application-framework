<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use Application\Admin\Area\Devel\BaseNewsScreen;
use Application\Admin\Area\Devel\News\BaseCreateAlertScreen;
use Application\Admin\Area\Devel\News\BaseCreateArticleScreen;
use Application\Admin\Area\Devel\News\BaseNewsListScreen;
use Application\AppFactory;
use Application_Admin_Area_Devel;
use Application_Admin_ScreenInterface;
use Application_Formable;
use AppUtils\Microtime;
use DBHelper_BaseCollection;

class NewsCollection extends DBHelper_BaseCollection
{
    public const PRIMARY = 'news_id';
    public const TABLE_NAME = 'app_news';
    public const NEWS_TYPE_ARTICLE = 'article';
    public const NEWS_TYPE_ALERT = 'alert';

    public const COL_LABEL = 'label';
    public const COL_ARTICLE = 'article';
    public const COL_STATUS = 'status';
    public const COL_AUTHOR = 'author';
    public const COL_DATE_CREATED = 'date_created';
    public const COL_DATE_MODIFIED = 'date_modified';
    public const COL_SYNOPSIS = 'synopsis';
    public const COL_CRITICALITY = 'criticality';
    public const COL_SCHEDULED_FROM_DATE = 'scheduled_from_date';
    public const COL_SCHEDULED_TO_DATE = 'scheduled_to_date';
    public const COL_REQUIRES_RECEIPT = 'requires_receipt';
    public const COL_NEWS_TYPE = 'news_type';

    public function getRecordClassName(): string
    {
        return NewsEntry::class;
    }

    public function getRecordFiltersClassName(): string
    {
        return NewsFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return NewsFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_LABEL;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_LABEL => t('Title'),
            self::COL_ARTICLE => t('Article text')
        );
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY;
    }

    public function getRecordTypeName(): string
    {
        return 'news-entry';
    }

    public function getCollectionLabel(): string
    {
        return t('News articles');
    }

    public function getRecordLabel(): string
    {
        return t('News article');
    }

    public function getRecordProperties(): array
    {
        return array();
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_LABEL)->makeRequired();
        $this->keys->register(self::COL_SYNOPSIS)->setDefault('');
        $this->keys->register(self::COL_ARTICLE)->setDefault('');
        $this->keys->register(self::COL_AUTHOR)->makeRequired();
        $this->keys->register(self::COL_NEWS_TYPE)->makeRequired();

        $this->keys->register(self::COL_STATUS)
            ->setDefault(NewsEntryStatuses::DEFAULT_STATUS)
            ->makeRequired();

        $this->keys->register(self::COL_DATE_CREATED)
            ->makeRequired();

        $this->keys->register(self::COL_DATE_MODIFIED)
            ->makeRequired()
            ->setGenerator(function () {
                return Microtime::createNow()->getMySQLDate();
            });

        $this->keys->register(self::COL_CRITICALITY);

        $this->keys->register(self::COL_REQUIRES_RECEIPT)
            ->makeRequired();
    }

    public function createSettingsManager(Application_Formable $formable, ?NewsEntry $newsEntry) : NewsSettingsManager
    {
        return new NewsSettingsManager($formable, $this, $newsEntry);
    }

    // region: Admin URLs

    public function getAdminListURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = BaseNewsListScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminCreateArticleURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = BaseCreateArticleScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminCreateAlertURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = BaseCreateAlertScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE] = Application_Admin_Area_Devel::URL_NAME;
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseNewsScreen::URL_NAME;

        return AppFactory::createRequest()->buildURL($params);
    }

    // endregion
}
