<?php

abstract class Application_Admin_Area_Mode_Submode_Action extends Application_Admin_Skeleton
{
    use Application_Traits_Admin_Screen;
    
    /**
     * @var Application_Admin_Area_Mode
     */
    protected $mode;

    /**
     * @var Application_Admin_Area_Mode_Submode
     */
    protected $submode;

    /**
     * @var Application_Admin_Area
     */
    protected $area;

    public function __construct(Application_Driver $driver, Application_Admin_Area_Mode_Submode $submode)
    {
        $this->adminMode = $submode->isAdminMode();
        $this->submode = $submode;
        $this->mode = $submode->getMode();
        $this->area = $submode->getArea();
        
        parent::__construct($driver, $submode);

        $this->initScreen();
    }
    
   /**
    * @return Application_Admin_Area_Mode_Submode
    */
    public function getSubmode() : Application_Admin_Area_Mode_Submode
    {
        return $this->submode;
    }

    /**
     * @return Application_Admin_Area_Mode
     */
    public function getMode() : Application_Admin_Area_Mode
    {
        return $this->mode;
    }

    public function isUserAllowed() : bool
    {
        return true;
    }
}
