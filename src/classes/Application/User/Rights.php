<?php

declare(strict_types=1);

class Application_User_Rights
{
    public const int ERROR_UNKNOWN_RIGHT = 70701;
    public const int ERROR_UNKNOWN_GROUP = 70702;
    public const int ERROR_UNKNOWN_ROLE = 70703;

    /**
     * @var Application_User_Rights_Group[]
     */
    private array $groups = array();

    /**
     * @var array<string,Application_User_Rights_Role>
     */
    private array $roles = array();

    public function __construct()
    {

    }

    public function registerRole(string $roleID, string $label) : Application_User_Rights_Role
    {
        $role = new Application_User_Rights_Role($this, $roleID, $label);

        $this->roles[$roleID] = $role;

        return $role;
    }

    public function registerGroup(string $groupID, string $label, callable $rightsCallback) : Application_User_Rights_Group
    {
        $group = new Application_User_Rights_Group($this, $groupID, $label, $rightsCallback);

        $this->groups[] = $group;

        return $group;
    }

    /**
     * @return Application_User_Rights_Group[]
     */
    public function getGroups() : array
    {
        uasort($this->groups, static function (Application_User_Rights_Group $a, Application_User_Rights_Group $b) : int {
            return strnatcasecmp($a->getID(), $b->getID());
        });

        return $this->groups;
    }

    /**
     * @return Application_User_Rights_Container
     */
    public function getRights() : Application_User_Rights_Container
    {
        $result = new Application_User_Rights_Container();

        foreach($this->groups as $group)
        {
            $result->addContainer($group->getRights());
        }

        return $result;
    }

    public function toArray() : array
    {
        $result = array();

        foreach($this->groups as $group)
        {
            // Cannot be unpacked with array_push because the keys are strings
            $result = array_merge($result, $group->toArray());
        }

        return $result;
    }

    public function getGroupByID(string $groupID) : Application_User_Rights_Group
    {
        foreach ($this->groups as $group)
        {
            if($group->getID() === $groupID)
            {
                return $group;
            }
        }

        throw new Application_Exception(
            'Unknown rights group',
            sprintf('The rights group [%s] has not been registered.', $groupID),
            self::ERROR_UNKNOWN_GROUP
        );
    }

    public function getRightByID(string $rightID) : Application_User_Rights_Right
    {
        $right = $this->getRights()->getByID($rightID);

        if($right !== null)
        {
            return $right;
        }

        throw new Application_Exception(
            'Unknown user right',
            sprintf('The user right [%s] has not been registered.', $rightID),
            self::ERROR_UNKNOWN_RIGHT
        );
    }

    private bool $rolesSorted = false;

    /**
     * @return Application_User_Rights_Role[]
     */
    public function getRoles() : array
    {
        if($this->rolesSorted === false) {
            $this->rolesSorted = true;
            uasort($this->roles, static function (Application_User_Rights_Role $a, Application_User_Rights_Role $b): int {
                return strnatcasecmp($a->getLabel(), $b->getLabel());
            });
        }

        return array_values($this->roles);
    }

    public function roleIDExists(string $roleID) : bool
    {
        return isset($this->roles[$roleID]);
    }

    public function getRoleByID(string $roleID) : Application_User_Rights_Role
    {
        if(isset($this->roles[$roleID]))
        {
            return $this->roles[$roleID];
        }

        throw new Application_Exception(
            'Unknown user role',
            sprintf(
                'The user role [%s] does not exist. Available roles are: [%s].',
                $roleID,
                implode(', ', array_keys($this->roles))
            ),
            self::ERROR_UNKNOWN_ROLE
        );
    }

    public function getGroupNames() : array
    {
        $groups = array();
        foreach ($this->groups as $group) {
            $groups[] = $group->getLabel();
        }

        sort($groups);

        return $groups;
    }

    public function rightIDExists(string $name) : bool
    {
        return $this->getRights()->idExists($name);
    }
}
