<?php
/**
 * @package DBHelper
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace DBHelper\Admin;

use Application\Admin\Screens\Events\BeforeContentRenderedEvent;
use Application\Admin\Screens\Events\BreadcrumbHandledEvent;
use Application\Interfaces\Admin\AdminScreenInterface;
use DBHelper_BaseRecord;
use UI;
use UI\AdminURLs\AdminURL;
use UI\AdminURLs\AdminURLException;
use UI\AdminURLs\AdminURLInterface;
use UI_Bootstrap_BigSelection_Item_Regular;
use UI_Page_Breadcrumb;

/**
 * Tie-in class for selecting a DB record from a short list
 * in an administration screen.
 *
 * This takes over control of the target screen's content
 * rendering and action handling to display a list of records
 * to select when none is present in the request.
 *
 * ## Usage
 *
 * 1. Extend this class.
 * 2. Instantiate the class in the screen's {@see \Application_Traits_Admin_Screen::_handleBeforeActions()} method.
 * 3. Call {@see self::getRecord()} or {@see self::requireItem()} to retrieve the selected record.
 *
 * ## Limitations
 *
 * The selection is made to work with a small range of records.
 * It does not have any pagination or search functionality.
 * It uses the "Big Selection" UI widget to display the records.
 *
 * @package DBHelper
 * @subpackage Admin Screens
 */
abstract class BaseRecordSelectionTieIn implements RecordSelectionTieInInterface
{
    private AdminScreenInterface $screen;
    private AdminURLInterface $baseURL;
    private ?DBHelper_BaseRecord $record = null;
    private bool $recordFetched = false;
    private UI $ui;

    /**
     * @param AdminScreenInterface $screen
     * @param AdminURLInterface|null $baseURL The base URL for all record links. The record ID will be automatically injected into this (replacing existing IDs). If not specified, the URL of the screen will be used, as returned by {@see AdminScreenInterface::getURL()}.
     * @throws AdminURLException
     */
    public function __construct(AdminScreenInterface $screen, ?AdminURLInterface $baseURL=null)
    {
        if($baseURL === null) {
            $baseURL = AdminURL::create()->importURL($screen->getURL());
        }

        $this->screen = $screen;
        $this->baseURL = $baseURL;
        $this->ui = $this->screen->getUI();

        $this->screen->onBeforeContentRendered(function (BeforeContentRenderedEvent $event) : void {
            $this->renderContent($event);
        });

        $this->screen->onBreadcrumbHandled(function (BreadcrumbHandledEvent $event) : void {
            $this->handleBreadcrumb($event->getBreadcrumb());
        });
    }

    final public function getScreen(): AdminScreenInterface
    {
        return $this->screen;
    }

    final public function isRecordSelected(): bool
    {
        return $this->getRecord() !== null;
    }

    final public function getRecord() : ?DBHelper_BaseRecord
    {
        if($this->recordFetched) {
            return $this->record;
        }

        $this->recordFetched = true;
        $this->record = $this->getCollection()->getByRequest();

        return $this->record;
    }

    final public function requireItem() : object
    {
        $item = $this->getRecord();
        if($item !== null) {
            return $item;
        }

        $collection = $this->getCollection();

        throw new DBHelperAdminException(
            'No record available in the request.',
            sprintf(
                'No record of type [%s] is available for the current request. '.PHP_EOL.
                'The ID is expected to be given in the request parameter [%s].',
                $collection->getRecordTypeName(),
                $collection->getRecordRequestPrimaryName()
            ),
            DBHelperAdminException::ERROR_NO_RECORD_IN_REQUEST
        );
    }

    final public function getURL() : AdminURLInterface
    {
        $tenant = $this->getRecord();
        if(isset($tenant)) {
            return $this->getURLRecord($tenant);
        }

        return clone $this->baseURL;
    }

    final public function getURLRecord(DBHelper_BaseRecord $item) : AdminURLInterface
    {
        return (clone $this->baseURL)
            ->int(
                $this->getCollection()->getRecordRequestPrimaryName(),
                $item->getID()
            );
    }

    private function handleBreadcrumb(UI_Page_Breadcrumb $breadcrumb): void
    {
        $record = $this->getRecord();

        if($record === null) {
            return;
        }

        $breadcrumb
            ->appendItem($record->getLabel())
            ->makeLinked($this->getURL());
    }

    private function renderContent(BeforeContentRenderedEvent $event) : void
    {
        $tenant = $this->getRecord();

        if($tenant !== null) {
            return;
        }

        $event->replaceScreenContentWith($this->renderRecordSelection());
    }

    private function renderRecordSelection() : string
    {
        $sel = $this->ui
            ->createBigSelection()
            ->enableFiltering();

        $records = $this->getSelectableRecords();
        if(empty($records)) {
            return $this->renderNoRecordsAvailable();
        }

        // Automatically switch to the more compact variant
        // depending on the number of records available.
        if(count($records) > RecordSelectionTieInInterface::COMPACT_LIST_THRESHOLD) {
            $sel->makeSmall();
        }

        foreach($records as $record) {
            $sel->addLink(
                $record->getLabel(),
                $this->getURLRecord($record)
            );
        }

        return (string)$this->screen
            ->getRenderer()
            ->appendContent($sel)
            ->makeWithoutSidebar();
    }

    /**
     * Gives the possibility to adjust the record entry.
     *
     * @param UI_Bootstrap_BigSelection_Item_Regular $entry
     * @param DBHelper_BaseRecord $record
     * @return void
     */
    abstract protected function adjustEntry(UI_Bootstrap_BigSelection_Item_Regular $entry, DBHelper_BaseRecord $record) : void;

    private function renderNoRecordsAvailable() : string
    {
        return (string)$this->screen
            ->getRenderer()
            ->appendContent(
                $this->ui->createMessage($this->resolveEmptySelectionMessage())
                    ->enableIcon()
                    ->makeNotDismissable()
                    ->makeInfo()
            );
    }

    private function resolveEmptySelectionMessage() : string
    {
        $message = sb()->add($this->getEmptySelectionText());

        if($this->isSelectionRightsBased()) {
            $message->note()->add($this->getRequiredRightsText());
        }

        return (string)$message;
    }

    /**
     * Gets the text to display when no records are available.
     * @return string
     * @overridable
     */
    protected function getEmptySelectionText() : string
    {
        return t('No %1$s records are available.', $this->getCollection()->getRecordLabel());
    }

    /**
     * Gets the text to display when rights are required to view the records.
     * @return string
     * @overridable
     */
    protected function getRequiredRightsText() : string
    {
        return t('You may not have the necessary rights to view the records.');
    }
}
