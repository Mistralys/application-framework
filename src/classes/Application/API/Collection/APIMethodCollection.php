<?php
/**
 * @package API
 * @subpackage Method Collection
 */

declare(strict_types=1);

namespace Application\API\Collection;

use Application\API\APIMethodInterface;
use Application\API\APIManager;
use Application\AppFactory;
use AppUtils\Collections\BaseClassLoaderCollectionMulti;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Collection of all available API methods in the application.
 * This includes APIs from the application framework as well
 * as those provided by the application itself.
 *
 * @package API
 * @subpackage Method Collection
 *
 * @method APIMethodInterface[] getAll()
 * @method APIMethodInterface getByID(string $id)
 */
class APIMethodCollection extends BaseClassLoaderCollectionMulti
{
    private APIManager $api;

    public function __construct(APIManager $api)
    {
        $this->api = $api;
    }

    /**
     * @param class-string<APIMethodInterface> $class
     * @return APIMethodInterface
     */
    protected function createItemInstance(string $class): StringPrimaryRecordInterface
    {
        return new $class($this->api);
    }

    public function getInstanceOfClassName(): ?string
    {
        return APIMethodInterface::class;
    }

    public function getClassFolders(): array
    {
        return array(
            FolderInfo::factory(APP_INSTALL_FOLDER . '/classes/Application/API/Method'),
            FolderInfo::factory(AppFactory::createDriver()->getClassesFolder() . '/API/')
        );
    }

    public function isRecursive(): bool
    {
        return true;
    }

    public function getDefaultID(): string
    {
        return $this->getAutoDefault();
    }
}
