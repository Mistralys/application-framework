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
use Application\Collection\CollectionItemInterface;
use Application\Interfaces\HiddenVariablesInterface;
use AppUtils\Interfaces\StringableInterface;
use Closure;
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
 * ## Chaining tie-ins
 *
 * To make tie-ins interdependent, so that later tie-ins are only enabled
 * when a record is selected in the parent tie-in, you can pass the parent tie-in
 * to the constructor {@see self::__construct()}. The child tie-in will then
 * automatically be enabled once the parent tie-in has a record selected.
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
    private ?CollectionItemInterface $record = null;
    private bool $recordFetched = false;
    private UI $ui;
    private ?Closure $enabledCallback = null;
    private ?RecordSelectionTieInInterface $parent;

    /**
     * @var string[]
     */
    private array $inheritRequestVars = array();

    /**
     * @param AdminScreenInterface $screen
     * @param AdminURLInterface|null $baseURL The base URL for all record links. The record ID will be automatically injected into this (replacing existing IDs). If not specified, the URL of the screen will be used, as returned by {@see AdminScreenInterface::getURL()}.
     * @param RecordSelectionTieInInterface|null $parent The parent tie-in to inherit the enabled state from (this tie-in will be enabled once the parent tie-in has a record selected).
     * @throws AdminURLException
     */
    public function __construct(AdminScreenInterface $screen, ?AdminURLInterface $baseURL=null, ?RecordSelectionTieInInterface $parent=null)
    {
        if($baseURL === null) {
            $baseURL = AdminURL::create()->importURL($screen->getURL());
        }

        $this->screen = $screen;
        $this->baseURL = $baseURL;
        $this->parent = $parent;
        $this->ui = $this->screen->getUI();

        $this->init();

        $this->screen->onBeforeContentRendered(function (BeforeContentRenderedEvent $event) : void {
            $this->renderContent($event);
        });

        $this->screen->onBreadcrumbHandled(function (BreadcrumbHandledEvent $event) : void {
            $this->handleBreadcrumb($event->getBreadcrumb());
        });

        // Ensure that we will pass through all relevant request variables in the URL
        if(isset($this->parent)) {
            foreach($this->getAncestry() as $tieIn) {
                $this->inheritRequestVar($tieIn->getRequestPrimaryVarName());
            }
        }
    }

    /**
     * @return void
     * @overridable Optional initialization method.
     */
    protected function init() : void
    {
    }

    /**
     * @param CollectionItemInterface $record
     * @return array<string,string|int|StringableInterface>
     */
    protected function getRecordRequestVars(CollectionItemInterface $record) : array
    {
        return array($this->getRequestPrimaryVarName(), $record->getID());
    }

    /**
     * @param HiddenVariablesInterface $subject
     * @return $this
     */
    public function injectHiddenVars(HiddenVariablesInterface $subject): self
    {
        $record = $this->getRecord();

        if($record !== null) {
            $subject->addHiddenVars($this->getRecordRequestVars($record));
            $subject->addHiddenVars($this->_getHiddenVars());
        }

        return $this;
    }

    /**
     * @return array<string,string>
     */
    final public function getHiddenVars() : array
    {
        $vars = array_merge(
            $this->_getHiddenVars(),
            array(
                $this->getRequestPrimaryVarName() => $this->getRecordID()
            )
        );

        $result = array();
        foreach($vars as $name => $value) {
            $value = (string)$value;

            if($value !== '') {
                $result[$name] = $value;
            }
        }

        ksort($result);

        return $result;
    }

    protected function _getHiddenVars(): array
    {
        return array();
    }

    public function getParent() : ?RecordSelectionTieInInterface
    {
        return $this->parent;
    }

    /**
     * Gets the ancestry of this tie-in, starting with the topmost
     * parent.
     *
     * @return RecordSelectionTieInInterface[]
     */
    public function getAncestry() : array
    {
        $ancestry = array();

        $parent = $this->parent;

        while($parent !== null) {
            $ancestry[] = $parent;
            $parent = $parent->getParent();
        }

        return array_reverse($ancestry);
    }

    final public function getScreen(): AdminScreenInterface
    {
        return $this->screen;
    }

    final public function isRecordSelected(): bool
    {
        return $this->getRecord() !== null;
    }

    final public function getRecord() : ?CollectionItemInterface
    {
        if($this->recordFetched) {
            return $this->record;
        }

        $this->recordFetched = true;

        $id = $this->getRecordID();
        if(!empty($id)) {
            $this->record = $this->getRecordByID($id);
        }

        return $this->record;
    }

    /**
     * @return string|int|NULL
     */
    public function getRecordID()
    {
        $id = AppFactory::createRequest()->getParam($this->getRequestPrimaryVarName());
        if(!empty($id) && $this->recordIDExists($id)) {
            return $id;
        }

        return null;
    }

    public function getEnabledCallback() : ?Closure
    {
        return $this->enabledCallback;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function setEnabledCallback(?Closure $callback): self
    {
        $this->enabledCallback = $callback;
        return $this;
    }

    /**
     * @param string|int $id
     * @return bool
     */
    abstract protected function recordIDExists($id) : bool;

    /**
     * @param string|int $id
     * @return CollectionItemInterface
     */
    abstract protected function getRecordByID($id) : CollectionItemInterface;

    /**
     * @return CollectionItemInterface
     * @throws CollectionException
     */
    final public function requireRecord() : CollectionItemInterface
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

        return $this->adjustURL(clone $this->baseURL);
    }

    final public function getURLRecord(CollectionItemInterface $record) : AdminURLInterface
    {
        return $this->adjustURL(clone $this->baseURL)
            ->string(
                $this->getRequestPrimaryVarName(),
                (string)$record->getID()
            );
    }

    protected function adjustURL(AdminURLInterface $url) : AdminURLInterface
    {
        foreach($this->inheritRequestVars as $var) {
            $url->inheritParam($var);
        }

        foreach($this->_getHiddenVars() as $name => $value) {
            $url->auto($name, $value);
        }

        return $url;
    }

    final public function isEnabled() : bool
    {
        if(isset($this->parent) && !$this->parent->isRecordSelected()) {
            return false;
        }

        $callback = $this->getEnabledCallback();
        if($callback !== null) {
            return $callback() === true;
        }

        return !$this->isRecordSelected();
    }

    private function handleBreadcrumb(UI_Page_Breadcrumb $breadcrumb): void
    {
        if(!$this->isEnabled()) {
            return;
        }

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
        if(!$this->isEnabled()) {
            return;
        }

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
     * @param CollectionItemInterface $record
     * @return void
     */
    abstract protected function adjustEntry(UI_Bootstrap_BigSelection_Item_Regular $entry, CollectionItemInterface $record) : void;

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
        return t('No records are available.');
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

    /**
     * @inheritDoc
     * @return $this
     */
    public function inheritRequestVar(string $name) : self
    {
        if(!in_array($name, $this->inheritRequestVars, true)) {
            $this->inheritRequestVars[] = $name;
        }

        return $this;
    }
}
