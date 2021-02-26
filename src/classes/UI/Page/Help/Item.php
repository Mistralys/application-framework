<?php

use AppUtils\Traits_Optionable;
use AppUtils\Interface_Optionable;
use AppUtils\Traits_Classable;
use AppUtils\Interface_Classable;

abstract class UI_Page_Help_Item extends UI_Renderable implements UI_Renderable_Interface, Interface_Optionable, Interface_Classable
{
    use Traits_Optionable;
    use Traits_Classable;
    
   /**
    * @var UI_Page_Help
    */
    protected $help;
    
    public function __construct(UI_Page_Help $help, $options=array())
    {
        parent::__construct($help->getPage());
        
        $this->help = $help;
        
        if(!empty($options)) {
            $this->setOptions($options);
        }
    }
}
