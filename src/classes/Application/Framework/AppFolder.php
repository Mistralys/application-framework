<?php

declare(strict_types=1);

namespace Application\Framework;

use AdminException;
use Application\AppFactory;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\Interfaces\StringableInterface;
use Mistralys\AppFramework\AppFramework;

class AppFolder implements StringableInterface
{
    public const string TYPE_DRIVER_CLASSES = 'driver-classes';
    public const string TYPE_FRAMEWORK_CLASSES = 'framework-classes';
    public const string TYPE_DRIVER_ROOT = 'driver-root';
    public const string TYPE_UNKNOWN = 'unknown';

    private string $folderPath;
    private string $type = self::TYPE_UNKNOWN;
    private string $relative = '';

    public function __construct(FolderInfo|string $targetFolder)
    {
        $this->folderPath = FileHelper::resolvePathDots((string)$targetFolder);

        $this->detectType();
    }

    public static function create(FolderInfo|string $targetFolder) : self
    {
        return new self($targetFolder);
    }

    public function isValid() : bool
    {
        return $this->type !== self::TYPE_UNKNOWN;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getRelativePath() : string
    {
        return $this->relative;
    }

    public function getIdentifier() : string
    {
        return $this->type.':'.$this->relative;
    }

    private function detectType() : void
    {
        $driverClasses = $this->getDriverClassesFolder();
        if(str_starts_with($this->folderPath, $driverClasses)) {
            $this->makeType(self::TYPE_DRIVER_CLASSES, $driverClasses);
            return;
        }

        $frameworkClasses = $this->getFrameworkClassesFolder();
        if(str_starts_with($this->folderPath, $frameworkClasses)) {
            $this->makeType(self::TYPE_FRAMEWORK_CLASSES, $frameworkClasses);
            return;
        }

        $driverRoot = $this->getDriverFolder();
        if(str_starts_with($this->folderPath, $driverRoot)) {
            $this->makeType(self::TYPE_DRIVER_ROOT, $driverRoot);
        }
    }

    private function makeType(string $typeID, string $relativeTo) : void
    {
        $this->type = $typeID;
        $this->relative = FileHelper::relativizePath($this->folderPath, $relativeTo);
    }

    private static ?string $driverClassesFolder = null;

    public function getDriverClassesFolder() : string
    {
        if(!isset(self::$driverClassesFolder)) {
            self::$driverClassesFolder = FileHelper::resolvePathDots(AppFactory::createDriver()->getClassesFolder());
        }

        return self::$driverClassesFolder;
    }

    private static ?string $driverRootFolder = null;

    public function getDriverFolder() : string
    {
        if(!isset(self::$driverRootFolder)) {
            self::$driverRootFolder = FileHelper::resolvePathDots(AppFactory::createDriver()->getRootFolder());
        }

        return self::$driverRootFolder;
    }

    private static ?string $frameworkRootFolder = null;

    public function getFrameworkClassesFolder() : string
    {
        if(!isset(self::$frameworkRootFolder)) {
            self::$frameworkRootFolder = FileHelper::resolvePathDots(AppFramework::getInstance()->getClassesFolder());
        }

        return self::$frameworkRootFolder;
    }

    public function requireValid() : self
    {
        if($this->isValid()) {
            return $this;
        }

        throw new AdminException(
            'Invalid application source folder',
            sprintf(
                'The folder [%s] is not a recognized driver or framework classes folder',
                $this->folderPath
            ),
            AdminException::ERROR_INVALID_APP_SOURCE_FOLDER
        );
    }

    public function getTypeLabel() : string
    {
        return match ($this->type) {
            self::TYPE_DRIVER_CLASSES => t('Driver Classes'),
            self::TYPE_FRAMEWORK_CLASSES => t('Framework Classes'),
            self::TYPE_DRIVER_ROOT => t('Driver Root'),
            default => t('Unknown'),
        };
    }

    public function __toString(): string
    {
        return '('.$this->getTypeLabel().') '.$this->getRelativePath();
    }

    public function isFrameworkClasses() : bool
    {
        return $this->type === self::TYPE_FRAMEWORK_CLASSES;
    }

    public function isDriverClasses() : bool
    {
        return $this->type === self::TYPE_DRIVER_CLASSES;
    }

    public function isDriverRoot() : bool
    {
        return $this->type === self::TYPE_DRIVER_ROOT;
    }

    public function isDriver() : bool
    {
        return $this->isDriverClasses() || $this->isDriverRoot();
    }

    public function isFramework() : bool
    {
        return $this->isFrameworkClasses();
    }
}
