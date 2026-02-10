<?php
/**
 * @package Application
 * @subpackage API
 */

declare(strict_types=1);

namespace DBHelper\API\Methods;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Groups\APIGroupInterface;
use Application\API\Groups\FrameworkAPIGroup;
use Application\API\Traits\JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use AppUtils\ArrayDataCollection;
use AppUtils\FileHelper;

/**
 * API method that compiles information about all DBHelper collections
 * that are in use in the application.
 *
 * @package Application
 * @subpackage API
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DescribeCollectionsAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;

    public const string METHOD_NAME = 'DescribeCollections';
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

    public const string RESPONSE_KEY_COLLECTIONS = 'collections';

    protected function collectResponseData(ArrayDataCollection $response, string $version): void
    {
        $sourceFolder = $this->driver->getClassesFolder();

        $files = FileHelper::createFileFinder($sourceFolder)
            ->makeRecursive()
            ->setPathmodeRelative()
            ->stripExtensions()
            ->getMatches();

        if ($this->isSimulation()) {
            $this->log(sprintf('Root folder is [%s].', $sourceFolder));
            $this->log(sprintf('Found [%s] PHP files.', count($files)));
        }

        $collections = array();

        foreach ($files as $relativeName) {
            $path = $sourceFolder . '/' . $relativeName . '.php';

            $classes = $this->resolveCollectionClasses($path);

            foreach ($classes as $class) {
                $this->log(sprintf('Found class [%s].', $class));

                $collection = new $class();
                $collections[] = $collection->describe();
            }
        }

        $response->setKey(self::RESPONSE_KEY_COLLECTIONS, $collections);
    }

    protected function resolveCollectionClasses(string $file): array
    {
        $code = file_get_contents($file);

        $matches = array();
        preg_match_all('/([a-z0-9]*)\s*class\s+([a-z0-9_]+)+\s+extends\s+DBHelper_BaseCollection/six', $code, $matches, PREG_PATTERN_ORDER);

        if (empty($matches[0]) || empty($matches[0][0])) {
            return array();
        }

        $found = array();

        for ($i = 0, $iMax = count($matches[0]); $i < $iMax; $i++) {
            // ignore it if there is an abstract flag, it cannot be instantiated like this 
            if (stripos($matches[1][$i], 'abstract') !== false) {
                continue;
            }

            $found[] = $matches[2][$i];
        }

        return $found;
    }

    // endregion

    // region: C - Documentation

    public function getDescription(): string
    {
        return <<<'MARKDOWN'
Compiles information about all DBHelper collections that are in use in the application.
MARKDOWN;
    }

    public function getExampleJSONResponse(): array
    {
        return array();
    }

    public function getRelatedMethodNames(): array
    {
        return array();
    }

    public function getChangelog(): array
    {
        return array();
    }

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }

    // endregion
}
