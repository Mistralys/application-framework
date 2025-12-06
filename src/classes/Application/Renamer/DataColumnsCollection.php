<?php

declare(strict_types=1);

namespace Application\Renamer;

use Application\AppFactory;
use AppUtils\Collections\BaseClassLoaderCollection;
use AppUtils\FileHelper\FolderInfo;

/**
 * @method DataColumnInterface[] getAll()
 * @method DataColumnInterface getByID(string $id)
 */
class DataColumnsCollection extends BaseClassLoaderCollection
{
    /**
     * @param class-string<DataColumnInterface> $class
     * @return DataColumnInterface
     */
    protected function createItemInstance(string $class): DataColumnInterface
    {
        return new $class();
    }

    /**
     * @return class-string<DataColumnInterface>
     */
    public function getInstanceOfClassName(): string
    {
        return DataColumnInterface::class;
    }

    public function isRecursive(): bool
    {
        return true;
    }

    public function getClassesFolder(): FolderInfo
    {
        return FolderInfo::factory(AppFactory::createDriver()->getClassesFolder().'/RenamerColumns');
    }

    public function getDefaultID(): string
    {
        return $this->getAutoDefault();
    }
}
