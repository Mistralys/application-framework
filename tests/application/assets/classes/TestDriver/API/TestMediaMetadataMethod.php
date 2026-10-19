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
use Application\Media\API\MediaMetadataAPIInterface;
use Application\Media\API\MediaMetadataAPITrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use AppUtils\ArrayDataCollection;

/**
 * Test stub for the {@see MediaMetadataAPIInterface}/{@see MediaMetadataAPITrait}
 * media metadata piece: registers non-clearable `name`/`description`/`keywords`
 * parameters, runs the HTML validation rule, and echoes the resolved values
 * plus `resolveMediaName()`'s result.
 *
 * Exists so PHPStan has a real consumer of the trait (`trait.unused`), and as
 * a DB-free isolation harness for the metadata piece.
 *
 * @package TestDriver
 * @subpackage API
 */
class TestMediaMetadataMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    MediaMetadataAPIInterface,
    JSONResponseInterface
{
    use RequestRequestTrait;
    use MediaMetadataAPITrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestMediaMetadata';
    public const string DEFAULT_NAME = 'synthetic-default-name';

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function getDescription() : string
    {
        return 'Test API method stub for the media metadata piece (MediaMetadataAPIInterface/MediaMetadataAPITrait).';
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
        $this->registerMediaMetadataParams(false);
    }

    protected function collectRequestData(string $version) : void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version) : void
    {
        $this->validateMediaMetadata();

        $response->setKey(MediaMetadataAPIInterface::PARAM_NAME, $this->resolveMediaName(self::DEFAULT_NAME));
        $response->setKey(MediaMetadataAPIInterface::PARAM_DESCRIPTION, $this->getMediaDescription());
        $response->setKey(MediaMetadataAPIInterface::PARAM_KEYWORDS, $this->getMediaKeywords());
    }

    public function getExampleJSONResponse() : array
    {
        return array(
            MediaMetadataAPIInterface::PARAM_NAME => self::DEFAULT_NAME,
            MediaMetadataAPIInterface::PARAM_DESCRIPTION => '',
            MediaMetadataAPIInterface::PARAM_KEYWORDS => '',
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
