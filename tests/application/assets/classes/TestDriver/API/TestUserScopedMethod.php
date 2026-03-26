<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\APIManager;
use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Cache\APICacheStrategyInterface;
use Application\API\Cache\Strategies\FixedDurationStrategy;
use Application\API\Cache\UserScopedCacheInterface;
use Application\API\Cache\UserScopedCacheTrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use AppUtils\ArrayDataCollection;

/**
 * User-scoped cacheable API method stub for unit tests.
 *
 * Allows the user identifier and method-specific cache parameters to be
 * injected at construction time so test cases can exercise all combinations.
 *
 * @package TestDriver
 * @subpackage API
 */
class TestUserScopedMethod
    extends BaseAPIMethod
    implements
        RequestRequestInterface,
        JSONResponseInterface,
        UserScopedCacheInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use UserScopedCacheTrait;

    public const string METHOD_NAME = 'TestUserScoped';
    public const string VERSION_1_0 = '1.0';

    private string $userIdentifier;
    private array $scopedParams;

    /**
     * @param APIManager $api
     * @param string $userIdentifier Value returned by {@see getUserCacheIdentifier()}.
     * @param array<string,mixed> $scopedParams Values returned by {@see getUserScopedCacheKeyParameters()}.
     */
    public function __construct(
        APIManager $api,
        string $userIdentifier = 'user-1',
        array $scopedParams = array()
    )
    {
        $this->userIdentifier = $userIdentifier;
        $this->scopedParams = $scopedParams;
        parent::__construct($api);
    }

    public function getUserCacheIdentifier() : string
    {
        return $this->userIdentifier;
    }

    public function getUserScopedCacheKeyParameters() : array
    {
        return $this->scopedParams;
    }

    public function getCacheStrategy() : APICacheStrategyInterface
    {
        return new FixedDurationStrategy(FixedDurationStrategy::DURATION_1_HOUR);
    }

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function getDescription() : string
    {
        return 'User-scoped cacheable stub for unit and integration testing.';
    }

    public function getRelatedMethodNames() : array
    {
        return array();
    }

    public function getVersions() : array
    {
        return array(self::VERSION_1_0);
    }

    public function getCurrentVersion() : string
    {
        return self::VERSION_1_0;
    }

    public function getGroup() : APIGroupInterface
    {
        return TestDriverAPIGroup::create();
    }

    protected function init() : void
    {
    }

    protected function collectRequestData(string $version) : void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version) : void
    {
        $response->setKey('userScope', $this->userIdentifier);
    }

    public function getExampleJSONResponse() : array
    {
        return array('userScope' => 'user-1');
    }

    public function getChangelog() : array
    {
        return array();
    }

    public function getReponseKeyDescriptions() : array
    {
        return array();
    }
}
