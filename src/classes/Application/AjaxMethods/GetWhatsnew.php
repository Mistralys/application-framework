<?php

use Application\WhatsNew;
use AppLocalize\Localization;

class Application_AjaxMethods_GetWhatsnew extends Application_AjaxMethod
{
    public function processJSON()
    {
        $changes = array(
            'last_version' => $this->lastVersion,
            'current_version' => $this->driver->getExtendedVersion(),
            'current_version_number' => $this->driver->getVersion()
        );
        
        $versions = $this->whatsnew->getVersions();
        
        foreach($versions as $version) 
        {
            $number = $version->getNumber();
        
            foreach($this->languages as $payloadName => $langID) 
            {
                if(!isset($changes[$payloadName])) {
                    $changes[$payloadName] = array();
                }
                
                $data = null;
                
                if($version->hasLanguage($langID)) 
                {
                     $lang = $version->getLanguage($langID);
                     $data = $lang->toArray();
                }
                
                $changes[$payloadName][$number] = $data;
            }
        }
        
        $this->sendResponse($changes);
    }

    /**
     * @var string
     */
    protected string $lastVersion;
    
   /**
    * @var WhatsNew
    */
    protected WhatsNew $whatsnew;

    /**
     * @var array<string,string>
     */
    protected array $languages = array(
        'versions' => 'en'
    );
    
    protected function validateRequest() : void
    {
        $this->whatsnew = Application_Driver::createWhatsnew();

        $this->languages['versions'] = strtoupper(Localization::getAppLocale()->getLanguageCode());
        
        if($this->user->isDeveloper()) {
            $this->languages['dev'] = 'DEV';
        }
        
        $this->lastVersion = (string)$this->request->getParam('last_version');
        if (empty($this->lastVersion)) {
            $this->lastVersion = $this->whatsnew->getCurrentVersion()->getNumber();
        }
    }
}