<?php 

use AppUtils\Traits_Optionable;
use AppUtils\Interface_Optionable;

abstract class Application_RevisionableCollection_DataGridMultiAction implements Interface_Optionable, Application_Interfaces_Iconizable
{
    use Traits_Optionable;
    
   /**
    * @var Application_Admin_Skeleton
    */
    protected $adminScreen;
    
   /**
    * @var UI_DataGrid
    */
    protected $grid;
    
    protected $initDone = false;
    
    protected $redirectURL;
    
   /**
    * @var Application_RevisionableCollection
    */
    protected $collection;

   /**
    * @var UI_DataGrid_Action
    */
    protected $action;
    
   /**
    * @var string
    */
    protected $id;
    
    public function __construct(Application_RevisionableCollection $collection, Application_Admin_Skeleton $adminScreen, UI_DataGrid $grid, $label, $redirectURL)
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
    public function getAction()
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
    
    public function callback_process(UI_DataGrid_Action $action, $ids)
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

        if($amount == 0) {
            $this->adminScreen->redirectWithInfoMessage(
                t(
                    'No %1$s were selected to which the action could be applied.',
                    $this->collection->getRecordReadableNamePlural()
                ),
                $this->redirectURL
            );
        }
        
        if($amount == 1) {
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
    
    public function setConfirmMessage($message, $withInput=false)
    {
        $this->action->makeConfirm($message, $withInput);
        return $this;
    }
    
   /**
    * @param UI_Icon $icon
    * @return Application_RevisionableCollection_DataGridMultiAction
    */
    public function setIcon(UI_Icon $icon)
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
    
    public function makeDangerous()
    {
        $this->action->makeDangerous();
        return $this;
    }
    
    public function makeSuccess()
    {
        $this->action->makeSuccess();
        return $this;
    }
    
    public function setTooltip($text)
    {
        $this->action->setTooltip($text);
        return $this;
    }
}
