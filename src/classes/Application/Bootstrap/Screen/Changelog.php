<?php

declare(strict_types=1);

class Application_Bootstrap_Screen_Changelog extends Application_Bootstrap_Screen
{
   /**
    * @var string
    */
    protected string $langID = 'dev';
    
    public function getDispatcher()
    {
        return 'changelog.php';
    }
    
    protected function _boot()
    {
        $this->enableScriptMode();
        $this->disableAuthentication();
        $this->createEnvironment();
        
        $versions = Application_Driver::createWhatsnew()->getVersionsByLanguage($this->langID);
        
        header('Content-Type:text/plain; encoding=utf-8');
        
        $separator = str_repeat('-', 65).PHP_EOL;

        foreach($versions as $version) 
        {
            $lang = $version->getLanguage($this->langID);
            
            echo $separator;
            echo 'V'.$version->getNumber().PHP_EOL;
            echo $separator;
            echo PHP_EOL;
            
            $categories = $lang->getCategories();
            
            foreach($categories as $category) 
            {
                $entries = $category->getItems();
                $catLabel = $category->getLabel();
                
                foreach($entries as $entry) 
                {
                    echo '- '.$catLabel;
                    
                    if($entry->hasIssue()) {
                        echo ' - '.$entry->getIssue();
                    }
                 
                    $text = $entry->getPlainText();
                    
                    echo ': '.$text;
                    
                    if($entry->hasAuthor()) {
                        echo ' ('.$entry->getAuthor().')';
                    }
                    
                    echo PHP_EOL;
                }
            }
            
            echo PHP_EOL;
        }
        
        Application::exit('Shown the changelog.');
    }
}
