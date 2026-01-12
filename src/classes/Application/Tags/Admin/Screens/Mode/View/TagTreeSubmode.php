<?php

declare(strict_types=1);

namespace Application\Tags\Admin\Screens\Mode\View;

use Application\Tags\Admin\TagScreenRights;
use Application\Tags\Admin\Traits\ViewSubmodeInterface;
use Application\Tags\Admin\Traits\ViewSubmodeTrait;
use Application\Tags\TagRecord;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode;
use UI_Themes_Theme_ContentRenderer;

/**
 * @property TagRecord $record
 */

class TagTreeSubmode extends BaseRecordSubmode implements ViewSubmodeInterface
{
    use ViewSubmodeTrait;

    public const string URL_NAME = 'tag-tree';
    public const string REQUEST_PARAM_DELETE_TAG = 'delete-tag';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return TagScreenRights::SCREEN_VIEW_TAG_TREE;
    }

    public function getRecordMissingURL(): string
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

    public function getDefaultSubscreenClass(): ?string
    {
        return null;
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

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->record->getLabel());
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
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
