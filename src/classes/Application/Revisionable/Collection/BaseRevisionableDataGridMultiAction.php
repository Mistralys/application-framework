<?php

declare(strict_types=1);

namespace Application\Revisionable\Collection;

use Application\Revisionable\RevisionableInterface;
use Application_Admin_Skeleton;
use Application_Interfaces_Iconizable;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Traits\OptionableTrait;
use UI\AdminURLs\AdminURLInterface;
use UI_DataGrid;
use UI_DataGrid_Action;
use UI_Exception;
use UI_Icon;

abstract class BaseRevisionableDataGridMultiAction implements OptionableInterface, Application_Interfaces_Iconizable
{
    use OptionableTrait;

    protected Application_Admin_Skeleton $adminScreen;
    protected UI_DataGrid $grid;
    protected bool $initDone = false;
    protected string $redirectURL;
    protected BaseRevisionableCollection $collection;
    protected UI_DataGrid_Action $action;
    protected string $id;

    /**
     * @param BaseRevisionableCollection $collection
     * @param Application_Admin_Skeleton $adminScreen
     * @param UI_DataGrid $grid
     * @param string|int|float|StringableInterface|null $label
     * @param string|AdminURLInterface $redirectURL
     * @throws UI_Exception
     */
    public function __construct(BaseRevisionableCollection $collection, Application_Admin_Skeleton $adminScreen, UI_DataGrid $grid, string|int|float|StringableInterface|null $label, string|AdminURLInterface $redirectURL)
    {
        $this->collection = $collection;
        $this->adminScreen = $adminScreen;
        $this->grid = $grid;
        $this->id = nextJSID();
        $this->redirectURL = (string)$redirectURL;

        if ($this->getOption('confirm')) {
            $this->action = $this->grid->addConfirmAction(
                $this->id,
                $label,
                ''
            );
        } else {
            $this->action = $this->grid->addAction(
                $this->id,
                $label
            );
        }

        $this->action->setCallback(array($this, 'callback_process'));
    }

    /**
     * Retrieves the data grid action instance.
     * @return UI_DataGrid_Action
     */
    public function getAction(): UI_DataGrid_Action
    {
        return $this->action;
    }

    public function getDefaultOptions(): array
    {
        return array(
            'confirm' => false
        );
    }

    abstract protected function getSingleMessage(RevisionableInterface $revisionable): string;

    /**
     * @param int $amount
     * @param RevisionableInterface[] $processed
     * @return string
     */
    abstract protected function getMultipleMessage(int $amount, array $processed): string;

    abstract protected function processEntry(RevisionableInterface $revisionable): void;

    public function callback_process(UI_DataGrid_Action $action): void
    {
        $this->adminScreen->startTransaction();

        $processed = array();
        foreach ($action->getSelectedValues() as $revisionable_id) {
            $record = $this->collection->getByID((int)$revisionable_id);

            $record->startCurrentUserTransaction();

            $this->processEntry($record);
            if ($record->hasChanges()) {
                $processed[] = $record;
            }

            $record->endTransaction();
        }

        $this->adminScreen->endTransaction();

        $amount = count($processed);

        if ($amount === 0) {
            $this->adminScreen->redirectWithInfoMessage(
                t(
                    'No %1$s were selected to which the action could be applied.',
                    $this->collection->getRecordReadableNamePlural()
                ),
                $this->redirectURL
            );
        }

        if ($amount === 1) {
            $this->adminScreen->redirectWithSuccessMessage(
                $this->getSingleMessage($processed[0]),
                $this->redirectURL
            );
        }

        $this->adminScreen->redirectWithSuccessMessage(
            $this->getMultipleMessage($amount, $processed),
            $this->redirectURL
        );
    }

    /**
     * @param string|number|StringableInterface $message
     * @param bool $withInput
     * @return $this
     * @throws UI_Exception
     */
    public function setConfirmMessage($message, bool $withInput = false): self
    {
        $this->action->makeConfirm($message, $withInput);
        return $this;
    }

    /**
     * @param UI_Icon|NULL $icon
     * @return $this
     */
    public function setIcon(?UI_Icon $icon): self
    {
        $this->action->setIcon($icon);
        return $this;
    }

    public function hasIcon(): bool
    {
        return $this->action->hasIcon();
    }

    public function getIcon(): ?UI_Icon
    {
        return $this->action->getIcon();
    }

    /**
     * @return $this
     */
    public function makeDangerous(): self
    {
        $this->action->makeDangerous();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeSuccess(): self
    {
        $this->action->makeSuccess();
        return $this;
    }

    /**
     * @param string|number|StringableInterface|NULL $text
     * @return $this
     * @throws UI_Exception
     */
    public function setTooltip($text): self
    {
        $this->action->setTooltip($text);
        return $this;
    }
}
