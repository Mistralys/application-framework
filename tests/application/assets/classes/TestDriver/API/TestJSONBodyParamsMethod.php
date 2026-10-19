<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Parameters\Type\BooleanParameter;
use Application\API\Parameters\Type\IntegerParameter;
use Application\API\Parameters\Type\JSONParameter;
use Application\API\Parameters\Type\StringParameter;
use Application\API\Traits\JSONRequestInterface;
use Application\API\Traits\JSONRequestTrait;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use AppUtils\ArrayDataCollection;

/**
 * Test stub for the JSON-body parameter resolution feature (see
 * {@see \Application\API\Parameters\ValueSource\JSONBodyValueSource}):
 * registers one parameter of each of the non-header, non-reserved types
 * that resolve from a request's raw value, so tests can prove each type
 * resolves from the JSON body, falls back to the request (query/POST) when
 * the key is absent from the body, and that the body value wins when both
 * are present.
 *
 * NOTE: The plan's final shape for this fixture additionally declares a
 * `payload` field through `ParamTypeSelector::base64File()`. That parameter
 * type does not exist yet (it is added by a later work package) — this
 * fixture is therefore limited to the four non-base64 fields for now, and
 * is expected to gain `payload` once `Base64FileParameter` lands.
 *
 * @package TestDriver
 * @subpackage API
 *
 * @see \AppFrameworkTests\API\Parameters\JSONBodyParamResolutionTest
 * @see \AppFrameworkTests\API\JSONBodyParamsLiveHTTPTest
 */
class TestJSONBodyParamsMethod
    extends BaseAPIMethod
    implements
    JSONRequestInterface,
    JSONResponseInterface
{
    use JSONRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestJSONBodyParams';

    public const string PARAM_LABEL = 'label';
    public const string PARAM_COUNT = 'count';
    public const string PARAM_FLAG = 'flag';
    public const string PARAM_META = 'meta';

    /**
     * Declared removed (see {@see self::init()}), used by
     * {@see \AppFrameworkTests\API\RemovedParamJSONBodyTest} to prove a
     * removed name is detected whether it is sent inside the JSON body or
     * the query string (AC-19).
     */
    public const string PARAM_LEGACY_LABEL = 'legacyLabel';

    private StringParameter $labelParam;
    private IntegerParameter $countParam;
    private BooleanParameter $flagParam;
    private JSONParameter $metaParam;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'Test API method stub proving JSON-body parameter resolution, with request (query/POST) fallback, for one parameter of each non-header, non-reserved type.';
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
        $this->labelParam = $this->addParam(self::PARAM_LABEL, 'Label')
            ->string();

        $this->countParam = $this->addParam(self::PARAM_COUNT, 'Count')
            ->integer();

        $this->flagParam = $this->addParam(self::PARAM_FLAG, 'Flag')
            ->boolean();

        $this->metaParam = $this->addParam(self::PARAM_META, 'Meta')
            ->JSON();

        $this->addRemovedParam(self::PARAM_LEGACY_LABEL);
    }

    protected function collectRequestData(string $version): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        // Resolved through each parameter's own getValue(), not the raw
        // request: this is what actually exercises JSONBodyValueSource's
        // body-wins-over-request resolution the fixture exists to prove.
        $response->setKey(self::PARAM_LABEL, $this->labelParam->getValue());
        $response->setKey(self::PARAM_COUNT, $this->countParam->getValue());
        $response->setKey(self::PARAM_FLAG, $this->flagParam->getValue());
        $response->setKey(self::PARAM_META, $this->metaParam->getValue());
    }

    public function getExampleJSONResponse(): array
    {
        return array(
            self::PARAM_LABEL => 'example-label',
            self::PARAM_COUNT => 1,
            self::PARAM_FLAG => true,
            self::PARAM_META => array('key' => 'value'),
        );
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getResponseKeyDescriptions(): array
    {
        return array();
    }
}
