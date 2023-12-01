<?php
/**
 * @package Application
 * @subpackage News
 * @see \Application\NewsCentral\NewsCollection
 */

declare(strict_types=1);

namespace Application\NewsCentral;

use Application;
use Application\Admin\Area\BaseNewsScreen;
use Application\Admin\Area\News\BaseCreateAlertScreen;
use Application\Admin\Area\News\BaseCreateArticleScreen;
use Application\Admin\Area\News\BaseNewsListScreen;
use Application\Admin\Area\News\BaseReadNewsScreen;
use Application\AppFactory;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application_Admin_ScreenInterface;
use Application_Formable;
use Application_User;
use AppUtils\BaseException;
use AppUtils\Microtime;
use DBHelper;
use DBHelper_BaseCollection;
use DBHelper_StatementBuilder;
use DBHelper_StatementBuilder_ValuesContainer;
use NewsCentral\Entries\NewsAlert;
use NewsCentral\Entries\NewsArticle;

/**
 * NOTE: Article classes are determined via {@see self::resolveRecordClass()}.
 *
 * @package Application
 * @subpackage News
 *
 * @method array<int,NewsArticle|NewsAlert>[] getAll()
 * @method NewsFilterCriteria getFilterCriteria()
 * @method NewsFilterSettings getFilterSettings()
 * @method NewsArticle|NewsAlert getByID(int $record_id)
 * @method NewsArticle|NewsAlert|NULL getByRequest()
 * @method NewsArticle|NewsAlert createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 */
class NewsCollection extends DBHelper_BaseCollection
{
    public const PRIMARY_NAME = 'news_id';
    public const TABLE_NAME = 'app_news';
    public const TABLE_NAME_ENTRY_CATEGORIES = 'app_news_entry_categories';

    public const COL_LABEL = 'label';
    public const COL_ARTICLE = 'article';
    public const COL_STATUS = 'status';
    public const COL_AUTHOR = 'author';
    public const COL_LOCALE = 'locale';
    public const COL_VIEWS = 'views';
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
            self::COL_SYNOPSIS => t('Synopsis'),
            self::COL_ARTICLE => t('Article text')
        );
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY_NAME;
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

    private ?CategoriesCollection $categoriesCollection = null;

    public function createCategories() : CategoriesCollection
    {
        if(!isset($this->categoriesCollection)) {
            $this->categoriesCollection = new CategoriesCollection();
        }

        return $this->categoriesCollection;
    }

    protected function resolveRecordClass(int $record_id): string
    {
        $query = <<<'EOT'
SELECT
    {news_type}
FROM 
    {table_news}
WHERE
    {news_primary}=:primary
EOT;

        $type = DBHelper::fetchKey(
            self::COL_NEWS_TYPE,
            self::statementBuilder($query),
            array(
                'primary' => $record_id
            )
        );

        if($type === NewsEntryTypes::NEWS_TYPE_ALERT) {
            return NewsAlert::class;
        }

        return NewsArticle::class;
    }

    public static function statementBuilder(string $template) : DBHelper_StatementBuilder
    {
        return statementBuilder($template, self::statementValues());
    }

    public static function statementValues() : DBHelper_StatementBuilder_ValuesContainer
    {
        return statementValues()
            ->table('{table_news}', self::TABLE_NAME)
            ->table('{table_entry_categories}', self::TABLE_NAME_ENTRY_CATEGORIES)

            ->field('{news_primary}', self::PRIMARY_NAME)
            ->field('{news_type}', self::COL_NEWS_TYPE)
            ->field('{categories_primary}', CategoriesCollection::PRIMARY_NAME)
            ->field('{date_scheduled_from}', self::COL_SCHEDULED_FROM_DATE)
            ->field('{date_scheduled_to}', self::COL_SCHEDULED_TO_DATE);
    }

    /**
     * Creates a new article as a draft.
     *
     * @param string $label
     * @param string $locale
     * @param string $synopsis
     * @param string $article
     * @param Application_User|null $user
     * @return NewsArticle
     * @throws BaseException
     */
    public function createNewArticle(string $label, string $locale, string $synopsis, string $article, ?Application_User $user=null) : NewsArticle
    {
        if($user === null) {
            $user = Application::getUser();
        }

        return $this->createNewRecord(array(
            self::COL_NEWS_TYPE => NewsEntryTypes::NEWS_TYPE_ARTICLE,
            self::COL_LABEL => $label,
            self::COL_AUTHOR => $user->getID(),
            self::COL_LOCALE => $locale,
            self::COL_SYNOPSIS => $synopsis,
            self::COL_ARTICLE => $article,
            self::COL_STATUS => NewsEntryStatuses::STATUS_DRAFT
        ));
    }

    public function createNewAlert(string $label, string $locale, string $message, NewsEntryCriticality $criticality, bool $requiresReceipt, ?Application_User $user=null) : NewsAlert
    {
        if($user === null) {
            $user = Application::getUser();
        }

        return $this->createNewRecord(array(
            self::COL_NEWS_TYPE => NewsEntryTypes::NEWS_TYPE_ALERT,
            self::COL_LABEL => $label,
            self::COL_AUTHOR => $user->getID(),
            self::COL_LOCALE => $locale,
            self::COL_SYNOPSIS => $message,
            self::COL_STATUS => NewsEntryStatuses::STATUS_DRAFT,
            self::COL_CRITICALITY => $criticality->getID(),
            self::COL_REQUIRES_RECEIPT => bool2string($requiresReceipt, true)
        ));
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_LABEL)->makeRequired();
        $this->keys->register(self::COL_SYNOPSIS)->setDefault('');
        $this->keys->register(self::COL_ARTICLE)->setDefault('');
        $this->keys->register(self::COL_AUTHOR)->makeRequired();
        $this->keys->register(self::COL_NEWS_TYPE)->makeRequired();
        $this->keys->register(self::COL_LOCALE)->makeRequired();

        $this->keys->register(self::COL_STATUS)
            ->setDefault(NewsEntryStatuses::DEFAULT_STATUS)
            ->makeRequired();

        $this->keys->register(self::COL_DATE_CREATED)
            ->makeRequired()
            ->setGenerator(function () : string {
                return Microtime::createNow()->getMySQLDate();
            });

        $this->keys->register(self::COL_DATE_MODIFIED)
            ->makeRequired()
            ->setGenerator(function () {
                return Microtime::createNow()->getMySQLDate();
            });

        $this->keys->register(self::COL_CRITICALITY);

        $this->keys->register(self::COL_REQUIRES_RECEIPT)
            ->setDefault('no')
            ->makeRequired();
    }

    public function createSettingsManager(Application_Formable $formable, ?NewsEntry $newsEntry) : NewsSettingsManager
    {
        return new NewsSettingsManager($formable, $this, $newsEntry);
    }

    // region: Admin URLs

    public function getAdminListURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseNewsListScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminCreateArticleURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseCreateArticleScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminCreateAlertURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseCreateAlertScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE] = BaseNewsScreen::URL_NAME;

        return AppFactory::createRequest()->buildURL($params);
    }

    public function getLiveReadURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseReadNewsScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    // endregion
}
