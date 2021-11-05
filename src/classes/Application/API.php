<?php

class Application_API
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
     * @var Application_API
     */
    protected static $instance;

    protected $repositories;

    protected function __construct()
    {
        $this->driver = Application_Driver::getInstance();
        $this->request = $this->driver->getRequest();

        $this->repositories = array(
            'Application_API_Method' => APP_INSTALL_FOLDER . '/classes/Application/API/Method',
            APP_CLASS_NAME . '_API' => APP_ROOT . '/assets/classes/' . APP_CLASS_NAME . '/API/'
        );
    }

    public function getRepositories()
    {
        return $this->repositories;
    }

    /**
     * Returns the global instance of the API manager,
     * creating the instance as needed.
     *
     * @return Application_API
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Application_API();
        }

        return self::$instance;
    }

    public const ERROR_METHOD_NOT_FOUND = 112547001;

    public const ERROR_NO_METHOD_SPECIFIED = 112547002;

    public const ERROR_INVALID_METHOD_CLASS = 112547003;

    public function process()
    {
        $name = $this->request->getParam('method');
        if (empty($name)) {
            throw new Application_Exception(
                'No method specified',
                'The method request parameter was empty.',
                self::ERROR_NO_METHOD_SPECIFIED
            );
        }

        $method = $this->loadMethod($name);

        $inputFormats = $method->getInputFormats();
        $defaultInputFormat = $method->getDefaultInputFormat();
        $inputFormat = $defaultInputFormat;

        $outputFormats = $method->getOutputFormats();
        $defaultOutputFormat = $method->getDefaultOutputFormat();
        $outputFormat = $defaultOutputFormat;

        // if the method has several input formats, determine
        // which one should be used.
        if (count($inputFormats) > 1) {
            $inputFormat = $this->request->getParam('input', $defaultInputFormat);
        }

        // if the method has several output formats, determine
        // which one should be used.
        if (count($outputFormats) > 1) {
            $outputFormat = $this->request->getParam('output', $defaultOutputFormat);
        }

        $method->selectInputFormat($inputFormat);
        $method->selectOutputFormat($outputFormat);
        $method->process();
    }

    /**
     *
     * @param string $name
     * @return Application_API_Method
     */
    public function loadMethod($name)
    {
        $class = null;
        foreach ($this->repositories as $classBasename => $repository) {
            $file = $repository . '/' . $name . '.php';
            if (file_exists($file)) {
                $class = $classBasename . '_' . $name;
                break;
            }
        }

        if (!$class) {
            throw new Application_Exception(
                'Method not found',
                'The specified method [%1$s] could not be found in the available repositories.',
                self::ERROR_METHOD_NOT_FOUND
            );
        }

        require_once 'Application/API/Method.php';

        Application::requireClass($class);
        $method = new $class($this);

        if (!$method instanceof Application_API_Method) {
            throw new Application_Exception(
                'Invalid Method class',
                'The method has to extend the Application_API_Method base class.',
                self::ERROR_INVALID_METHOD_CLASS
            );
        }

        return $method;
    }
}

;