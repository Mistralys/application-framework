<?php

declare(strict_types=1);

namespace Application\Media\Collection;

use Application;
use Application\Admin\Area\Media\BaseViewMediaScreen;
use Application\Admin\Area\Media\View\BaseMediaSettingsScreen;
use Application\Admin\Area\Media\View\BaseMediaStatusScreen;
use Application\AppFactory;
use Application\Tags\Taggables\TagContainer;
use Application_Admin_ScreenInterface;
use Application_Exception_DisposableDisposed;
use Application_Media_Document;
use Application_User;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use DateTime;
use DBHelper_BaseCollection_OperationContext_Delete;
use DBHelper_BaseRecord;

/**
 * @property MediaCollection $collection
 * @method MediaCollection getCollection()
 */
class MediaRecord extends DBHelper_BaseRecord implements Application\Tags\Taggables\TaggableInterface
{
    use Application\Tags\Taggables\TaggableTrait;

    public function getLabel(): string
    {
        return $this->getRecordStringKey(MediaCollection::COL_NAME);
    }

    public function getLabelLinked() : string
    {
        return (string)sb()
            ->link(
                $this->getLabel(),
                $this->getAdminViewURL()
            );
    }

    public function getAdminDownloadURL(array $params=array()) : string
    {
        $params[BaseMediaStatusScreen::REQUEST_PARAM_DOWNLOAD] = 'yes';

        return $this->getAdminStatusURL($params);
    }

    public function getAdminStatusURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = BaseMediaStatusScreen::URL_NAME;

        return $this->getAdminViewURL($params);
    }

    public function getAdminSettingsURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = BaseMediaSettingsScreen::URL_NAME;

        return $this->getAdminViewURL($params);
    }

    public function getAdminViewURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseViewMediaScreen::URL_NAME;
        $params[MediaCollection::PRIMARY_NAME] = $this->getID();

        return $this->getCollection()->getAdminURL($params);
    }

    public function getDateAdded() : DateTime
    {
        return $this->getRecordDateKey(MediaCollection::COL_DATE_ADDED);
    }

    public function getAuthor() : Application_User
    {
        return Application::createUser($this->getAuthorID());
    }

    public function getAuthorID() : int
    {
        return $this->getRecordIntKey(MediaCollection::COL_USER_ID);
    }

    public function getDescription() : string
    {
        return $this->getRecordStringKey(MediaCollection::COL_DESCRIPTION);
    }

    public function getKeywords() : string
    {
        return $this->getRecordStringKey(MediaCollection::COL_KEYWORDS);
    }

    public function isEditable() : bool
    {
        return true;
    }

    /**
     * NOTE: This is not the same as the media document's
     * {@see \Application_Media_Document::getFilesize()}
     * method. It uses the database column value first,
     * then the file on disk.
     *
     * @return int
     * @throws Application_Exception_DisposableDisposed
     */
    public function getFileSize() : int
    {
        $size = $this->getRecordIntKey(MediaCollection::COL_SIZE);

        if($size === 0 || !MediaCollection::hasSizeColumn()) {
            return $this->getMediaDocument()->getFilesize();
        }

        return $size;
    }

    /**
     * @param bool $forceDownload
     * @return never
     */
    public function sendFile(bool $forceDownload=false)
    {
        $this->getMediaDocument()->sendFile($forceDownload);
    }

    public function getMediaDocument() : Application_Media_Document
    {
        return AppFactory::createMedia()->getByID($this->getID());
    }

    public function refreshFileSize() : self
    {
        $this->log('Updating the file size.');

        $this->setRecordKey(MediaCollection::COL_SIZE, $this->getMediaDocument()->getFilesize());
        $this->save();
        return $this;
    }

    public function renderThumbnail(?int $preferredSize=null) : string
    {
        return $this->getMediaDocument()->renderThumbnail($preferredSize);
    }

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue) : void
    {
    }

    protected function _onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context): void
    {
        $context->setOption('file_path', $this->getMediaDocument()->getPath());
    }

    protected function _onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context): void
    {
        FileHelper::deleteFile($context->getOption('file_path'));
    }

    public function getTagCollection(): TagContainer
    {
        return $this->collection->getTagContainer();
    }

    public function getTaggedRecordPrimary(): int
    {
        return $this->getID();
    }
}
