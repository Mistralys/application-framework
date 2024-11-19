<?php

declare(strict_types=1);

namespace TestDriver\Area\RevisionableScreen;

use Application\Revisionable\RevisionableInterface;
use Application_Admin_Area_Mode_RevisionableList;
use Application_RevisionableCollection;
use TestDriver\Revisionables\RevisionableCollection;
use UI;

class RevisionableListScreen extends Application_Admin_Area_Mode_RevisionableList
{
    public const URL_NAME = 'list';

    /**
     * @return RevisionableCollection
     */
    public function getCollection(): Application_RevisionableCollection
    {
        return RevisionableCollection::getInstance();
    }

    protected function getEntryData(RevisionableInterface $revisionable) : array
    {
        return array();
    }

    protected function configureColumns() : void
    {
    }

    protected function configureActions() : void
    {
    }

    public function getBackOrCancelURL(): string
    {
        // TODO: Implement getBackOrCancelURL() method.
    }

    public function isUserAllowed(): bool
    {
        return true;
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getTitle(): string
    {
        return t('Available revisionables');
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('add-new', t('Create revisionable'))
            ->setIcon(UI::icon()->add())
            ->setTooltip(t('Opens the screen to create a new revisionable.'))
            ->link($this->getCollection()->adminURL()->create());

        parent::_handleSidebar();
    }
}
