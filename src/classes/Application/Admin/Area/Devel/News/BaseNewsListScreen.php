<?php

declare(strict_types=1);

namespace Application\Admin\Area\Devel\News;

use Application\AppFactory;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsEntry;
use Application_Admin_Area_Mode_Submode_CollectionList;
use Application_User;
use AppUtils\ClassHelper;
use DBHelper_BaseCollection;
use DBHelper_BaseFilterCriteria_Record;
use DBHelper_BaseRecord;
use UI;

/**
 * @property NewsCollection $collection
 */
abstract class BaseNewsListScreen extends Application_Admin_Area_Mode_Submode_CollectionList
{
    public const URL_NAME = self::URL_NAME_DEFAULT;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function isUserAllowed(): bool
    {
        return $this->user->canViewNews();
    }

    /**
     * @return NewsCollection
     */
    protected function createCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createNews();
    }

    protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry) : array
    {
        $newsEntry = ClassHelper::requireObjectInstanceOf(NewsEntry::class, $record);

        return [
            'label' => $newsEntry->getLabel(),
            'author' => $newsEntry->getAuthor()->getName(),
            'synopsis' => $newsEntry->getSynopsis(),
            'criticality' => $newsEntry->getCriticalityID(),
            'visible_from_date' => $newsEntry->getScheduledFromDate(),
            'visible_to_date' => $newsEntry->getScheduledToDate(),
            'dismissable' => $newsEntry->isReceiptRequired(),
        ];
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn('label', t('Title'));
    }

    protected function configureActions(): void
    {
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create-article', t('Create article...'))
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->collection->getAdminCreateArticleURL())
            ->requireRight(Application_User::RIGHT_CREATE_NEWS);

        $this->sidebar->addButton('create-alert', t('Create news alert...'))
            ->setIcon(UI::icon()->add())
            ->makeLinked($this->collection->getAdminCreateAlertURL())
            ->requireRight(Application_User::RIGHT_CREATE_NEWS_ALERTS);


        parent::_handleSidebar();
    }

    public function getBackOrCancelURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function getNavigationTitle(): string
    {
        return t('Articles');
    }

    public function getTitle(): string
    {
        return t('Available news articles');
    }
}
