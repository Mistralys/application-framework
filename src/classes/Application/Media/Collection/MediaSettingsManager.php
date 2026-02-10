<?php

declare(strict_types=1);

namespace Application\Media\Collection;

use Application\AppFactory;
use Application\Application;
use Application\Media\MediaException;
use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable;
use Application_Media_Document;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use Closure;
use DBHelper\Interfaces\DBHelperRecordInterface;
use HTML_QuickForm2_Element_InputFile;
use HTML_QuickForm2_Element_InputText;
use HTML_QuickForm2_Element_Textarea;
use HTML_QuickForm2_Rule_Callback;
use UI;

/**
 * @property MediaRecord|null $record
 */
class MediaSettingsManager extends Application_Formable_RecordSettings_Extended
{
    public const int ERROR_NO_DOCUMENT_IN_CREATE_MODE = 146101;

    public const string SETTING_NAME = 'name';
    public const string SETTING_FILE = 'file';
    public const string SETTING_DESCRIPTION = 'description';
    public const string SETTING_KEYWORDS = 'keywords';

    private HTML_QuickForm2_Element_InputFile $fileElement;

    public function __construct(Application_Interfaces_Formable $formable, ?MediaRecord $record = null)
    {
        parent::__construct($formable, AppFactory::createMediaCollection(), $record);

        $this->setDefaultsUseStorageNames(true);
    }

    protected function processPostCreateSettings(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
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
        $recordData->setKey(MediaCollection::COL_SIZE, $upload->getSize());
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
            ->setIcon(UI::icon()->settings())
            ->expand();

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

        if(!MediaCollection::hasSizeColumn()) {
            return;
        }

        $group = $this->addGroup(t('Description'))
            ->setIcon(UI::icon()->text());

        $group->registerSetting(self::SETTING_DESCRIPTION)
            ->setStorageName(MediaCollection::COL_DESCRIPTION)
            ->setCallback(Closure::fromCallable(array($this, 'injectDescription')));

        $group->registerSetting(self::SETTING_KEYWORDS)
            ->setStorageName(MediaCollection::COL_KEYWORDS)
            ->setCallback(Closure::fromCallable(array($this, 'injectKeywords')));
    }

    private function injectDescription() : HTML_QuickForm2_Element_Textarea
    {
        $el = $this->addElementTextarea(self::SETTING_DESCRIPTION, t('Description'));
        $el->addFilterTrim();
        $el->setComment(sb()
            ->t('Optional description with any relevant information pertaining to the image.')
            ->t('This can include copyright information, sources and the like.')
            ->nl()
            ->t(
                'You may use %1$s syntax for formatting, links and more.',
                sb()->link('Markdown', 'https://commonmark.org/help/', true)
            )
        );

        $this->addRuleNoHTML($el);
        $this->makeLengthLimited($el, 0, 1200);

        return $el;
    }

    private function injectKeywords() : HTML_QuickForm2_Element_Textarea
    {
        $el = $this->addElementTextarea(self::SETTING_KEYWORDS, t('Keywords'));
        $el->addFilterTrim();
        $el->setComment(sb()
            ->t('Optional keywords to help with searching for the image.')
        );

        $this->addRuleNoHTML($el);
        $this->makeLengthLimited($el, 0, 500);

        return $el;
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
            ->nl()
            ->t('Accepted file extensions:')
            ->mono(implode(' ', AppFactory::createMedia()->getExtensions()))
        );

        if($this->isEditMode())
        {
            $el->appendComment(sb()
                ->nl()
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
