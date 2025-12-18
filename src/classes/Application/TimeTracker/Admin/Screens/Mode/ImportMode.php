<?php

declare(strict_types=1);

namespace Application\TimeTracker\Admin\Screens\Mode;

use Application;
use Application\Admin\Area\BaseMode;
use Application\AppFactory;
use Application\TimeTracker\Admin\TimeTrackerScreenRights;
use Application\TimeTracker\Admin\Traits\ModeInterface;
use Application\TimeTracker\Admin\Traits\ModeTrait;
use Application\TimeTracker\Export\TimeExporter;
use Application\TimeTracker\Export\TimeImporter;
use Application\TimeTracker\TimeTrackerCollection;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\MimeTypesEnum;
use HTML_QuickForm2_Element_InputFile;
use UI;
use UI_Themes_Theme_ContentRenderer;

class ImportMode extends BaseMode implements ModeInterface
{
    use ModeTrait;

    public const string URL_NAME = 'import';

    private TimeTrackerCollection $timeTracker;
    private HTML_QuickForm2_Element_InputFile $fileElement;

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Import');
    }

    public function getTitle(): string
    {
        return t('Import time entries');
    }

    public function getRequiredRight(): string
    {
        return TimeTrackerScreenRights::SCREEN_IMPORT;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getTitle());
        $this->renderer->setAbstract(sb()
            ->t('This allows you to import previously exported time entries.')
            ->noteBold()
            ->t('Imported entries are added as new entries.')
        );
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->timeTracker->adminURL()->import());
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('import', t('Import now'))
            ->setIcon(UI::icon()->import())
            ->makePrimary()
            ->makeClickableSubmit($this);

        $this->sidebar->addButton('cancel', t('Cancel'))
            ->makeLinked($this->timeTracker->adminURL()->globalList());

        $this->sidebar->addSeparator();

        $panel = $this->sidebar->addDeveloperPanel();
        $panel->addConvertedButton('import');
    }

    protected function _handleActions(): bool
    {
        $this->timeTracker = AppFactory::createTimeTracker();

        $this->createImportForm();

        if($this->isFormValid()) {
            $this->handleImport();
        }

        return true;
    }

    private function handleImport() : void
    {
        $this->startTransaction();

        $tempFile = FileInfo::factory(Application::getTempFile(null, 'xlsx'));
        $this->fileElement->getUpload()->moveTo($tempFile->getPath());

        $importer = new TimeImporter($tempFile, $this->user);
        $importer->import();

        FileHelper::deleteFile($tempFile);

        $this->endTransaction();

        $this->redirectWithSuccessMessage(sb()
            ->t(
                'The file has been imported successfully at %1$s.',
                sb()->time()
            )
            ->t(
                '%1$s time entries have been added.',
                $importer->countImportedRows()
            ),
            $this->timeTracker->adminURL()->globalList()
        );
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendFormable($this)
            ->makeWithSidebar();
    }

    private function createImportForm() : void
    {
        $this->createFormableForm('import_time_entries');

        $this->injectUpload();
    }

    private function injectUpload() : void
    {
        $el = $this->addElementFile('import_file', t('File to import'));
        $el->addAccept(MimeTypesEnum::MIME_XLSX);
        $el->setComment(sb()
            ->note()
            ->t('The file must have been downloaded using the export function to be compatible with the import.')
            ->nl()
            ->t('The file must have a header row, and the columns must be in this exact order:')
            ->ul(TimeExporter::COLUMNS)
        );

        $this->fileElement = $el;
    }
}
