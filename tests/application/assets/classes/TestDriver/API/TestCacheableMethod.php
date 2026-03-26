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
use Application\API\Cache\CacheableAPIMethodInterface;
use Application\API\Cache\CacheableAPIMethodTrait;
use Application\API\Cache\Strategies\FixedDurationStrategy;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use AppUtils\ArrayDataCollection;

/**
 * Cacheable API method stub for unit and integration tests.
 *
 * Tracks how many times {@see collectResponseData()} was invoked via
 * {@see getCollectCount()}, so tests can verify whether a response came
 * from cache (count unchanged) or was freshly computed (count incremented).
 *
 * @package TestDriver
 * @subpackage API
 */
class TestCacheableMethod
    extends BaseAPIMethod
    implements
        RequestRequestInterface,
        JSONResponseInterface,
        CacheableAPIMethodInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use CacheableAPIMethodTrait;

    public const string METHOD_NAME = 'TestCacheable';
    public const string KEY_COLLECT_COUNT = 'collectCount';
    public const string VERSION_1_0 = '1.0';

    private int $ttl;
    private array $cacheParams;
    private int $collectCount = 0;

    /**
     * @param APIManager $api
     * @param int $ttl TTL in seconds for the FixedDurationStrategy (default: 1 hour).
     * @param array<string,mixed> $cacheParams Parameters to include in the cache key.
     */
    public function __construct(
        APIManager $api,
        int $ttl = FixedDurationStrategy::DURATION_1_HOUR,
        array $cacheParams = array()
    )
    {
        $this->ttl = $ttl;
        $this->cacheParams = $cacheParams;
        parent::__construct($api);
    }

    public function getCacheStrategy() : APICacheStrategyInterface
    {
        return new FixedDurationStrategy($this->ttl);
    }

    public function getCacheKeyParameters() : array
    {
        return $this->cacheParams;
    }

    /**
     * Returns the number of times {@see collectResponseData()} was called.
     * A cache hit leaves this value unchanged between calls to processReturn().
     */
    public function getCollectCount() : int
    {
        return $this->collectCount;
    }

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function getDescription() : string
    {
        return 'Cacheable stub API method for unit and integration testing.';
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
        $this->collectCount++;
        $response->setKey(self::KEY_COLLECT_COUNT, $this->collectCount);
    }

    public function getExampleJSONResponse() : array
    {
        return array(self::KEY_COLLECT_COUNT => 1);
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
