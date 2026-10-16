<?php
/**
 * @package TestDriver
 * @subpackage API
 */

declare(strict_types=1);

namespace TestDriver\API;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Cache\APICacheStrategyInterface;
use Application\API\Cache\CacheableAPIMethodInterface;
use Application\API\Cache\CacheableAPIMethodTrait;
use Application\API\Cache\Strategies\FixedDurationStrategy;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use application\assets\classes\TestDriver\APIClasses\TestDriverAPIGroup;
use AppUtils\ArrayDataCollection;

/**
 * API method stub for testing the removed-parameter declaration and
 * rejection lifecycle step (see {@see BaseAPIMethod::addRemovedParam()}
 * and {@see BaseAPIMethod::rejectRemovedParams()}).
 *
 * Declares:
 *
 * - A required active parameter, `newName`.
 * - A removed parameter, `oldName`, with a replacement (`newName`), a
 *   `removedSince` version (`2.0`), and a note containing literal `%`
 *   and `%s` sequences — to prove the note is used verbatim and never
 *   interpreted as a format string.
 * - A removed parameter, `legacyFlag`, with no replacement, version, or
 *   note declared — to prove those parts of the composed message are
 *   optional.
 *
 * Also implements {@see CacheableAPIMethodInterface}, tracking how many
 * times {@see collectResponseData()} was invoked via {@see getCollectCount()},
 * so tests can verify that a request using a removed parameter is rejected
 * before the cache is ever read or written (i.e. never served from, or
 * recorded into, a cache entry).
 *
 * @package TestDriver
 * @subpackage API
 */
class TestRemovedParamMethod
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseInterface,
    CacheableAPIMethodInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use CacheableAPIMethodTrait;

    public const string METHOD_NAME = 'TestRemovedParam';

    public const string VERSION_1_0 = '1.0';
    public const string VERSION_2_0 = '2.0';
    public const string CURRENT_VERSION = self::VERSION_2_0;

    public const string PARAM_NEW_NAME = 'newName';
    public const string PARAM_OLD_NAME = 'oldName';
    public const string PARAM_LEGACY_FLAG = 'legacyFlag';

    public const string OLD_NAME_NOTE = 'See the migration guide for details (100% complete, format %s not evaluated).';

    public const string KEY_NEW_NAME = 'newName';
    public const string KEY_COLLECT_COUNT = 'collectCount';

    private int $collectCount = 0;

    public function getCacheStrategy() : APICacheStrategyInterface
    {
        return new FixedDurationStrategy(FixedDurationStrategy::DURATION_1_HOUR);
    }

    public function getCacheKeyParameters() : array
    {
        return array();
    }

    /**
     * Returns the number of times {@see collectResponseData()} was called.
     * A removed-parameter rejection, like a cache hit, must leave this
     * value unchanged between calls.
     */
    public function getCollectCount() : int
    {
        return $this->collectCount;
    }

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    public function getDescription(): string
    {
        return 'Test API method stub for the removed-parameter declaration and rejection lifecycle step.';
    }

    public function getRelatedMethodNames(): array
    {
        return array();
    }

    public function getVersions(): array
    {
        return array(self::VERSION_1_0, self::VERSION_2_0);
    }

    public function getCurrentVersion(): string
    {
        return self::CURRENT_VERSION;
    }

    public function getGroup(): APIGroupInterface
    {
        return TestDriverAPIGroup::create();
    }

    protected function init(): void
    {
        $this->addParam(self::PARAM_NEW_NAME, 'New Name')
            ->string()
            ->makeRequired();

        $this->addRemovedParam(self::PARAM_OLD_NAME)
            ->setReplacement(self::PARAM_NEW_NAME)
            ->setRemovedSince(self::VERSION_2_0)
            ->setNote(self::OLD_NAME_NOTE);

        $this->addRemovedParam(self::PARAM_LEGACY_FLAG);
    }

    protected function collectRequestData(string $version): void
    {
    }

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $this->collectCount++;

        $response->setKey(self::KEY_NEW_NAME, $this->getParam(self::PARAM_NEW_NAME));
        $response->setKey(self::KEY_COLLECT_COUNT, $this->collectCount);
    }

    public function getExampleJSONResponse(): array
    {
        return array(
            self::KEY_NEW_NAME => 'example-value',
            self::KEY_COLLECT_COUNT => 1
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
