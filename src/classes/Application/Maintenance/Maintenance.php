<?php

use AppLocalize\Localization;
use AppUtils\FileHelper\FolderInfo;

class Application_Maintenance
{
   /**
    * @var Application_Driver
    */
    protected $driver;
    
   /**
    * @var string
    */
    protected $settingName = '__maintenance_plans';
    
   /**
    * @var Application_Maintenance_Plan[]
    */
    protected $plans = array();
    
   /**
    * @var boolean
    */
    protected $changes = false;
    
    public function __construct(Application_Driver $driver)
    {
        $this->driver = $driver;
        
        $data = Application_Driver::createSettings()->getArray($this->settingName);

        foreach($data as $entry) 
        {
            $plan = new Application_Maintenance_Plan(
                $this,
                $entry['id'],
                new DateTime($entry['start']), 
                $entry['duration']
            );
            
            foreach($entry['infoTexts'] as $localeName => $text) 
            {
                $locale = Localization::getAppLocaleByName($localeName);
                $plan->setInfoText($locale, $text);
            }
            
            $this->plans[] = $plan;
        }
    }

    public static function getAdminScreensFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/Admin/Screens')->requireExists();
    }
    
   /**
    * @return Application_Maintenance_Plan[]
    */
    public function getPlans()
    {
        return $this->plans;
    }
    
   /**
    * @param DateTime $start
    * @param string $durationString E.g. "1 hour"
    * @return Application_Maintenance_Plan
    */
    public function addPlan(DateTime $start, $durationString, $infoText = null)
    {
        $this->changes = true;
        
        $plan = new Application_Maintenance_Plan(
            $this, 
            $this->nextID(),
            $start, 
            $durationString
        );
        
        $this->plans[] = $plan;
        
        return $plan;
    }
    
    public function save()
    {
        if(!$this->changes) {
            return;
        }
        
        usort($this->plans, array($this, 'callback_sortPlans'));
        
        $data = array();
        foreach($this->plans as $plan) {
            $data[] = $plan->serialize();
        }

        Application_Driver::createSettings()->setArray($this->settingName, $data);
    }
    
    protected $enabled;
    
    public function isEnabled()
    {
        if(isset($this->enabled)) {
            return $this->enabled;
        }
        
        foreach($this->plans as $plan) {
            if($plan->isEnabled()) {
                $this->enabled = true;
                return true;
            }
        }
        
        $this->enabled = false;
        return false;
    }
    
   /**
    * @return Application_Maintenance_Plan|NULL
    */
    public function getActivePlan()
    {
        foreach($this->plans as $plan) {
            if($plan->isEnabled()) {
                return $plan;
            }
        }

        return null;
    }
    
    public function callback_sortPlans(Application_Maintenance_Plan $a, Application_Maintenance_Plan $b)
    {
        if($a->getStart() > $b->getStart()) {
            return 1;
        }
        
        if($a->getStart() < $b->getStart()) {
            return -1;
        }
        
        return 0;
    }
    
    public function cleanUp()
    {
        $keep = array();
        foreach($this->plans as $plan) {
            if($plan->isValid()) {
                $keep[] = $plan;
            }
        }
        
        if(count($this->plans) != count($keep)) {
            $this->changes = true;
        }
        
        $this->plans = $keep;
    }
    
    protected function nextID() : int
    {
        return Application_Driver::createSettings()
            ->increaseCounter($this->settingName.'_id');
    }
    
    public function idExists(int $id) : bool
    {
        foreach($this->plans as $plan) {
            if($plan->getID() == $id) {
                return true;
            }        
        }
        
        return false;
    }
    
    public function delete(Application_Maintenance_Plan $plan)
    {
        $id = $plan->getID();
        $keep = array();
        foreach($this->plans as $item) {
            if($item->getID() != $id) {
                $keep[] = $item;
            }
        }
        
        if(count($this->plans) != count($keep)) {
            $this->changes = true;
        }
        
        $this->plans = $keep;
    }
    
    public function getByID($id)
    {
        foreach($this->plans as $item) {
            if($item->getID() == $id) {
                return $item;
            }
        }
        
        return null;
    }

    public function renderScreen(UI_Page $page) : string
    {
        $page->selectFrame('maintenance');

        return $page->renderTemplate(
            'maintenance',
            array(
                'plan' => $this->getActivePlan()
            )
        );
    }
}
