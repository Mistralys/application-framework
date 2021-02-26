<?php

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
    protected $lastVersion;
    
   /**
    * @var Application_Whatsnew
    */
    protected $whatsnew;

    /**
     * @var array<string,string>
     */
    protected $languages = array(
        'versions' => 'en'
    );
    
    protected function validateRequest()
    {
        $this->whatsnew = $this->driver->createWhatsnew();

        $this->languages['versions'] = \AppLocalize\Localization::getAppLocale()->getShortName();
        
        if($this->user->isDeveloper()) {
            $this->languages['dev'] = 'dev';
        }
        
        $this->lastVersion = $this->request->getParam('last_version');
        if (empty($this->lastVersion)) {
            $this->lastVersion = $this->whatsnew->getCurrentVersion()->getNumber();
        }
    }
}