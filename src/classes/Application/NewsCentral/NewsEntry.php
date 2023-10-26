<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use Application\Admin\Area\News\BaseViewArticleScreen;
use Application\Admin\Area\News\ViewArticle\BaseArticleSettingsScreen;
use Application\Admin\Area\News\ViewArticle\BaseArticleStatusScreen;
use Application\AppFactory;
use Application_Admin_ScreenInterface;
use Application_User;
use Application_Users_User;
use DateTime;
use DBHelper_BaseRecord;
use NewsCentral\NewsEntryStatus;
use NewsCentral\NewsEntryType;

/**
 * @property NewsCollection $collection
 */
class NewsEntry extends DBHelper_BaseRecord
{
    public function publish() : self
    {
        $this->setStatus(NewsEntryStatuses::getInstance()->getPublished());
        $this->save();

        return $this;
    }

    public function setStatus(NewsEntryStatus $status) : bool
    {
        return $this->setRecordKey(NewsCollection::COL_STATUS, $status->getID());
    }

    public function getStatusID() : string
    {
        return $this->getRecordStringKey(NewsCollection::COL_STATUS);
    }

    public function getStatus() : NewsEntryStatus
    {
        return NewsEntryStatuses::getInstance()->getByID($this->getStatusID());
    }

    public function isPublished() : bool
    {
        return $this->getStatus()->isPublished();
    }

    /**
     * @var string[]
     */
    private array $dateUpdateKeys = array(
        NewsCollection::COL_SYNOPSIS,
        NewsCollection::COL_LABEL,
        NewsCollection::COL_ARTICLE,
        NewsCollection::COL_STATUS
    );

    public function isAlert() : bool
    {
        return $this->getTypeID() === NewsEntryTypes::NEWS_TYPE_ALERT;
    }

    public function setRequiresReceipt(bool $required) : bool
    {
        return $this->setRecordBooleanKey(NewsCollection::COL_REQUIRES_RECEIPT, $required);
    }

    public function setLabel(string $label) : bool
    {
        return $this->setRecordKey(NewsCollection::COL_LABEL, $label);
    }

    public function setSynopsis(string $synopsis) : bool
    {
        return $this->setRecordKey(NewsCollection::COL_SYNOPSIS, $synopsis);
    }

    public function setArticle(string $article) : bool
    {
        return $this->setRecordKey(NewsCollection::COL_ARTICLE, $article);
    }

    public function setCriticality(NewsEntryCriticality $criticality) : bool
    {
        return $this->setRecordKey(NewsCollection::COL_CRITICALITY, $criticality->getID());
    }

    protected function init() : void
    {
        foreach($this->dateUpdateKeys as $key) {
            $this->registerRecordKey($key, '', true);
        }
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : bool
    {
        // Update the date modified on change
        if(in_array($name, $this->dateUpdateKeys, true)) {
            $this->setRecordDateKey(NewsCollection::COL_DATE_MODIFIED, new DateTime());
        }

        return true;
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
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = BaseArticleStatusScreen::URL_NAME;

        return $this->getAdminViewURL($params);
    }

    public function getAdminSettingsURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = BaseArticleSettingsScreen::URL_NAME;

        return $this->getAdminViewURL($params);
    }

    public function getAdminPublishURL(array $params=array()) : string
    {
        $params[BaseArticleStatusScreen::REQUEST_PARAM_PUBLISH] = 'yes';

        return $this->getAdminStatusURL($params);
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[NewsCollection::PRIMARY] = $this->getID();
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseViewArticleScreen::URL_NAME;

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
