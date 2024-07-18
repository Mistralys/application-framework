<?php

declare(strict_types=1);

use Application\API\APIException;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;

class Application_API
{
    public const ERROR_METHOD_NOT_FOUND = 112547001;
    public const ERROR_NO_METHOD_SPECIFIED = 112547002;
    public const ERROR_INVALID_METHOD_CLASS = 112547003;
    public const ERROR_CLASS_NOT_IN_FILE = 112547004;

    protected Application_Driver $driver;
    protected Application_Request $request;
    protected static ?Application_API $instance = null;

    /**
     * @var string[]
     */
    protected array $repositories;

    protected function __construct()
    {
        $this->driver = Application_Driver::getInstance();
        $this->request = $this->driver->getRequest();

        $this->repositories = array(
            'BaseAPIMethod' => APP_INSTALL_FOLDER . '/classes/Application/API/Method',
            APP_CLASS_NAME . '_API' => APP_ROOT . '/assets/classes/' . APP_CLASS_NAME . '/API/'
        );
    }

    /**
     * @return string[]
     */
    public function getRepositories() : array
    {
        return $this->repositories;
    }

    /**
     * Returns the global instance of the API manager,
     * creating the instance as needed.
     *
     * @return Application_API
     */
    public static function getInstance() : Application_API
    {
        if (!isset(self::$instance)) {
            self::$instance = new Application_API();
        }

        return self::$instance;
    }

    public function process() : void
    {
        $name = $this->request->getParam('method');
        if (empty($name)) {
            throw new APIException(
                'No method specified',
                'The method request parameter was empty.',
                self::ERROR_NO_METHOD_SPECIFIED
            );
        }

        $this->loadMethod($name)->process();
    }

    /**
     *
     * @param string $name
     * @return BaseAPIMethod
     * @throws APIException
     */
    public function loadMethod(string $name) : BaseAPIMethod
    {
        $class = null;

        foreach ($this->repositories as $classBasename => $repository)
        {
            $file = $repository . '/' . $name . '.php';
            if (!file_exists($file)) {
                continue;
            }

            try
            {
                $searchName = $classBasename . '_' . $name;
                $class = ClassHelper::requireResolvedClass($searchName);
                break;
            }
            catch (BaseClassHelperException $e)
            {
                throw new APIException(
                    'Method class not found by name',
                    sprintf(
                        'Found method file [%s], but the expected class [%s] could not be resolved.',
                        $file,
                        $searchName
                    ),
                    self::ERROR_CLASS_NOT_IN_FILE
                );
            }
        }

        if (!$class) {
            throw new APIException(
                'Method not found',
                'The specified method [%1$s] could not be found in the available repositories.',
                self::ERROR_METHOD_NOT_FOUND
            );
        }

        try
        {
            return ClassHelper::requireObjectInstanceOf(
                BaseAPIMethod::class,
                new $class($this)
            );
        }
        catch (BaseClassHelperException $e)
        {
            throw new APIException(
                'Invalid Method class',
                'The method has to extend the Application_API_Method base class.',
                self::ERROR_INVALID_METHOD_CLASS,
                $e
            );
        }
    }
}
