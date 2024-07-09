<?php
/**
 * @package Application
 * @subpackage User
 */

declare(strict_types=1);

namespace Application\User\Roles;

use Application_User_Rights;
use Application_User_Rights_Role;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Abstract base class for a user role that can be simulated.
 *
 * @package Application
 * @subpackage User
 */
abstract class BaseRole
    implements
    StringPrimaryRecordInterface,
    StringableInterface
{
    protected Application_User_Rights $rightsManager;
    private bool $registered = false;
    private Application_User_Rights_Role $role;

    public function __construct(Application_User_Rights $rightsManager)
    {
        $this->rightsManager = $rightsManager;
        $this->role = $this->rightsManager->registerRole($this->getID(), $this->getLabel());
    }

    public function register() : void
    {
        if($this->registered) {
            return;
        }

        $this->registered = true;

        $this->role->addRights(...$this->getRights());
    }

    abstract public function getLabel() : string;

    abstract public function getRights() : array;

    public function __toString() : string
    {
        return $this->getLabel();
    }
}
