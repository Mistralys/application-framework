<?php

declare(strict_types=1);

namespace Application\ErrorLog\Admin\Screens;

use Application\Admin\Area\Mode\BaseSubmode;
use Application\AppFactory;
use Application\ErrorDetails\ExceptionPageRenderer;
use Application\ErrorLog\Admin\ErrorLogScreenRights;
use Application\ErrorLog\Admin\Traits\ErrorLogSubmodeInterface;
use Application\ErrorLog\Admin\Traits\ErrorLogSubmodeTrait;
use Application\ErrorLog\ErrorLogException;
use Application_Bootstrap;
use Application_ErrorLog;
use AppUtils\BaseException;
use AppUtils\FileHelper;
use Connectors;
use Throwable;
use UI;
use UI_DataGrid;
use UI_DataGrid_Action;
use UI_Themes_Theme_ContentRenderer;

class ListSubmode extends BaseSubmode implements ErrorLogSubmodeInterface
{
    use ErrorLogSubmodeTrait;

    public const string URL_NAME = 'list';
    public const string REQUEST_PARAM_TRIGGER_EXCEPTION = 'trigger_exception';
    public const string REQUEST_PARAM_TRIGGER_WARNING = 'trigger_warning';
    public const string REQUEST_PARAM_DELETEALL = 'deleteall';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return ErrorLogScreenRights::SCREEN_LIST;
    }

    public function getTitle(): string
    {
        return t('Error Log Entries');
    }

    public function getNavigationTitle(): string
    {
        return t('List');
    }

    protected Application_ErrorLog $errorlog;

    protected function _handleActions(): bool
    {
        $this->errorlog = AppFactory::createErrorLog();

        if ($this->request->getBool(self::REQUEST_PARAM_DELETEALL)) {
            $this->handle_deleteAll();
        }

        if ($this->request->getBool(self::REQUEST_PARAM_TRIGGER_EXCEPTION)) {
            $this->handle_triggerException();
        }

        if ($this->request->getBool(self::REQUEST_PARAM_TRIGGER_WARNING)) {
            $this->handle_triggerWarning();
        }

        $this->createDatagrid();

        return true;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->mode->getNavigationTitle())->makeLinkedFromMode($this->mode);
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        $entries = array();
        $logs = $this->errorlog->getLogs();

        foreach ($logs as $log) {
            $entries[] = array(
                'id' => $log->getID(),
                'monthnumber' => $log->getMonthNumber(),
                'month' => '<a href="' . $log->getAdminViewURL() . '">' . $log->getMonthName() . '</a>',
                'size' => $log->getFileSizePretty(),
                'bytesize' => $log->getFileSize()
            );
        }

        return $this->renderer
            ->makeWithSidebar()
            ->appendDataGrid(
               $this->datagrid,
                $entries,
            );
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle(t('Error logs for %1$s', $this->errorlog->getYear()));
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('clear_all', t('Delete all'))
            ->makeDangerous()
            ->makeConfirm(t('This will delete all logfiles for %1$s.', $this->errorlog->getYear()))
            ->setTooltip(t('This will delete all logfiles for %1$s.', $this->errorlog->getYear()))
            ->makeLinked($this->errorlog->getAdminDeleteAllURL());

        $this->sidebar->addSeparator();

        $panel = $this->sidebar->addDeveloperPanel();

        $panel->addHeading(t('Send errors into the log'));

        $panel->addButton(
            UI::button(t('%1$s exception', 'PHP'))
                ->link($this->errorlog->getAdminTriggerExceptionURL())
        );

        $panel->addButton(
            UI::button(t('%1$s exception', 'PHP') . ' (incl. previous)')
                ->link($this->errorlog->getAdminTriggerExceptionURL(array('prev' => 'yes')))
        );

        $panel->addButton(
            UI::button(t('%1$s warning', 'PHP'))
                ->link($this->errorlog->getAdminTriggerWarningURL())
        );

        $panel->addButton(
            UI::button('PHP AppUtils BaseException')
                ->link($this->errorlog->getAdminTriggerExceptionURL(array('base' => 'yes')))
        );

        $panel->addButton(
            UI::button('Connector exception')
                ->link($this->errorlog->getAdminTriggerExceptionURL(array('connector' => 'yes')))
        );

        $panel->addButton(
            UI::button(t('%1$s exception', 'JavaScript'))
                ->click('throw new ApplicationException(\'Test JavaScript exception\', \'Developer info details\', 42903)')
        );

        $panel->addButton(
            UI::button(t('%1$s exception', 'JavaScript') . ' (' . t('Generic') . ')')
                ->click('throw new Error(\'Test generic exception class\')')
        );

        $panel->addButton(
            UI::button(t('%1$s exception', 'AJAX'))
                ->click('application.createAJAX(\'ThrowError\').Send()')
        );

        $panel->addSeparator();

        $panel->addHeading(t('Display an exception page'));

        $panel->addButton(
            UI::button(t('%1$s exception', 'PHP'))
                ->link($this->errorlog->getAdminTriggerExceptionURL(array('prev' => 'yes', 'display' => 'yes')))
        );

        $panel->addSeparator();

        $reporting = (int)ini_get('error_reporting');

        $panel->addHeading(t('PHP error reporting'));

        $panel->addHTML(sb()
            ->ul(array(
                'Display errors: ' . bool2string(ini_get('display_errors')),
                'Error reporting: ' . $reporting,
                'E_ALL: ' . bool2string(($reporting & E_ALL) > 0),
                'E_ERROR: ' . bool2string(($reporting & E_ERROR) > 0),
                'E_WARNING: ' . bool2string(($reporting & E_WARNING) > 0),
                'E_NOTICE: ' . bool2string(($reporting & E_NOTICE) > 0),
                'E_PARSE: ' . bool2string(($reporting & E_PARSE) > 0),
                'E_USER_ERROR: ' . bool2string(($reporting & E_USER_ERROR) > 0),
                'E_USER_WARNING: ' . bool2string(($reporting & E_USER_WARNING) > 0),
                'E_USER_NOTICE: ' . bool2string(($reporting & E_USER_NOTICE) > 0)
            ))
        );
    }

    protected UI_DataGrid $datagrid;

    protected function createDatagrid(): void
    {
        $grid = $this->ui->createDataGrid('errorlogs');
        $grid->configureForScreen($this);

        $grid->configureForScreen($this);

        $grid->addColumn('month', t('Month'))
            ->setSortable(true)
            ->setSortingNumeric('monthnumber');

        $grid->addColumn('size', t('Size'))
            ->setSortable()
            ->setSortingNumeric('bytesize');

        $grid->enableMultiSelect('id');

        $grid->addConfirmAction(
            'delete',
            t('Delete...'),
            t('This will delete the log file for the month.') . ' ' .
            t('This cannot be undone, are you sure?')
        )
            ->setIcon(UI::icon()->delete())
            ->makeDangerous()
            ->setCallback(array($this, 'handle_multiDelete'));

        $this->datagrid = $grid;
    }

    protected function handle_deleteAll() : never
    {
        $logs = $this->errorlog->getFolder();

        FileHelper::deleteTree($logs);

        $this->redirectWithSuccessMessage(
            t(
                'All error logs for %1$s have been deleted successfully at %2$s.',
                $this->errorlog->getYear(),
                date('H:i:s')
            ),
            $this->errorlog->getAdminListURL()
        );
    }

    public function handle_multiDelete(UI_DataGrid_Action $action) : void
    {
        foreach ($action->getSelectedValues() as $eid) {
            $log = $this->errorlog->getByID((string)$eid);

            FileHelper::deleteFile($log->getFilePath());
        }

        $this->redirectWithSuccessMessage(
            UI::icon()->ok() . ' ' .
            t('The selected error logs have been deleted at %1$s.', date('H:i:s')),
            $this->getURL()
        );
    }

    protected function handle_triggerException(): void
    {
        try {
            $prev = null;

            if ($this->request->getBool('prev')) {
                $prev = new ErrorLogException(
                    'Test previous exception',
                    'This is a previous exception triggered on purpose in the errorlogs admin.',
                    ErrorLogException::ERROR_TEST_PREVIOUS_EXCEPTION
                );
            }

            if ($this->request->getBool('base')) {
                throw new BaseException(
                    'Errorlog test AppUtils base exception',
                    'This is an exception triggered on purpose in the errorlogs admin.',
                    42901,
                    $prev
                );
            }

            if ($this->request->getBool('connector')) {
                Connectors::createStubConnector()->executeFailRequest();
            }

            throw new ErrorLogException(
                'Errorlog test exception',
                'This is an exception triggered on purpose in the errorlogs admin.',
                ErrorLogException::ERROR_TEST_EXCEPTION,
                $prev
            );
        } catch (Throwable $e) {
            /* @see ExceptionPageRenderer */
            if($this->request->getBool('display')) {
                throw $e;
            }

            $e = Application_Bootstrap::convertException($e);
            $e->log();
        }

        $this->redirectWithSuccessMessage(
            t('Exception triggered successfully at %1$s.', date('H:i:s')),
            $this->errorlog->getAdminListURL()
        );
    }

    private function handle_triggerWarning(): void
    {
        trigger_error('This is a test warning triggered on purpose in the errorlogs admin.', E_USER_WARNING);
    }
}
