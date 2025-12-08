<?php

declare(strict_types=1);

namespace Application\Campaigns\API\Methods;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use Application\Campaigns\API\CampaignAPIGroup;
use Application\Campaigns\API\CampaignAPIInterface;
use Application\Campaigns\API\CampaignAPITrait;
use AppUtils\ArrayDataCollection;

class GetCampaignsAPI extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseWithExampleInterface,
    CampaignAPIInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use JSONResponseWithExampleTrait;
    use CampaignAPITrait;

    public const string METHOD_NAME = 'GetCampaigns';
    public const string VERSION_1_0 = '1.0';
    public const string DEFAULT_VERSION = self::VERSION_1_0;
    public const array VERSIONS = array(
        self::VERSION_1_0
    );

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getVersions(): array
    {
        return self::VERSIONS;
    }

    public function getCurrentVersion(): string
    {
        return self::DEFAULT_VERSION;
    }

    // region: B - Core Methods

    protected function init(): void
    {
    }

    protected function collectRequestData(string $version): void
    {
    }

    // endregion

    // region: A - Response payload

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
    }

    // endregion

    // region: C - Documentation Methods

    public function getDescription(): string
    {
        return <<<'MARKDOWN'
Retrieves a list of all available campaigns in the system.
MARKDOWN;
    }

    public function getGroup(): APIGroupInterface
    {
        return CampaignAPIGroup::getInstance();
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getRelatedMethodNames(): array
    {
        return array(
            GetCampaignAPI::METHOD_NAME
        );
    }

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }

    // endregion
}
