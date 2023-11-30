<?php

declare(strict_types=1);

namespace Application\NewsCentral;

use Application\Admin\Area\News\BaseViewArticleScreen;
use Application\Admin\Area\News\ViewArticle\BaseArticleSettingsScreen;
use Application\Admin\Area\News\ViewArticle\BaseArticleStatusScreen;
use Application\AppFactory;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application\NewsCentral\Categories\Category;
use Application_Admin_ScreenInterface;
use Application_User;
use Application_Users_User;
use AppLocalize\Localization;
use AppLocalize\Localization_Locale;
use DateTime;
use DBHelper;
use DBHelper_BaseRecord;
use League\CommonMark\CommonMarkConverter;
use NewsCentral\Entries\EntryCategoriesManager;
use NewsCentral\NewsEntryStatus;
use NewsCentral\NewsEntryType;
use UI;
use function AppUtils\valBoolTrue;

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

    public function setLabel(string $label) : bool
    {
        return $this->setRecordKey(NewsCollection::COL_LABEL, $label);
    }

    public function getViews() : int
    {
        return $this->getRecordIntKey(NewsCollection::COL_VIEWS);
    }

    public function getLocaleID() : string
    {
        return $this->getRecordStringKey(NewsCollection::COL_LOCALE);
    }

    public function getLocale() : Localization_Locale
    {
        return Localization::getContentLocaleByName($this->getLocaleID());
    }

    private ?EntryCategoriesManager $categoriesManager = null;

    public function getCategoriesManager() : EntryCategoriesManager
    {
        if(!isset($this->categoriesManager)) {
            $this->categoriesManager = new EntryCategoriesManager($this);
        }

        return $this->categoriesManager;
    }

    public function getSchedulingBadge() : ?\UI_Badge
    {
        if(!$this->hasScheduling()) {
            return null;
        }

        return UI::label(t('Scheduled'))
            ->setIcon(UI::icon()->time())
            ->setTooltip(t('This news entry has scheduling enabled.'))
            ->makeInactive();
    }

    public function hasScheduling() : bool
    {
        return $this->getScheduledFromDate() !== null || $this->getScheduledToDate() !== null;
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
        $params[NewsCollection::PRIMARY_NAME] = $this->getID();
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


    public function getScheduledFromDate(): ?DateTime
    {
        return $this->getRecordDateKey(NewsCollection::COL_SCHEDULED_FROM_DATE);
    }

    public function getScheduledToDate(): ?DateTime
    {
        return $this->getRecordDateKey(NewsCollection::COL_SCHEDULED_TO_DATE);
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
