<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\Mode;

use Application\Admin\Area\BaseMode;
use Application\AppFactory;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\Admin\Traits\ModeInterface;
use Application\TimeTracker\Admin\Traits\ModeTrait;
use Application\TimeTracker\Export\TimeExporter;
use Application\TimeTracker\TimeTrackerCollection;
use UI;
use UI_Themes_Theme_ContentRenderer;

class ExportMode extends BaseMode implements ModeInterface
{
    use ModeTrait;

    public const string URL_NAME = 'export';
    public const string REQUEST_PARAM_CONFIRM = 'confirm';

    private TimeTrackerCollection $timeTracker;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Export');
    }

    public function getTitle(): string
    {
        return t('Export time entries');
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_EXPORT;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());
        $this->renderer->setAbstract(sb()
            ->t('This allows you to export time entries.')
            ->t('This makes it possible, among other things, to import the data again in another instance.')
        );
    }

    protected function _handleActions(): bool
    {
        $this->timeTracker = AppFactory::createTimeTracker();

        if($this->request->getBool(self::REQUEST_PARAM_CONFIRM)) {
            $this->handleExport();
        }

        return true;
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked(AppFactory::createTimeTracker()->adminURL()->export());
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('export', t('Export now'))
            ->setIcon(UI::icon()->export())
            ->makePrimary()
            ->makeLinked($this->timeTracker->adminURL()->exportConfirm());

        $this->sidebar->addButton('cancel', t('Cancel'))
            ->makeLinked($this->timeTracker->adminURL()->globalList());
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->makeWithSidebar();
    }

    protected function handleExport() : void
    {
        new TimeExporter()->sendFile();
    }
}
