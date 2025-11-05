<?php

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Traits\DryRunAPIInterface;
use Application\API\Traits\DryRunAPITrait;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use AppUtils\ArrayDataCollection;

/**
 * @see DryRunAPIInterface
 * @see DryRunAPITrait
 */
class TestDryRunMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseInterface,
    DryRunAPIInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use DryRunAPITrait;

    public const string METHOD_NAME= 'TestDryRun';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'A test API method to demonstrate dry run functionality.';
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getRelatedMethodNames(): array
    {
        return array();
    }

    public function getVersions(): array
    {
        return array('1.0');
    }

    public function getCurrentVersion(): string
    {
        return '1.0';
    }

    protected function init(): void
    {
        $this->registerDryRunParam();
    }

    protected function collectRequestData(string $version): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $response->setKey(DryRunAPIInterface::PARAM_DRY_RUN, $this->isDryRun());
    }

    public function getExampleJSONResponse(): array
    {
        $response = ArrayDataCollection::create();

        $this->collectResponseData($response, $this->getCurrentVersion());

        return $response->getData();
    }

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }
}
