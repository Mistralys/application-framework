<?php
/**
 * @package Application
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Admin\Index\API\Methods;

use Application\Admin\Index\AdminScreenIndex;
use Application\Admin\Index\API\DescribeAdminAreasAPIInterface;
use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Groups\FrameworkAPIGroup;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use AppUtils\ArrayDataCollection;

/**
 * API method that compiles information about all administration areas
 * available in the application.
 *
 * @package Application
 * @subpackage API
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see BaseAPIMethod
 */
class DescribeAdminAreasAPI
    extends BaseAPIMethod
    implements
    JSONResponseInterface,
    RequestRequestInterface,
    DescribeAdminAreasAPIInterface
{
    use JSONResponseTrait;
    use RequestRequestTrait;

    public const string METHOD_NAME = 'DescribeAdminAreas';
    public const string VERSION_1_0 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1_0;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getVersions(): array
    {
        return array(
            self::VERSION_1_0
        );
    }

    public function getCurrentVersion(): string
    {
        return self::CURRENT_VERSION;
    }

    public function getGroup(): APIGroupInterface
    {
        return FrameworkAPIGroup::create();
    }

    // region: B - Setup

    protected function init(): void
    {
    }

    protected function collectRequestData(string $version): void
    {
    }

    // endregion

    // region: A - Payload

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $response->setKey(DescribeAdminAreasAPIInterface::KEY_ROOT_SCREENS, AdminScreenIndex::getInstance()->getTree());
    }

    // endregion

    // region: C - Documentation

    public function getDescription(): string
    {
        return <<<'MARKDOWN'
Compiles information about all administration areas available in the application,
in the form of a tree with root areas and subscreen structure.
MARKDOWN;
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }

    public function getExampleJSONResponse(): array
    {
        return array();
    }

    public function getRelatedMethodNames(): array
    {
        return array();
    }

    // endregion
}
