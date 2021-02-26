<?php

interface Application_Interfaces_Iconizable
{
   /**
    * @param UI_Icon $icon
    * @return $this
    */
    public function setIcon(UI_Icon $icon);
    
    public function hasIcon() : bool;
    
    public function getIcon() : ?UI_Icon;
}
