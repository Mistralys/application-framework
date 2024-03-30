<?php

declare(strict_types=1);

namespace Application\Admin\Area\Media;

use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application_Admin_Area_Mode_CollectionList;
use Application_User;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use Closure;
use DBHelper_BaseCollection;
use DBHelper_BaseFilterCriteria_Record;
use DBHelper_BaseRecord;
use UI;
use UI_DataGrid_Action;

abstract class BaseMediaListScreen extends Application_Admin_Area_Mode_CollectionList
{
    public const URL_NAME = 'list';
    public const REQUEST_PARAM_UPDATE_SIZES = 'update-sizes';

    public const COL_LABEL = 'label';
    public const COL_TYPE = 'type';
    public const COL_SIZE = 'size';
    public const COL_DATE_ADDED = 'date_added';
    public const COL_ADDED_BY = 'added_by';
    public const COL_EXTENSION = 'extension';
    public const COL_ID = 'id';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    /**
     * @return MediaCollection
     */
    protected function createCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createMediaCollection();
    }

    protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry) : array
    {
        $media = ClassHelper::requireObjectInstanceOf(MediaRecord::class, $record);
        $document = $media->getMediaDocument();

        return array(
            self::COL_LABEL => $media->getLabelLinked(),
            self::COL_TYPE => sb()->icon($document->getTypeIcon())->add($document->getTypeLabel()),
            self::COL_SIZE => ConvertHelper::bytes2readable($media->getFileSize()),
            self::COL_ADDED_BY => $media->getAuthor()->getName(),
            self::COL_EXTENSION => $document->getExtension(),
            self::COL_DATE_ADDED => ConvertHelper::date2listLabel($media->getDateAdded(), true, true),
            self::COL_ID => $media->getID()
        );
    }

    protected function _handleActions(): bool
    {
        if($this->request->getBool(self::REQUEST_PARAM_UPDATE_SIZES) && $this->user->isDeveloper())
        {
            $this->startTransaction();
            $this->createCollection()->updateFileSizes();
            $this->endTransaction();

            $this->redirectWithSuccessMessage(
                t('The file sizes have been updated successfully at %1$s.', sb()->time()),
                $this->createCollection()->adminURL()->list()
            );
        }

        return parent::_handleActions();
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create-media', t('Add a file...'))
            ->setIcon(UI::icon()->add())
            ->link($this->createCollection()->adminURL()->create());

        parent::_handleSidebar();

        $this->sidebar->addSeparator();

        $dev = $this->sidebar->addDeveloperPanel();

        $dev->addButton(UI::button(t('Update file sizes'))
            ->setTooltip(t('Update the file size of all media files in the database.'))
            ->requireTrue(MediaCollection::hasSizeColumn())
            ->setIcon(UI::icon()->refresh())
            ->link($this->createCollection()->adminURL()->updateSizes())
        );

        $dev->addButton(UI::button(t('Update file sizes (simulate)'))
            ->requireTrue(MediaCollection::hasSizeColumn())
            ->setIcon(UI::icon()->refresh())
            ->link($this->createCollection()->adminURL()->updateSizes()->simulation())
        );
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_ID, t('ID'))->setCompact()->alignRight();
        $this->grid->addColumn(self::COL_TYPE, t('Type'))->setCompact();
        $this->grid->addColumn(self::COL_LABEL, t('Name'))->setSortable(true, MediaCollection::COL_NAME);
        $this->grid->addColumn(self::COL_EXTENSION, t('Extension'))->setSortable(false, MediaCollection::COL_EXTENSION);
        $this->grid->addColumn(self::COL_DATE_ADDED, t('Added on'))->alignRight()->setSortable(false, MediaCollection::COL_DATE_ADDED);
        $this->grid->addColumn(self::COL_ADDED_BY, t('Added by'));
        $this->grid->addColumn(self::COL_SIZE, t('Size'))->alignRight()->setSortable(false, MediaCollection::COL_SIZE);
    }

    protected function configureActions(): void
    {
        $this->grid->addAction('delete', t('Delete...'))
            ->setIcon(UI::icon()->delete())
            ->makeDangerous()
            ->requireRight(Application_User::RIGHT_DELETE_MEDIA)
            ->setTooltip(t('Delete the selected media files.'))
            ->setCallback(Closure::fromCallable(array($this, 'handleMultiDelete')))
            ->makeConfirm(sb()
                ->para(sb()
                    ->t('This will delete the media file from the database, and from the disk.')
                )
                ->para(sb()
                    ->bold(t(
                        'This can cause errors if any items in %1$s still use the file.',
                        $this->driver->getAppNameShort()
                    ))
                    ->t('For technical reasons, not all uses of the file can be automatically detected.')
                )
                ->para(sb()
                    ->cannotBeUndone()
                )
            );
    }

    protected function handleMultiDelete(UI_DataGrid_Action $action): void
    {
        $action->createRedirectMessage($this->createCollection()->adminURL()->list())
            ->single(t('The media file %1$s has been deleted successfully at %2$s.', '$label', '$time'))
            ->multiple(t('%1$s media files have been deleted successfully at %2$s.', '$amount', '$time'))
            ->none(t('No media files selected that could be deleted.'))
            ->processDeleteDBRecords($this->createCollection())
            ->redirect();
    }

    public function getBackOrCancelURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }

    public function isUserAllowed(): bool
    {
        return $this->user->canViewMedia();
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getTitle(): string
    {
        return t('Available media files');
    }
}
