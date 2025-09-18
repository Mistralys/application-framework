<?php

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Traits\JSONRequestInterface;
use Application\API\Traits\JSONRequestTrait;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use AppUtils\ArrayDataCollection;

class TestJSON2JSONMethod extends BaseAPIMethod implements JSONRequestInterface, JSONResponseInterface
{
    use JSONRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestJSON2JSONMethod';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return <<<'MARKDOWN'
A test method that accepts a JSON object and returns it as a JSON response.
MARKDOWN;

    }

    public function getVersions(): array
    {
        return array(
            '1.0'
        );
    }

    public function getCurrentVersion(): string
    {
        return '1.0';
    }

    protected function init(): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $response->setKeys($this->getRequestData()->getData());
    }
}
