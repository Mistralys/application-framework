<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use Application\Media\API\MediaDocumentAPIInterface;
use Application\Media\API\MediaDocumentAPITrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use AppUtils\ArrayDataCollection;

/**
 * Test stub for the {@see MediaDocumentAPIInterface}/{@see MediaDocumentAPITrait}
 * media document lookup piece: registers the `mediaID` parameter and echoes
 * the resolved record's ID back as `mediaID`.
 *
 * @package TestDriver
 * @subpackage API
 *
 * @see \AppFrameworkTests\Media\API\MediaDocumentParamsContainerTest
 */
class TestMediaDocumentMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    MediaDocumentAPIInterface,
    JSONResponseInterface
{
    use RequestRequestTrait;
    use MediaDocumentAPITrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestMediaDocument';

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function getDescription() : string
    {
        return 'Test API method stub for the media document lookup piece (MediaDocumentAPIInterface/MediaDocumentAPITrait).';
    }

    public function getRelatedMethodNames() : array
    {
        return array();
    }

    public function getVersions() : array
    {
        return array('1.0');
    }

    public function getCurrentVersion() : string
    {
        return '1.0';
    }

    public function getGroup() : APIGroupInterface
    {
        return TestDriverAPIGroup::create();
    }

    protected function init() : void
    {
        $this->manageMediaDocumentParams()->manageID()->register();
    }

    protected function collectRequestData(string $version) : void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version) : void
    {
        $response->setKey(
            MediaDocumentAPIInterface::PARAM_MEDIA_ID,
            $this->manageMediaDocumentParams()->requireValue()->getID()
        );
    }

    public function getExampleJSONResponse() : array
    {
        return array(
            MediaDocumentAPIInterface::PARAM_MEDIA_ID => 1,
        );
    }

    public function getChangelog() : array
    {
        return array();
    }

    public function getResponseKeyDescriptions() : array
    {
        return array();
    }
}
