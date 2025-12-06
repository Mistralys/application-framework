<?php

declare(strict_types=1);

use AppUtils\ClassHelper;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait;

class Application_Driver_AdminInfo implements OptionableInterface
{
    use OptionableTrait;
    
   /**
    * @var string
    */
    protected $sourceFolder;
    
   /**
    * @var Application_Driver
    */
    protected $driver;
    
   /**
    * @var array<string,array<string,mixed>>
    */
    protected array $areas = array();
    
   /**
    * @var Application_Admin_Skeleton[]
    */
    protected $instances;
    
   /**
    * @var Application_Admin_Area
    */
    private $areascreen;
    
    public function __construct()
    {
        $this->driver = Application_Driver::getInstance();
        $this->sourceFolder = $this->driver->getClassesFolder().'/Area';
    }
    
    public function enableSyntaxCheck(bool $enable=true) : Application_Driver_AdminInfo
    {
        $this->setOption('check-syntax', $enable);
        return $this;
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            'check-syntax' => false
        );
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function toArray() : array
    {
        return $this->areas;
    }
    
    public function analyzeFiles() : Application_Driver_AdminInfo
    {
        $checkSyntax = $this->getOption('check-syntax');
        
        $files = AppUtils\FileHelper::createFileFinder($this->sourceFolder)
        ->makeRecursive()
        ->setPathmodeRelative()
        ->stripExtensions()
        ->getAll();
        
        sort($files);
        
        foreach($files as $relpath)
        {
            $file = $this->sourceFolder.'/'.$relpath.'.php';
            
            $classInfo = \AppUtils\FileHelper::findPHPClasses($file);
            
            if(!$classInfo->hasClasses()) {
                $this->log('<span style="color:#cc0000">'.$relpath.': No class matches.</span>');
                continue;
            }
            
            $classes = $classInfo->getClasses();
            foreach($classes as $class)
            {
                $this->processClass($relpath, $class);
            }
        }
        
        return $this;
    }
    
    protected function processClass(string $relpath, \AppUtils\FileHelper_PHPClassInfo_Class $class)
    {
        $className = $class->getName();
        
        $this->log('Found class ['.$className.'] ['.$class->getDeclaration().']');
        
        // abstract class
        if($class->isAbstract()) {
            $this->log('<span style="color:#cc0000">'.$className.'@'.$relpath.': Abstract class.</span>');
            return;
        }
        
        // skip non-skeleton classes and wizard steps
        if(!$class->isSubclassOf(Application_Admin_Skeleton::class)) {
            $this->log('<span style="color:#cc0000">'.$className.'@'.$relpath.': Does not extend admin skeleton.</span>');
            return;
        }
        
        if($class->isSubclassOf(Application_Admin_Wizard_Step::class)) {
            $this->log('<span style="color:#cc0000">'.$className.'@'.$relpath.': Wizard step class.</span>');
            return;
        }
        
        $this->log(sprintf('Processing class [%s].', $className));
        
        $tokens = explode('/', $relpath);
        $area = $tokens[0];
        $checkSyntax = $this->getOption('check-syntax');
        
        if(!isset($this->areas[$area]))
        {
            if(!is_subclass_of($className, Application_Admin_Area::class)) {
                throw new Application_Exception(
                    'Not an admin area! '.$className
                );
            }
            
            /* @var $screen Application_Admin_Area */
            $screen = new $className($this->driver, false);
            
            $this->instances[$relpath] = $screen;
            
            $this->areas[$area] = array(
                'relativePath' => $relpath,
                'name' => $area,
                'type' => 'area',
                'class' => $className,
                'extends' => $class->getExtends(),
                'urlName' => $screen->getURLName(),
                'urlPath' => $screen->getURLPath(),
                'title' => $screen->getTitle(),
                'navTitle' => $screen->getNavigationTitle(),
                'defaultMode' => $screen->getDefaultMode(),
                'navGroup' => $screen->getNavigationGroup(),
                'isCore' => $screen->isCore(),
                'dependencies' => $screen->getDependencies(),
                'screens' => array()
            );
        }
        
        if(!isset($tokens[1])) {
            return;
        }
        
        $mode = $tokens[1];
        if(!isset($this->areas[$area]['screens'][$mode]))
        {
            $this->areascreen = ClassHelper::requireObjectInstanceOf(
                Application_Admin_Area::class,
                $this->instances[$this->areas[$area]['relativePath']]
            );
            
            /* @var $modeScreen Application_Admin_Area_Mode */
            $modeScreen = new $className($this->driver, $this->areascreen);
            
            $this->instances[$relpath] = $modeScreen;
            
            $this->areas[$area]['screens'][$mode] = array(
                'relativePath' => $relpath,
                'name' => $mode,
                'type' => 'mode',
                'class' => $className,
                'extends' => $class->getExtends(),
                'urlName' => $modeScreen->getURLName(),
                'urlPath' => $modeScreen->getURLPath(),
                'title' => $modeScreen->getTitle(),
                'navTitle' => $modeScreen->getNavigationTitle(),
                'defaultSubmode' => $modeScreen->getDefaultSubmode(),
                'screens' => array()
            );
        }
        
        if(!isset($tokens[2])) {
            return;
        }
        
        $submode = $tokens[2];
        if(!isset($this->areas[$area]['screens'][$mode]['screens'][$submode]))
        {
            $this->log(sprintf('Registering admin submode %s', $relpath));
            
            $modeScreen = $this->instances[$this->areas[$area]['screens'][$mode]['relativePath']];
            
            /* @var $submodeScreen Application_Admin_Area_Mode_Submode */
            $submodeScreen = new $className($this->driver, $modeScreen);
            
            $this->instances[$relpath] = $submodeScreen;
            
            $this->areas[$area]['screens'][$mode]['screens'][$submode] = array(
                'relativePath' => $relpath,
                'name' => $submode,
                'type' => 'submode',
                'class' => $className,
                'extends' => $class->getExtends(),
                'urlName' => $submodeScreen->getURLName(),
                'urlPath' => $submodeScreen->getURLPath(),
                'title' => $submodeScreen->getTitle(),
                'navTitle' => $submodeScreen->getNavigationTitle(),
                'defaultAction' => $submodeScreen->getDefaultAction(),
                'screens' => array()
            );
        }
        
        if(!isset($tokens[3])) {
            return;
        }
        
        $action = $tokens[3];
        
        $this->log(sprintf('Registering admin action %s', $relpath));
        
        $submodeScreen = $this->instances[$this->areas[$area]['screens'][$mode]['screens'][$submode]['relativePath']];
        
        $actionScreen = new $className($this->driver, $submodeScreen);
        
        $this->instances[$relpath] = $actionScreen;
        
        $this->areas[$area]['screens'][$mode]['screens'][$submode]['screens'][] = array(
            'relativePath' => $relpath,
            'name' => $action,
            'type' => 'action',
            'class' => $className,
            'extends' => $class->getExtends(),
            'urlName' => $actionScreen->getURLName(),
            'urlPath' => $actionScreen->getURLPath(),
            'title' => $actionScreen->getTitle(),
            'navTitle' => $actionScreen->getNavigationTitle(),
        );
    }
    
    protected function log(string $message)
    {
         Application::log('Driver AdminInfo | '.$message);
    }
}
