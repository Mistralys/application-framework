<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\ErrorResponsePayload;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use Application\Locales\API\AppLocaleAPIInterface;
use Application\Locales\API\AppLocaleAPITrait;
use AppUtils\ArrayDataCollection;
use TestDriver\API\TestAppLocale\AppLocaleResponse;

/**
 * Test method for selecting an application locale when calling an API method.
 *
 * It uses a custom response class to easily access the returned data,
 * {@see AppLocaleResponse}.
 *
 * @package TestDriver
 * @subpackage API
 *
 * @see AppLocaleAPITests Matching test case
 *
 * @method AppLocaleResponse|ErrorResponsePayload processReturn()
 */
class TestAppLocaleMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseInterface,
    AppLocaleAPIInterface
{
    use AppLocaleAPITrait;
    use RequestRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestAppLocale';
    public const string KEY_TEXT = 'text';
    public const string TEXT_GB = 'Yes';
    public const string TEXT_DE = 'Ja';

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'Test method for application locales';
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
        $this->registerAppLocaleParameter();
    }

    protected function getResponseClass(): string
    {
        return AppLocaleResponse::class;
    }

    protected function collectRequestData(string $version): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $this->applyLocale();

        $response->setKey(self::KEY_TEXT, t('Yes'));
    }

    public function getExampleJSONResponse(): array
    {
        return array(
            self::KEY_TEXT => t('Yes')
        );
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }
}
