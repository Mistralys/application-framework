<?php
/**
 * @package API
 * @subpackage Versioning
 */

declare(strict_types=1);

namespace Application\API\Versioning;

use AppUtils\ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollection;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Collection of all API versions available for a specific API method
 * that uses versioning via {@see VersionedAPITrait}. Uses ClassHelper
 * class loading to dynamically load all version classes in the
 * version folder of the method.
 *
 * @package API
 * @subpackage Versioning
 *
 * @method APIVersionInterface[] getAll()
 * @method APIVersionInterface getByID(string $id)
 * @method APIVersionInterface getDefault()
 */
class VersionCollection extends BaseClassLoaderCollection
{
    private VersionedAPIInterface $method;

    public function __construct(VersionedAPIInterface $method)
    {
        $this->method = $method;
    }

    protected function createItemInstance(string $class): ?StringPrimaryRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            APIVersionInterface::class,
            new $class($this->method)
        );
    }

    public function getInstanceOfClassName(): string
    {
        return APIVersionInterface::class;
    }

    public function isRecursive(): bool
    {
        return false;
    }

    public function getClassesFolder(): FolderInfo
    {
        return $this->method->getVersionFolder();
    }

    public function getDefaultID(): string
    {
        return $this->method->getCurrentVersion();
    }
}
