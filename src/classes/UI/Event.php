<?php

class UI_Event extends Application_EventHandler_Event
{
   /**
    * @return UI
    */
    public function getUI()
    {
        return $this->getArgument(0);
    }
}