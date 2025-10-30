<?php
/**
 * @package Application
 * @subpackage User
 */

declare(strict_types=1);

namespace Application\User\Roles;

use Application\User\UserException;
use Application_User_Rights;
use AppUtils\ClassHelper;
use AppUtils\Collections\BaseStringPrimaryCollection;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;

/**
 * Handles the user roles that can be simulated during
 * local development.
 *
 * The individual roles are loaded dynamically from the
 * <code>Role</code> directory.
 *
 * @package Application
 * @subpackage User
 *
 * @method BaseRole getByID(string $id)
 * @method BaseRole[] getAll()
 * @property array<string,BaseRole> $items
 */
class RoleCollection extends BaseStringPrimaryCollection
{
    public const ERROR_ROLES_NOT_INITIALIZED = 159401;
    private Application_User_Rights $rightsManager;

    private static ?self $instance = null;

    public function __construct(Application_User_Rights $rightsManager)
    {
        $this->rightsManager = $rightsManager;

        self::$instance = $this;
    }

    public static function getInstance(): self
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }

        throw new UserException(
            'The role collection has not been initialized',
            '',
            self::ERROR_ROLES_NOT_INITIALIZED
        );
    }

    public function getDefaultID(): string
    {
        return '';
    }

    public function getFrameworkRoleFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/../Role');
    }

    public function getAppRoleFolder() : FolderInfo
    {
        return FolderInfo::factory(APP_ROOT.'/assets/classes/'.APP_CLASS_NAME.'/User/Role');
    }

    protected function registerItems(): void
    {
        $this->registerFolderItems($this->getFrameworkRoleFolder(), 'Application_User_Role_');
        $this->registerFolderItems($this->getAppRoleFolder(), APP_CLASS_NAME.'_User_Role_');

        uasort($this->items, static function(BaseRole $a, BaseRole $b) : int {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });
    }

    protected function registerFolderItems(FolderInfo $folder, string $classTemplate): void
    {
        if(!$folder->exists()) {
            return;
        }

        $names = FileHelper::createFileFinder($folder)
            ->getPHPClassNames();

        foreach ($names as $name)
        {
            $class = ClassHelper::requireResolvedClass($classTemplate.$name);

            $role = ClassHelper::requireObjectInstanceOf(
                BaseRole::class,
                new $class($this->rightsManager)
            );

            $this->registerItem($role);
        }
    }

    protected function initItems(): array
    {
        parent::initItems();

        uasort($this->items, static function(BaseRole $a, BaseRole $b) : int {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });

        return $this->items;
    }

    public function register() : void
    {
        $roles = $this->getAll();

        foreach ($roles as $role) {
            $role->register();
        }
    }
}
