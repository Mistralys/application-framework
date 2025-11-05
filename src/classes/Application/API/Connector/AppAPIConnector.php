<?php

declare(strict_types=1);

namespace Application\API\Connector;

use Application\Bootstrap\Screen\APIBootstrap;
use AppUtils\ArrayDataCollection;
use AppUtils\ClassHelper;
use Connectors;
use Connectors_Connector;
use Connectors_Exception;
use Throwable;

class AppAPIConnector extends Connectors_Connector
{
    private string $appURL;

    public function __construct(string $appURL)
    {
        $this->appURL = $appURL;
        parent::__construct();
    }

    public static function create(string $appURL) : self
    {
        return ClassHelper::requireObjectInstanceOf(
            self::class,
            Connectors::createConnector(self::class, $appURL)
        );
    }

    protected function checkRequirements(): void
    {
    }

    public function getURL(): string
    {
        return $this->appURL.'/'.APIBootstrap::DISPATCHER;
    }

    /**
     * @param string $methodName
     * @param array<string,mixed>|ArrayDataCollection $params
     * @return ArrayDataCollection
     * @throws Connectors_Exception
     * @throws Throwable
     */
    public function fetchMethodData(string $methodName, array|ArrayDataCollection $params=array()) : ArrayDataCollection
    {
        return $this->createAPIMethod()
            ->fetchJSON($methodName, ArrayDataCollection::create($params));
    }

    private ?AppAPIMethod $apiMethod = null;

    private function createAPIMethod() : AppAPIMethod
    {
        if(!isset($this->apiMethod)) {
            $this->apiMethod = ClassHelper::requireObjectInstanceOf(
                AppAPIMethod::class,
                $this->createMethod(AppAPIMethod::class)
            );
        }

        return $this->apiMethod;
    }
}
