<?php

abstract class Application_Admin_Area_Mode_Maintenance_List extends Application_Admin_Area_Mode_Submode
{
   /**
    * @var Application_Admin_Area_Mode_Maintenance
    */
    protected $mode;
    
    public function getNavigationTitle() : string
    {
        return t('Maintenance plans');
    }

    public function getDefaultAction() : string
    {
        return '';
    }

    public function getURLName() : string
    {
        return 'list';
    }

    public function getTitle() : string
    {
        return t('Maintenance plans');
    }
    
   /**
    * @var Application_Maintenance
    */
    protected $maintenance;
    
    protected function _handleActions()
    {
        $this->maintenance = $this->driver->getMaintenance();
        
        if($this->request->getBool('simulate_plan')) {
            $this->handleSimulate();
        }
        
        if($this->request->getBool('delete')) {
            $this->handleDelete();
        }
        
        $this->createDataGrid();
    }
    
    protected function _renderContent()
    {
        // do some cleanup on the occasion to have a clean list every time.
        $this->maintenance->cleanUp();
        $this->maintenance->save();
        
        $plans = $this->maintenance->getPlans();
        
        $entries = array();
        foreach($plans as $plan) {
            $entries[] = array(
                'start' => AppUtils\ConvertHelper::date2listLabel($plan->getStart(), true, true),
                'end' => AppUtils\ConvertHelper::date2listLabel($plan->getEnd(), true, true),
                'duration' => AppUtils\ConvertHelper::interval2string($plan->getDuration()),
                'enabled' => $plan->getEnabledBadge(),
                'actions' => 
                UI::button()
                ->setIcon(UI::icon()->delete())
                ->link($this->getURL(array('delete' => 'yes', 'plan_id' => $plan->getID())))
                ->makeMini()
                ->makeDangerous().' '.
                UI::button()
                ->makeMini()
                ->setIcon(UI::icon()->view())
                ->setTooltipText(t('Preview the maintenance screen for regular users'))
                ->link($this->getURL(array('simulate_plan' => 'yes', 'plan_id' => $plan->getID())), '_blank')
            );
        }
        
        return $this->renderDatagrid($this->getTitle(), $this->grid, $entries);
    }
    
    protected function _handleSidebar()
    {
        $this->sidebar->addButton('add_plan', t('Add new plan'))
        ->setIcon(UI::icon()->add())
        ->makePrimary()
        ->makeLinked($this->area->getURL(array('mode' => 'maintenance', 'submode' => 'create')));
        
        $this->sidebar->addSeparator();
        
        $this->sidebar->addInfoMessage(
            t('Current server time:').' '.
            date('H:i')
        );
    }
    
    protected function _handleBreadcrumb()
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getNavigationTitle());
    }
    
   /**
    * @var UI_DataGrid
    */
    protected $grid;
    
    protected function createDataGrid()
    {
        $grid = $this->ui->createDataGrid('maintenance-plans');
        
        $grid->addColumn('start', t('Start'));
        $grid->addColumn('duration', t('Duration'));
        $grid->addColumn('end', t('End'));
        $grid->addColumn('enabled', t('Enabled?'));
        $grid->addColumn('actions', '')->setCompact()->roleActions();
        
        $this->grid = $grid;
    }
    
    protected function handleDelete()
    {
        $plan = $this->getPlan();
        
        $this->maintenance->delete($plan);
        $this->maintenance->save();
        
        $this->redirectWithSuccessMessage(
            t('The maintenance plan was removed successfully.'),
            $this->getURL()
        );
    }
    
    protected function handleSimulate()
    {
        $plan = $this->getPlan();
        
        echo $this->renderTemplate('maintenance', array('plan' => $plan));
        exit;
        
    }
    
    protected function getPlan()
    {
        $id = $this->request->registerParam('plan_id')->setInteger()->setCallback(array($this->maintenance, 'idExists'))->get();
        
        if(empty($id)) {
            $this->redirectWithErrorMessage(
                t('Unknown maintenance plan specified.'),
                $this->getURL()
            );
        }
        
        return $this->maintenance->getByID($id);
    }
}