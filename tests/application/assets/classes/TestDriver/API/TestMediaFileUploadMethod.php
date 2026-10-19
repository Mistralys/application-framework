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
use Application\Media\API\MediaFileUploadAPIInterface;
use Application\Media\API\MediaFileUploadAPITrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use AppUtils\ArrayDataCollection;

/**
 * Test stub for the {@see MediaFileUploadAPIInterface}/{@see MediaFileUploadAPITrait}
 * media file upload piece: registers required `fileName`/`fileContent`
 * parameters, validates the submitted file through `validateMediaFile()`,
 * and echoes the resolved file name, size and type ID.
 *
 * Exists so PHPStan has a real consumer of the trait (`trait.unused`), and as
 * a DB-free isolation harness for the upload piece.
 *
 * Covered by {@see \AppFrameworkTests\Media\API\MediaFileUploadPieceTest}.
 *
 * @package TestDriver
 * @subpackage API
 */
class TestMediaFileUploadMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    MediaFileUploadAPIInterface,
    JSONResponseInterface
{
    use RequestRequestTrait;
    use MediaFileUploadAPITrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestMediaFileUpload';
    public const string KEY_TYPE_ID = 'typeID';
    public const string KEY_FILE_SIZE = 'fileSize';

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function getDescription() : string
    {
        return 'Test API method stub for the media file upload piece (MediaFileUploadAPIInterface/MediaFileUploadAPITrait).';
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
        $this->registerMediaFileParams(true);
    }

    protected function collectRequestData(string $version) : void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version) : void
    {
        $typeID = $this->validateMediaFile();
        $content = $this->getMediaFileContent();

        $response->setKey(self::KEY_TYPE_ID, $typeID);
        $response->setKey(MediaFileUploadAPIInterface::PARAM_FILE_NAME, $content?->getFileName());
        $response->setKey(self::KEY_FILE_SIZE, $content?->getSize());
    }

    public function getExampleJSONResponse() : array
    {
        return array(
            self::KEY_TYPE_ID => 'Image',
            MediaFileUploadAPIInterface::PARAM_FILE_NAME => 'example.png',
            self::KEY_FILE_SIZE => 1024,
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
