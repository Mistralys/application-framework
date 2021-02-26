<?php

class UI_Event_FormCreated extends UI_Event
{
   /**
    * @return UI_Form
    */
    public function getForm()
    {
        return $this->getArgument(1);
    }
}