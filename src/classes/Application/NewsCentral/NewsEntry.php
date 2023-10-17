<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use Application\Admin\Area\Devel\News\BaseViewArticleScreen;
use Application\Admin\Area\Devel\News\ViewArticle\BaseArticleStatusScreen;
use Application\AppFactory;
use Application_Admin_ScreenInterface;
use Application_User;
use Application_Users_User;
use DateTime;
use DBHelper_BaseRecord;
use NewsCentral\NewsEntryType;
use TestDriver\Area\Devel\NewsScreen\ViewArticleScreen;
use function AppUtils\restoreThrowable;

/**
 * @property NewsCollection $collection
 */
class NewsEntry extends DBHelper_BaseRecord
{
    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    public function getLabel(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_LABEL);
    }

    public function getLabelLinked() : string
    {
        return (string)sb()
            ->linkRight(
                $this->getLabel(),
                $this->getAdminViewURL(),
                Application_User::RIGHT_VIEW_NEWS
            );
    }

    public function getAdminViewURL(array $params=array()) : string
    {
        return $this->getAdminURL($params);
    }

    public function getAdminStatusURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_ACTION] = BaseArticleStatusScreen::URL_NAME;

        return $this->getAdminViewURL($params);
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[NewsCollection::PRIMARY] = $this->getID();
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = BaseViewArticleScreen::URL_NAME;

        return $this->collection->getAdminURL($params);
    }

    public function getAuthorID() : int
    {
        return $this->getRecordIntKey(NewsCollection::COL_AUTHOR);
    }

    public function getAuthor() : Application_Users_User
    {
        return AppFactory::createUsers()->getByID($this->getAuthorID());
    }

    public function getTypeID() : string
    {
        return $this->getRecordStringKey(NewsCollection::COL_NEWS_TYPE);
    }

    public function getType() : NewsEntryType
    {
        return NewsEntryTypes::getInstance()->getByID($this->getTypeID());
    }

    public function getSynopsis(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_SYNOPSIS);
    }

    public function getArticle(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_ARTICLE);
    }

    public function getCriticalityID(): string
    {
        return $this->getRecordStringKey(NewsCollection::COL_CRITICALITY);
    }

    public function getScheduledFromDate(): ?DateTime
    {
        return $this->getRecordDateKey(NewsCollection::COL_SCHEDULED_FROM_DATE);
    }

    public function getScheduledToDate(): ?DateTime
    {
        return $this->getRecordDateKey(NewsCollection::COL_SCHEDULED_TO_DATE);
    }

    public function isReceiptRequired() : bool
    {
        return $this->getRecordBooleanKey(NewsCollection::COL_REQUIRES_RECEIPT);
    }

    public function getDateCreated(): DateTime
    {
        return $this->getRecordDateKey(NewsCollection::COL_DATE_CREATED);
    }

    public function getDateModified(): DateTime
    {
        return $this->getRecordDateKey(NewsCollection::COL_DATE_MODIFIED);
    }
}
