<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Parameters\Type\StringParameter;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use AppUtils\ArrayDataCollection;

/**
 * API method stub for testing {@see \Application\API\Parameters\Rules\Type\OnlyIfOtherValueInRule}
 * end to end, through {@see \Application\API\Parameters\Rules\RuleTypeSelector::onlyIfOtherValueIn()}.
 *
 * Declares:
 *
 * - A `mode` parameter, restricted to `a`/`b` via `validateByEnum()`.
 * - An `aOnlyOption` parameter, only accepted when `mode` is `a` — enforced
 *   by the rule under test, registered with
 *   {@see \Application\API\Parameters\Rules\RuleInterface::VALIDATION_PARAM_NOT_APPLICABLE}
 *   (`183602`) as the rejection code.
 *
 * @package TestDriver
 * @subpackage API
 */
class TestOnlyIfOtherValueInMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'TestOnlyIfOtherValueIn';

    public const string PARAM_MODE = 'mode';
    public const string PARAM_A_ONLY_OPTION = 'aOnlyOption';

    public const string MODE_A = 'a';
    public const string MODE_B = 'b';

    public const string KEY_MODE = 'mode';
    public const string KEY_A_ONLY_OPTION = 'aOnlyOption';

    private ?StringParameter $modeParam = null;
    private ?StringParameter $aOnlyOptionParam = null;

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'Test API method stub for the OnlyIfOtherValueInRule validation rule.';
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
        $this->modeParam = $this->manageParams()
            ->addParam(self::PARAM_MODE, 'Mode')
            ->string()
            ->makeRequired();

        $this->modeParam
            ->setDescription('Either "a" or "b".')
            ->validateByEnum(array(self::MODE_A, self::MODE_B));

        $this->aOnlyOptionParam = $this->manageParams()
            ->addParam(self::PARAM_A_ONLY_OPTION, 'A-only option')
            ->string();

        $this->aOnlyOptionParam->setDescription('Only accepted when mode is "a".');

        $this->manageParams()->addRule()->onlyIfOtherValueIn(
            'aOnlyOption is only accepted when mode is "a"',
            $this->aOnlyOptionParam,
            $this->modeParam,
            array(self::MODE_A)
        );
    }

    protected function collectRequestData(string $version): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $response->setKey(self::KEY_MODE, $this->getParam(self::PARAM_MODE));
        $response->setKey(self::KEY_A_ONLY_OPTION, $this->getParam(self::PARAM_A_ONLY_OPTION));
    }

    public function getExampleJSONResponse(): array
    {
        return array(
            self::KEY_MODE => self::MODE_A,
            self::KEY_A_ONLY_OPTION => 'example-value'
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
