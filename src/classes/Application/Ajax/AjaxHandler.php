<?php

declare(strict_types=1);

use Application\Ajax\AjaxException;
use Application\Ajax\AjaxMethodInterface;
use Application\AjaxMethods\NoAjaxHandlerFoundMethod;
use Application\AppFactory;
use Application\Application;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringableInterface;

class Application_AjaxHandler
{
    public const int ERROR_CANNOT_REGISTER_CLASS_FILE = 17995001;
    public const int ERROR_CLASSES_FOLDER_DOES_NOT_EXIST = 17995002;
    public const int ERROR_NO_SUCH_METHOD = 17995003;
    public const string REQUEST_PARAM_METHOD = 'method';

    protected Application_Driver $driver;
    protected Application_Request $request;
    protected Application_User $user;

   /**
    * @var array<string,Application_AjaxMethod>
    */
    protected array $methods = array();
    
    public function __construct(Application_Driver $driver)
    {
        $this->driver = $driver;
        $this->request = $driver->getRequest();
        $this->user = $driver->getUser();
        
        $this->requireMethodsFromFolder(FolderInfo::factory($this->driver->getApplication()->getClassesFolder().'/Application/AjaxMethods'));

        foreach(AppFactory::createFoldersManager()->choose()->AJAX()->resolveFolders() as $folder) {
            $this->addMethodsFromFolder($folder);
        }
    }

    public function getDriver() : Application_Driver
    {
        return $this->driver;
    }

    public function getRequest() : Application_Request
    {
        return $this->request;
    }
    
    public function requireMethodsFromFolder(FolderInfo $folder) : void
    {
        if ($folder->exists()) {
            $this->addMethodsFromFolder($folder);
            return;
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

    public function addMethodsFromFolder(FolderInfo $folder) : void
    {
        if(!$folder->exists()) {
            return;
        }

        $classes = AppFactory::findClassesInFolder($folder, true, Application_AjaxMethod::class);

        foreach ($classes as $class) {
            $this->registerClass($class);
        }

        ksort($this->methods);
    }

    public function registerClass(string $class) : void
    {
        if (!class_exists($class)) {
            throw new Application_Exception(
                'Cannot register AJAX class file',
                sprintf(
                    'The class [%1$s] does not exist.',
                    $class
                ),
                self::ERROR_CANNOT_REGISTER_CLASS_FILE
            );
        }

        $method = ClassHelper::requireObjectInstanceOf(
            Application_AjaxMethod::class,
            new $class($this)
        );

        $this->methods[$method->getMethodName()] = $method;
    }

    public function resolveReturnFormat() : string
    {
        $explicit = strtolower($this->request
            ->registerParam('returnFormat')
            ->setAlnum()
            ->getString(AjaxMethodInterface::RETURNFORMAT_JSON));

        foreach(AjaxMethodInterface::RETURN_FORMATS as $format) {
            if(strtolower($format) === $explicit) {
                return $format;
            }
        }

        return AjaxMethodInterface::RETURNFORMAT_JSON;
    }

    public function process() : void
    {
        $returnFormat = $this->resolveReturnFormat();

        $method = $this->getMethod();
        if(!$method) {
            // allow for fallback handlers if no method was found.
            Application_EventHandler::trigger('NoAjaxHandlerFound', array());
            
            $method = $this->getErrorMethod();
        }
        
        if (!$method->isFormatSupported($returnFormat)) {
            $this->sendError(t('Unsupported return format.'));
        }
        
        if($this->request->getBool('debug')) {
            $method->enableDebug();
        }

        $method->process($returnFormat);
    }
   
    public function getMethod() : ?Application_AjaxMethod
    {
        $methodName = $this->request->getParam(self::REQUEST_PARAM_METHOD);

        if (isset($this->methods[$methodName])) {
            return $this->methods[$methodName];
        }
        
        return null;
    }
    
    public function getErrorMethod() : Application_AjaxMethod
    {
        return $this->requireMethodByName(NoAjaxHandlerFoundMethod::METHOD_NAME);
    }

    /**
     * @return string[]
     */
    public function getMethodNames() : array
    {
        return array_keys($this->methods);
    }

    public function getMethodByName(string $methodName) : ?Application_AjaxMethod
    {
        return $this->methods[$methodName] ?? null;
    }

    public function requireMethodByName(string $methodName) : Application_AjaxMethod
    {
        $method = $this->getMethodByName($methodName);

        if($method !== null) {
            return $method;
        }

        throw new AjaxException(
            'Unknown AJAX method',
            sprintf(
                'The method [%1$s] does not exist. '.PHP_EOL.
                'Known methods are: '.PHP_EOL.
                '- %2$s',
                $methodName,
                implode(PHP_EOL.'- ', $this->getMethodNames())
            ),
            self::ERROR_NO_SUCH_METHOD
        );
    }

    /**
     * @param string|int|float|StringableInterface|NULL $message
     * @return never
     */
    protected function sendError($message) : never
    {
        $message = toString($message);
        if(empty($message)) {
            $message = 'Unknown error';
        }

        header('HTTP/1.1 500 ' . $message);
        Application::exit();
    }

    /**
     * @param Throwable $e
     * @return never
     * @throws ConvertHelper_Exception
     */
    public function displayException(Throwable $e) : never
    {
        if(isDevelMode() || ($this->user->isDeveloper() && $this->request->getBool('debug'))) 
        {
            displayError($e);
        }

        $this->sendError($e->getMessage() . ', error #' . $e->getCode());
    }
}
