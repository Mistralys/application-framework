<?php

declare(strict_types=1);

namespace Application\Media\Collection;

use Application;
use Application\AppFactory;
use Application\Media\MediaException;
use Application_Formable;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet;
use Application_Media_Document;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use Closure;
use DBHelper_BaseRecord;
use HTML_QuickForm2_Element_InputFile;
use HTML_QuickForm2_Element_InputText;
use HTML_QuickForm2_Rule_Callback;
use UI;

class MediaSettingsManager extends Application_Formable_RecordSettings_Extended
{
    public const ERROR_NO_DOCUMENT_IN_CREATE_MODE = 146101;

    public const SETTING_NAME = 'name';
    public const SETTING_FILE = 'file';

    private HTML_QuickForm2_Element_InputFile $fileElement;

    public function __construct(Application_Formable $formable, ?MediaRecord $record = null)
    {
        parent::__construct($formable, AppFactory::createMediaCollection(), $record);

        $this->setDefaultsUseStorageNames(true);
    }

    protected function processPostCreateSettings(DBHelper_BaseRecord $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
    }

    protected function getCreateData(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
        $upload = $this->fileElement->getUpload();

        if(!$upload->isValid()) {
            return;
        }

        $extension = FileHelper::getExtension($upload->getName());
        $type = AppFactory::createMedia()->getTypeByExtension($extension);

        $recordData->setKey(MediaCollection::COL_EXTENSION, $extension);
        $recordData->setKey(MediaCollection::COL_TYPE, $type);
    }

    protected function updateRecord(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
        $document = $this->requireDocument();
        $upload = $this->fileElement->getUpload();

        if(!$upload->isValid()) {
            return;
        }

        $tempFile = FileInfo::factory(Application::getTempFile());

        $upload->moveTo($tempFile->getPath());
        $document->setSourceFile($tempFile);
        $recordData->setKey(MediaCollection::COL_SIZE, $tempFile->getSize());

        $tempFile->delete();
    }

    protected function registerSettings(): void
    {
        $group = $this->addGroup(t('Settings'))
            ->setIcon(UI::icon()->settings());

        $group->registerSetting(self::SETTING_NAME)
            ->setStorageName(MediaCollection::COL_NAME)
            ->makeRequired()
            ->setCallback(Closure::fromCallable(array($this, 'injectName')));

        $file = $group->registerSetting(self::SETTING_FILE)
            ->makeInternal()
            ->setCallback(Closure::fromCallable(array($this, 'injectFile')));

        if(!$this->isEditMode()) {
            $file->makeRequired();
        }
    }

    private function injectName() : HTML_QuickForm2_Element_InputText
    {
        $el = $this->addElementText(self::SETTING_NAME, t('Name'));
        $el->addFilterTrim();

        $this->addRuleLabel($el);
        $this->makeLengthLimited($el, 0, 160);

        return $el;
    }

    private function requireDocument() : Application_Media_Document
    {
        if(isset($this->record)) {
            return $this->record->getMediaDocument();
        }

        throw new MediaException(
            'Cannot get media document when not editing a record.',
            '',
            self::ERROR_NO_DOCUMENT_IN_CREATE_MODE
        );
    }

    private function injectFile() : HTML_QuickForm2_Element_InputFile
    {
        $el = $this->addElementFile(self::SETTING_FILE, t('File'));
        $el->setComment(sb()
            ->t('Choose a media file to upload.')
        );

        if($this->isEditMode())
        {
            $el->appendComment(sb()
                ->t('Leave empty if you do not want to change the file.')
            );

            $this->addRuleCallback($el, Closure::fromCallable(array($this, 'validateFile')), '');
        }

        $this->fileElement = $el;

        return $el;
    }

    private function validateFile($value, HTML_QuickForm2_Rule_Callback $rule) : bool
    {
        $upload = $this->fileElement->getUpload();

        if(!$upload->isValid()) {
            return true;
        }

        $targetExtension = $this->requireDocument()->getExtension();
        $extension = FileHelper::getExtension($upload->getName());

        if($extension === $targetExtension) {
            return true;
        }

        $rule->setMessage(sb()
            ->t(
                'Must be a file with the %1$s extension.',
                sb()->code('.'.$targetExtension)
            )
        );

        return false;
    }

    public function getDefaultSettingName(): string
    {
        return self::SETTING_NAME;
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->canEditMedia();
    }
}
