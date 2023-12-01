<?php

declare(strict_types=1);

use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\ClassableTrait;
use AppUtils\Traits\OptionableTrait;

abstract class UI_Page_Help_Item extends UI_Renderable
    implements
    OptionableInterface,
    ClassableInterface
{
    use OptionableTrait;
    use ClassableTrait;
    
    protected UI_Page_Help $help;
    
    public function __construct(UI_Page_Help $help, $options=array())
    {
        parent::__construct($help->getPage());
        
        $this->help = $help;
        
        if(!empty($options)) {
            $this->setOptions($options);
        }
    }
}
