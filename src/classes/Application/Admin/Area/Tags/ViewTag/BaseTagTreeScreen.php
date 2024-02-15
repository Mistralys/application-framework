<?php

declare(strict_types=1);

namespace Application\Area\Tags\ViewTag;

use Application\AppFactory;
use Application\Tags\TagCollection;
use Application\Tags\TagRecord;
use Application_Admin_Area_Mode_Submode_CollectionRecord;

/**
 * @property TagRecord $record
 */
class BaseTagTreeScreen extends Application_Admin_Area_Mode_Submode_CollectionRecord
{
    public const URL_NAME = 'tag-tree';
    public const REQUEST_PARAM_DELETE_TAG = 'delete-tag';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    protected function createCollection() : TagCollection
    {
        return AppFactory::createTags();
    }

    protected function getRecordMissingURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function getNavigationTitle(): string
    {
        return t('Tree');
    }

    public function getTitle(): string
    {
        return t('Tag tree');
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    protected function _handleActions(): bool
    {
        if(parent::_handleActions() === false) {
            return false;
        }

        $this->handleDeleteTag();

        return true;
    }

    private function handleDeleteTag() : void
    {
        $tagID = $this->request->registerParam(self::REQUEST_PARAM_DELETE_TAG)->getInt();
        $collection = $this->createCollection();

        if($tagID === 0 || !$collection->idExists($tagID) || $tagID === $this->record->getID()) {
            return;
        }

        $tag = $collection->getByID($tagID);

        $this->startTransaction();

        $collection->deleteRecord($tag);

        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            t(
                'The tag %1$s has been deleted successfully at %2$s.',
                $tag->getLabel(),
                sb()->time()
            ),
            $this->record->getAdminTagTreeURL()
        );
    }

    protected function _renderContent()
    {
        return $this->renderer
            ->appendContent($this->record
                ->getRootTag()
                ->createTreeRenderer()
                ->makeEditable()
            )
            ->makeWithSidebar();
    }
}
