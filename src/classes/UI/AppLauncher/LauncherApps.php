<?php

declare(strict_types=1);

namespace UI\AppLauncher;

use Application\AppFactory;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollectionMulti;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

class LauncherApps extends BaseClassLoaderCollectionMulti
{
    public const DRIVER_FOLDER_NAME = 'AppLauncher';

    protected function createItemInstance(string $class): StringPrimaryRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            LauncherAppInterface::class,
            new $class()
        );
    }

    public function getInstanceOfClassName(): ?string
    {
        return LauncherAppInterface::class;
    }

    public function getClassFolders(): array
    {
        return array(AppFactory::createDriver()->getClassesFolder().'/'.self::DRIVER_FOLDER_NAME);
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
