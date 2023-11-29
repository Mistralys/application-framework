<?php

declare(strict_types=1);

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Traits\OptionableTrait;

abstract class Application_RevisionableCollection_DataGridMultiAction implements OptionableInterface, Application_Interfaces_Iconizable
{
    use OptionableTrait;
    
    protected Application_Admin_Skeleton $adminScreen;
    protected UI_DataGrid $grid;
    protected bool $initDone = false;
    protected string $redirectURL;
    protected Application_RevisionableCollection $collection;
    protected UI_DataGrid_Action $action;
    protected string $id;

    /**
     * @param Application_RevisionableCollection $collection
     * @param Application_Admin_Skeleton $adminScreen
     * @param UI_DataGrid $grid
     * @param string|number|StringableInterface $label
     * @param string $redirectURL
     * @throws UI_Exception
     */
    public function __construct(Application_RevisionableCollection $collection, Application_Admin_Skeleton $adminScreen, UI_DataGrid $grid, $label, string $redirectURL)
    {
        $this->collection = $collection;
        $this->adminScreen = $adminScreen;
        $this->grid = $grid;
        $this->id = nextJSID();
        $this->redirectURL = $redirectURL;

        if($this->getOption('confirm')) {
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
    
    public function getDefaultOptions() : array
    {
        return array(
            'confirm' => false
        );
    }
    
    abstract protected function getSingleMessage(Application_RevisionableCollection_DBRevisionable $revisionable);
    
    abstract protected function getMultipleMessage($amount, $processed);
    
    abstract protected function processEntry(Application_RevisionableCollection_DBRevisionable $revisionable);
    
    public function callback_process(UI_DataGrid_Action $action, $ids): void
    {
        $this->adminScreen->startTransaction();
        
        $processed = array();
        foreach($ids as $revisionable_id) 
        {
            $record = $this->collection->getByID($revisionable_id);
            
            $record->startCurrentUserTransaction();
            
            $this->processEntry($record);
            if($record->hasChanges()) {
                $processed[] = $record;
            }
            
            $record->endTransaction();
        }
        
        $this->adminScreen->endTransaction();
        
        $amount = count($processed);

        if($amount === 0) {
            $this->adminScreen->redirectWithInfoMessage(
                t(
                    'No %1$s were selected to which the action could be applied.',
                    $this->collection->getRecordReadableNamePlural()
                ),
                $this->redirectURL
            );
        }
        
        if($amount === 1) {
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
    public function setConfirmMessage($message, bool $withInput=false) : self
    {
        $this->action->makeConfirm($message, $withInput);
        return $this;
    }
    
   /**
    * @param UI_Icon|NULL $icon
    * @return $this
    */
    public function setIcon(?UI_Icon $icon) : self
    {
        $this->action->setIcon($icon);
        return $this;
    }
    
    public function hasIcon() : bool
    {
        return $this->action->hasIcon();
    }
    
    public function getIcon() : ?UI_Icon
    {
        return $this->action->getIcon();
    }

    /**
     * @return $this
     */
    public function makeDangerous() : self
    {
        $this->action->makeDangerous();
        return $this;
    }

    /**
     * @return $this
     */
    public function makeSuccess() : self
    {
        $this->action->makeSuccess();
        return $this;
    }

    /**
     * @param string|number|StringableInterface|NULL $text
     * @return $this
     * @throws UI_Exception
     */
    public function setTooltip($text) : self
    {
        $this->action->setTooltip($text);
        return $this;
    }
}
