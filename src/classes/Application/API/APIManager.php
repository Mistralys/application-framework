<?php
/**
 * @package API
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Collection\APIMethodCollection;
use Application\API\Collection\APIMethodIndex;
use Application_Driver;
use Application_Request;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;

/**
 * Main API manager class that is used as endpoint to
 * process API requests.
 *
 * ## Usage
 *
 * - To process an API request, call the {@see self::process()} method.
 * - To load a specific API method class, use {@see self::loadMethod()}.
 * - To access available API methods, use {@see self::getMethodCollection()}.
 *
 * @package API
 * @subpackage Core
 */
class APIManager
{
    public const int ERROR_METHOD_NOT_FOUND = 112547001;
    public const int ERROR_NO_METHOD_SPECIFIED = 112547002;
    public const int ERROR_INVALID_METHOD_CLASS = 112547003;

    protected Application_Driver $driver;
    protected Application_Request $request;
    protected static ?APIManager $instance = null;

    /**
     * @var string[]
     */
    protected array $repositories;
    private APIMethodCollection $collection;

    protected function __construct()
    {
        $this->driver = Application_Driver::getInstance();
        $this->request = $this->driver->getRequest();
        $this->collection = new APIMethodCollection($this);
    }

    /**
     * Returns the global instance of the API manager,
     * creating the instance as needed.
     *
     * @return APIManager
     */
    public static function getInstance(): APIManager
    {
        if (!isset(self::$instance)) {
            self::$instance = new APIManager();
        }

        return self::$instance;
    }

    public function getMethodCollection(): APIMethodCollection
    {
        return $this->collection;
    }

    private ?APIMethodIndex $methodIndex = null;

    public function getMethodIndex(): APIMethodIndex
    {
        if (!isset($this->methodIndex)) {
            $this->methodIndex = new APIMethodIndex($this);
        }

        return $this->methodIndex;
    }

    public function process(?string $methodName = null): void
    {
        if ($methodName === null) {
            $methodName = $this->request->registerParam(APIMethodInterface::REQUEST_PARAM_METHOD)->setAlnum()->getString();
        }

        if (empty($methodName)) {
            throw new APIException(
                'No method specified',
                'The method request parameter was empty.',
                self::ERROR_NO_METHOD_SPECIFIED
            );
        }

        $index = $this->getMethodIndex();

        if (!$index->methodExists($methodName)) {
            throw new APIException(
                'Method not found',
                sprintf(
                    'The specified method [%s] could not be found in the method index. ' . PHP_EOL .
                    'These are the known API methods: ' . PHP_EOL .
                    '- %s',
                    $methodName,
                    implode(PHP_EOL . '- ', $index->getMethodNames())
                ),
                self::ERROR_METHOD_NOT_FOUND
            );
        }

        $this->loadMethod($index->getMethodClass($methodName))->process();
    }

    /**
     *
     * @param class-string<APIMethodInterface> $class
     * @return BaseAPIMethod
     * @throws APIException
     */
    public function loadMethod(string $class): BaseAPIMethod
    {
        try {
            return ClassHelper::requireObjectInstanceOf(
                BaseAPIMethod::class,
                new $class($this)
            );
        } catch (BaseClassHelperException $e) {
            throw new APIException(
                'Invalid API method class',
                sprintf(
                    'The method has to extend the [%s] interface. ' . PHP_EOL .
                    'The class [%s] does not. ' . PHP_EOL .
                    'One possible reason is that the API method index is outdated (for example after refactorings). ' . PHP_EOL .
                    'Try clearing the method index to verify (via the Composer or UI cache control).',
                    APIMethodInterface::class,
                    $class
                ),
                self::ERROR_INVALID_METHOD_CLASS,
                $e
            );
        }
    }
}
