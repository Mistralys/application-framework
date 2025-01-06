<?php
/**
 * @package Application
 * @subpackage Collections
 */

declare(strict_types=1);

namespace Application\Collection\Admin;

use Application\Admin\Screens\Events\BeforeContentRenderedEvent;
use Application\Admin\Screens\Events\BreadcrumbHandledEvent;
use Application\AppFactory;
use Application\Collection\CollectionException;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application_CollectionItemInterface;
use DBHelper\Admin\BaseDBRecordSelectionTieIn;
use DBHelper\Admin\DBHelperAdminException;
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
 * 3. Call {@see self::getRecord()} or {@see self::requireRecord()} to retrieve the selected record.
 *
 * ## DBHelper variant
 *
 * If the records you want to select are from a DBHelper collection,
 * you can use the {@see BaseDBRecordSelectionTieIn} class instead.
 *
 * ## Limitations
 *
 * The selection uses the "Big Selection" UI widget to display
 * the records. It does not have any pagination or search functionality,
 * so it should not be used for overly large collections.
 *
 * @package Application
 * @subpackage Collections
 */
abstract class BaseRecordSelectionTieIn implements RecordSelectionTieInInterface
{
    private AdminScreenInterface $screen;
    private AdminURLInterface $baseURL;
    private ?Application_CollectionItemInterface $record = null;
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

        $this->init();
    }

    /**
     * @return void
     * @overridable Optional initialization method.
     */
    protected function init() : void
    {
    }

    final public function getScreen(): AdminScreenInterface
    {
        return $this->screen;
    }

    final public function isRecordSelected(): bool
    {
        return $this->getRecord() !== null;
    }

    final public function getRecord() : ?Application_CollectionItemInterface
    {
        if($this->recordFetched) {
            return $this->record;
        }

        $this->recordFetched = true;

        $id = AppFactory::createRequest()->registerParam($this->getRequestPrimaryVarName())->getInt();
        if($id > 0 && $this->recordIDExists($id)) {
            $this->record = $this->getRecordByID($id);
        }

        return $this->record;
    }

    abstract protected function recordIDExists(int $id) : bool;

    abstract protected function getRecordByID(int $id) : Application_CollectionItemInterface;

    /**
     * @return Application_CollectionItemInterface
     * @throws CollectionException
     */
    final public function requireRecord() : Application_CollectionItemInterface
    {
        $record = $this->getRecord();
        if($record !== null) {
            return $record;
        }

        throw new CollectionException(
            'No record available in the request.',
            sprintf(
                'No record is available for the current request. '.PHP_EOL.
                'The ID is expected to be given in the request parameter [%s].',
                $this->getRequestPrimaryVarName()
            ),
            DBHelperAdminException::ERROR_NO_RECORD_IN_REQUEST
        );
    }

    final public function getURL() : AdminURLInterface
    {
        $record = $this->getRecord();
        if(isset($record)) {
            return $this->getURLRecord($record);
        }

        return clone $this->baseURL;
    }

    final public function getURLRecord(Application_CollectionItemInterface $record) : AdminURLInterface
    {
        return (clone $this->baseURL)
            ->int(
                $this->getRequestPrimaryVarName(),
                $record->getID()
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

        $abstract = $this->getAbstract();
        if(!empty($abstract)) {
            $this->screen->getRenderer()->setAbstract($abstract);
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
            $entry = $sel->addLink(
                $record->getLabel(),
                $this->getURLRecord($record)
            );

            $this->adjustEntry($entry, $record);
        }

        return (string)$this->screen
            ->getRenderer()
            ->appendContent($sel)
            ->makeWithoutSidebar();
    }

    /**
     * Gives the possibility to adjust the record entry
     * before it is rendered in the list.
     *
     * @param UI_Bootstrap_BigSelection_Item_Regular $entry
     * @param Application_CollectionItemInterface $record
     * @return void
     */
    abstract protected function adjustEntry(UI_Bootstrap_BigSelection_Item_Regular $entry, Application_CollectionItemInterface $record) : void;

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
