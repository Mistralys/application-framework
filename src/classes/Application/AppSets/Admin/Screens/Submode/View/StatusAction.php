<?php

declare(strict_types=1);

namespace Application\AppSets\Admin\Screens\Submode\View;

use Application\AppSets\AppSet;
use Application\Sets\Admin\AppSetScreenRights;
use Application\Sets\Admin\Traits\ViewActionInterface;
use Application\Sets\Admin\Traits\ViewActionTrait;
use DBHelper\Admin\Screens\Action\BaseRecordStatusAction;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;

/**
 * @property AppSet $record
 */
class StatusAction extends BaseRecordStatusAction implements ViewActionInterface
{
    use ViewActionTrait;

    public const string URL_NAME = 'status';
    public const string REQUEST_PARAM_SET_ACTIVE = 'set_active';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Status');
    }

    public function getRequiredRight(): string
    {
        return AppSetScreenRights::SCREEN_VIEW_STATUS;
    }

    public function getRecordStatusURL(): AdminURLInterface
    {
        return $this->record->adminURL()->status();
    }

    protected function _populateGrid(UI_PropertiesGrid $grid, DBHelperRecordInterface $record): void
    {
        $set = $this->resolveAppSet($record);

        $grid->add(t('ID'), sb()->codeCopy($set->getID()));
        $grid->add(t('Alias'), sb()->codeCopy($set->getAlias()));
        $grid->add(t('Label'), $set->getLabel());

        $grid->addBoolean(t('Is current?'), $set->isActive())
            ->setComment(t('Whether this is the currently active application set.'))
            ->makeYesNo();

        $grid->add(t('Default area'), $set->getDefaultArea()->getTitle());
        $grid->add(t('Enabled areas'), sb()->ul($set->getEnabledAreaLabels()));
    }

    protected function _handleSidebar(): void
    {
        $btnActive = $this->sidebar->addButton('make-active', t('Make Active'))
            ->setIcon(UI::icon()->activate())
            ->makePrimary()
            ->makeLinked($this->record->adminURL()->makeActive());

        if($this->record->isActive()) {
            $btnActive->disable(t('This application set is already the active one.'));
        }
    }

    protected function _handleActions(): bool
    {
        if($this->request->getBool(self::REQUEST_PARAM_SET_ACTIVE)) {
            $this->handleActivation();
        }

        return true;
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->record->getLabel());
    }

    private function handleActivation(): void
    {
        $this->startTransaction();

        $this->createCollection()->makeSetActive($this->record);

        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            t(
                'The application set %s has been made active at %2$s.',
                sb()->reference($this->record->getLabel()),
                sb()->time()
            ),
            $this->record->adminURL()->status()
        );
    }
}
