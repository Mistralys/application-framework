<?php

declare(strict_types=1);

class Application_ErrorLog_Log_Entry_General extends Application_ErrorLog_Log_Entry
{
    public function getTypeLabel() : string
    {
        return t('General');
    }
    
    public function addProperties(UI_PropertiesGrid $grid) : void
    {
        
    }
}
