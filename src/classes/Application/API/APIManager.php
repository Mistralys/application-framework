<?php
/**
 * @package API
 * @subpackage Core
 */

declare(strict_types=1);

namespace Application\API;

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

    private ?APIUrls $adminURLs = null;

    public function adminURL() : APIUrls
    {
        if(!isset($this->adminURLs)) {
            $this->adminURLs = new APIUrls();
        }

        return $this->adminURLs;
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
        if ($methodName === null)
        {
            $method = $this->requireMethodFromRequest();
        } else {
            $method = $this->getMethodByName($methodName);
        }

        $method->process();
    }

    public function getRequestedMethodName() : ?string
    {
        $methodName = $this->request->registerParam(APIMethodInterface::REQUEST_PARAM_METHOD)->setAlnum()->getString();

        if($this->getMethodIndex()->methodExists($methodName)) {
            return $methodName;
        }

        return null;
    }

    private function requireMethodFromRequest() : APIMethodInterface
    {
        $methodName = $this->getRequestedMethodName();

        if (empty($methodName)) {
            throw new APIException(
                'No method specified',
                'The method request parameter was empty.',
                self::ERROR_NO_METHOD_SPECIFIED
            );
        }

        return $this->getMethodByName($methodName);
    }

    public function getMethodByName(string $methodName) : APIMethodInterface
    {
        return $this->loadMethod($this->getMethodIndex()->getMethodClass($methodName));
    }

    /**
     *
     * @param class-string<APIMethodInterface> $class
     * @return APIMethodInterface
     * @throws APIException
     */
    public function loadMethod(string $class): APIMethodInterface
    {
        try {
            return ClassHelper::requireObjectInstanceOf(
                APIMethodInterface::class,
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

    /**
     * Replaces all known method names in the given text with Markdown links to their documentation.
     * @param string $text
     * @return string
     */
    public function markdownifyMethodNames(string $text) : string
    {
        foreach ($this->getMethodIndex()->getMethodNames() as $methodName) {
            if(!str_contains($text, '#'.$methodName)) {
                continue;
            }

            $method = $this->getMethodByName($methodName);

            $text = str_replace(
                '#'.$methodName,
                '[' . $methodName . '](' . $method->getDocumentationURL() . ')',
                $text
            );
        }

        return $text;
    }
}
