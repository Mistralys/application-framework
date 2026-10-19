<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\PaginatedAPIInterface;
use Application\API\Traits\PaginatedAPITrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use AppUtils\ArrayDataCollection;

/**
 * Test stub for the {@see PaginatedAPIInterface}/{@see PaginatedAPITrait}
 * pagination piece: registers pagination with a default limit of 10 and a
 * maximum of 25, and echoes back the resolved `limit`/`offset` plus a fixed
 * synthetic `totalCount` of 42, through the standard pagination response
 * keys.
 *
 * @package TestDriver
 * @subpackage API
 *
 * @see \AppFrameworkTests\API\Traits\PaginatedAPITraitTest
 */
class TestPaginatedMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    PaginatedAPIInterface,
    JSONResponseInterface
{
    use RequestRequestTrait;
    use PaginatedAPITrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestPaginated';
    public const int SYNTHETIC_TOTAL_COUNT = 42;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'Test API method stub for the pagination piece (PaginatedAPIInterface/PaginatedAPITrait).';
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

    public function getGroup(): APIGroupInterface
    {
        return TestDriverAPIGroup::create();
    }

    protected function init(): void
    {
        $this->registerPaginationParams(10, 25);
    }

    protected function collectRequestData(string $version): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $this->addPaginationResponseKeys($response, self::SYNTHETIC_TOTAL_COUNT);
    }

    public function getExampleJSONResponse(): array
    {
        return array(
            PaginatedAPIInterface::KEY_LIMIT => 10,
            PaginatedAPIInterface::KEY_OFFSET => 0,
            PaginatedAPIInterface::KEY_TOTAL_COUNT => self::SYNTHETIC_TOTAL_COUNT,
        );
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getResponseKeyDescriptions(): array
    {
        return $this->getPaginationKeyDescriptions();
    }
}
