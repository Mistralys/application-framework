<?php

class Application_AjaxHandler
{
    /**
     * @var Application_Driver
     */
    protected $driver;

    /**
     * @var Application_Request
     */
    protected $request;

    /**
     * @var Application_User
     */
    protected $user;

   /**
    * @var Application_AjaxMethod[]
    */
    protected $methods = array();
    
    public const ERROR_CANNOT_REGISTER_CLASS_FILE = 17995001;

    public const ERROR_CLASSES_FOLDER_DOES_NOT_EXIST = 17995002;
    
    public function __construct(Application_Driver $driver)
    {
        $this->driver = $driver;
        $this->request = $driver->getRequest();
        $this->user = $driver->getUser();
        
        $this->requireMethodsFromFolder(
            $this->driver->getApplication()->getClassesFolder().'/Application/AjaxMethods',
            'Application_AjaxMethods'
        );
    }

    /**
     * @return Application_Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return Application_Request
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    public function requireMethodsFromFolder($folder, $classPrefix = null)
    {
        if (is_dir($folder)) {
            return $this->addMethodsFromFolder($folder, $classPrefix);
        }
         
        throw new Application_Exception(
            'Unknown AJAX classes folder',
            sprintf(
                'The classes folder [%1$s] not exist.',
                $folder
            ),
            self::ERROR_CLASSES_FOLDER_DOES_NOT_EXIST
        );
    }

    public function addMethodsFromFolder($folder, $classPrefix = null)
    {
        if(!is_dir($folder)) {
            return;
        }
        
        $files = AppUtils\FileHelper::createFileFinder($folder)
        ->setPathmodeAbsolute()
        ->getPHPFiles();
        
        foreach ($files as $file) {
            $this->registerClassFile($file, $classPrefix);
        }
    }

    public function registerClassFile($file, $classPrefix = null)
    {
        $methodName = pathinfo($file, PATHINFO_FILENAME);
        $className = $methodName;
        if (!empty($classPrefix)) {
            $className = $classPrefix . '_' . $className;
        }

        require_once $file;

        if (!class_exists($className)) {
            throw new Application_Exception(
                'Cannot register AJAX class file',
                sprintf(
                    'Loaded the file [%1$s] successfully, but the expected class [%2$s] was not present.',
                    $file,
                    $className
                ),
                self::ERROR_CANNOT_REGISTER_CLASS_FILE
            );
        }

        $this->methods[$methodName] = new $className($this);
    }

    public function registerMethod($methodName)
    {

    }

    public function process()
    {
        $returnFormat = $this->request->getParam('return', Application_AjaxMethod::RETURNFORMAT_JSON);
        $method = $this->getMethod();
        if(!$method) {
            // allow for fallback handlers if no method was found.
            Application_EventHandler::trigger('NoAjaxHandlerFound', array());
            
            $method = $this->getErrorMethod();
        }
        
        if (!$method->isFormatSupported($returnFormat)) {
            $this->sendError(t('Unsupported return format.'));
        }
        
        if($this->request->getParam('debug')=='yes') {
            $method->enableDebug();
        }

        $method->process($returnFormat);
    }
   
   /**
    * @return NULL|Application_AjaxMethod
    */
    public function getMethod()
    {
        $methodName = $this->request->getParam('method');
        if (isset($this->methods[$methodName])) {
            return $this->methods[$methodName];
        }
        
        return null;
    }
    
    public function getErrorMethod()
    {
        return $this->methods['NoAjaxHandlerFound'];
    }

    protected function sendError($message)
    {
        header('HTTP/1.1 500 ' . $message);
        Application::exit();
    }

    public function displayException(Exception $e)
    {
        if(isDevelMode() || ($this->user->isDeveloper() && $this->request->getBool('debug'))) 
        {
            displayError($e);
        }

        $this->sendError($e->getMessage() . ', error #' . $e->getCode());
    }
}
