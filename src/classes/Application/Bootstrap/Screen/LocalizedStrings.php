<?php

class Application_Bootstrap_Screen_LocalizedStrings extends Application_Bootstrap_Screen
{
    public function getDispatcher() : string
    {
        return 'themes/default/js/localized_strings.js.php';
    }
    
    protected $driverVersion;
    
    protected function _boot() : void
    {
        $this->createEnvironment();
        
        $this->driverVersion = $this->driver->getVersion();
        
        $js = null;
        if($this->isCacheEnabled()) 
        {
            $cache = $this->driver->getCache('localized_strings_compiled');
            if(!empty($cache)) {
                $js = $cache;
            }
        }
        
        if(empty($js)) {
            $js = $this->generateJavascript();
        }
        
        displayJS($js);
    }
    
    protected function generateJavascript()
    {
        $scanner = \AppLocalize\Localization::createScanner();
        $scanner->load();
        
        $hashes = $scanner->getCollection()->getHashesByLanguageID('Javascript');
        
        $tokens = array();
        foreach($hashes as $hash) 
        {
            $text = $hash->getTranslatedText();

            if(empty($text)) {
                continue;
            }
            
            $tokens[] = sprintf(
                "a('%s',%s)",
                $hash->getHash(),
                json_encode($text)
            );
        }
        
        if(empty($tokens))
        {
            return '/* No strings found. */';
        }

        $content =
        '/**'.PHP_EOL.
        ' * @generated '.date('Y-m-d H:i:s').PHP_EOL.
        ' * @version: '.$this->driverVersion.PHP_EOL.
        ' */'.PHP_EOL.
        'StringsRegistry.'.implode('.', $tokens).';';
        
        $this->driver->setCache('localized_strings_compiled', $content);
        $this->driver->setCache('localized_strings_version', $this->driverVersion);
        
        return $content;
    }
    
    protected function isCacheEnabled()
    {
        $cachedVersion = $this->driver->getCache('localized_strings_version');
        
        if($cachedVersion != $this->driverVersion) {
            return false;
        }
        
        if($this->driver->getRequest()->getBool('refresh')) {
            return false;
        }
        
        return true;
    }
}