<?php
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;

/**
 * File containing the {@link Application_API_Method_DescribeCollections} class.
 * 
 * @package Application
 * @subpackage API
 * @see Application_API_Method_DescribeCollections
 */

/**
 * API method that compiles information about all DBHelper collections
 * that are in use in the application.
 * 
 * @package Application
 * @subpackage API
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_API_Method
 */
class Application_API_Method_DescribeCollections extends Application_API_Method
{
    public function getDefaultInputFormat()
    {
        return 'json';
    }

    public function getDefaultOutputFormat()
    {
        return 'json';
    }

    public function getVersions()
    {
        return array(
            '1.0.0'
        );
    }

    public function getCurrentVersion()
    {
        return '1.0.0';
    }

    protected function configure()
    {        
    }
    
    public function input_json()
    {
        
    }
    
    public function output_json()
    {
        $sourceFolder = $this->driver->getClassesFolder();
        
        $files = AppUtils\FileHelper::createFileFinder($sourceFolder)
        ->makeRecursive()
        ->setPathmodeRelative()
        ->stripExtensions()
        ->getAll();
        
        if($this->isSimulation()) {
            $this->log(sprintf('Root folder is [%s].', $sourceFolder));
            $this->log(sprintf('Found [%s] PHP files.', count($files)));
        }
        
        $collections = array();
        
        foreach($files as $relativeName) 
        {
            $path = $sourceFolder.'/'.$relativeName.'.php';
            
            $classes = $this->resolveCollectionClasses($path);
            
            foreach($classes as $class)
            {
                $this->log(sprintf('Found class [%s].', $class));
                
                $collection = new $class();
                $collections[] = $collection->describe();
            }
        }
        
        $this->sendJSONResponse($collections);
    }
    
    protected function resolveCollectionClasses(string $file) : array
    {
        $code = file_get_contents($file);
        
        $matches = array();
        preg_match_all('/([a-z0-9]*)\s*class\s+([a-z0-9_]+)+\s+extends\s+DBHelper_BaseCollection/six', $code, $matches, PREG_PATTERN_ORDER);
        
        if(empty($matches[0]) || empty($matches[0][0]))
        {
            return array();
        }

        $found = array();
        
        for ($i = 0; $i < count($matches[0]); $i++)
        {
            // ignore it if there is an abstract flag, it cannot be instantiated like this 
            if(stristr($matches[1][$i], 'abstract'))
            {
                continue;
            }
          
            $found[] = $matches[2][$i];
        }
        
        return $found;
    }
}
