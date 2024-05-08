<?php

declare(strict_types=1);

use AppUtils\Interfaces\StringableInterface;
use function PHPUnit\Framework\callback;

class Application_User_Rights_Right
{
    public const ACTION_VIEW = 'view';
    public const ACTION_EDIT = 'edit';
    public const ACTION_CREATE = 'create';
    public const ACTION_DELETE = 'delete';
    public const ACTION_ADMINISTRATE = 'administrate';
    public const ACTION_AUTHENTICATE = 'authenticate';
    public const ACTION_ALL = 'all';

    private string $id;

    private string $label;

    private Application_User_Rights_Group $group;

    private string $description = '';

    /**
     * @var string[]
     */
    private array $rightGrants = array();

    /**
     * @var array<string,string[]>
     */
    private array $groupGrants = array();

    private string $action = '';
    private Application_User_Rights $manager;

    public function __construct(string $id, string $label, Application_User_Rights_Group $group)
    {
        $this->id = $id;
        $this->label = $label;
        $this->group = $group;
        $this->manager = $group->getManager();
    }

    public function getAction() : string
    {
        return $this->action;
    }

    public function getID() : string
    {
        return $this->id;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getGroup() : Application_User_Rights_Group
    {
        return $this->group;
    }

    /**
     * @param string|int|float|StringableInterface|NULL $description
     * @return $this
     * @throws UI_Exception
     */
    public function setDescription($description) : Application_User_Rights_Right
    {
        $this->description = toString($description);
        return $this;
    }

    public function grantRight(string $rightID) : Application_User_Rights_Right
    {
        if(!in_array($rightID, $this->rightGrants, true))
        {
            $this->rightGrants[] = $rightID;
            $this->resetGrantCache();
        }

        return $this;
    }

    /**
     * @param string ...$rightIDs
     * @return Application_User_Rights_Right
     */
    public function grantRights(...$rightIDs) : Application_User_Rights_Right
    {
        foreach($rightIDs as $rightID)
        {
            $this->grantRight($rightID);
        }

        return $this;
    }

    protected function grantGroup(string $groupID, string $action) : Application_User_Rights_Right
    {
        if(!isset($this->groupGrants[$groupID]))
        {
            $this->groupGrants[$groupID] = array();
        }

        if(!in_array($action, $this->groupGrants[$groupID], true))
        {
            $this->groupGrants[$groupID][] = $action;
            $this->resetGrantCache();
        }

        return $this;
    }

    /**
     * Grants all rights available in the target group.
     *
     * @param string $groupID
     * @return Application_User_Rights_Right
     */
    public function grantGroupAll(string $groupID) : Application_User_Rights_Right
    {
        return $this->grantGroup($groupID, self::ACTION_ALL);
    }

    /**
     * Grants all "VIEW" rights of the target group.
     *
     * @param string $groupID
     * @return Application_User_Rights_Right
     */
    public function grantGroupView(string $groupID) : Application_User_Rights_Right
    {
        return $this->grantGroup($groupID, self::ACTION_VIEW);
    }

    /**
     * Grants all "EDIT" rights of the target group.
     *
     * @param string $groupID
     * @return Application_User_Rights_Right
     */
    public function grantGroupEdit(string $groupID) : Application_User_Rights_Right
    {
        return $this->grantGroup($groupID, self::ACTION_EDIT);
    }

    /**
     * Grants all "CREATE" rights of the target group.
     *
     * @param string $groupID
     * @return Application_User_Rights_Right
     */
    public function grantGroupCreate(string $groupID) : Application_User_Rights_Right
    {
        return $this->grantGroup($groupID, self::ACTION_CREATE);
    }

    /**
     * Grants all "DELETE" rights of the target group.
     *
     * @param string $groupID
     * @return Application_User_Rights_Right
     */
    public function grantGroupDelete(string $groupID) : Application_User_Rights_Right
    {
        return $this->grantGroup($groupID, self::ACTION_DELETE);
    }

    public function grantGroupsView(array $groupIDs) : Application_User_Rights_Right
    {
        return $this->grantGroupsAction($groupIDs,self::ACTION_VIEW);
    }

    public function grantGroupsEdit(array $groupIDs) : Application_User_Rights_Right
    {
        return $this->grantGroupsAction($groupIDs,self::ACTION_EDIT);
    }

    public function grantGroupsCreate(array $groupIDs) : Application_User_Rights_Right
    {
        return $this->grantGroupsAction($groupIDs,self::ACTION_CREATE);
    }

    public function grantGroupsDelete(array $groupIDs) : Application_User_Rights_Right
    {
        return $this->grantGroupsAction($groupIDs,self::ACTION_DELETE);
    }

    public function grantGroupsAll(array $groupIDs) : Application_User_Rights_Right
    {
        return $this->grantGroupsAction($groupIDs,self::ACTION_ALL);
    }

    public function grantGroupsAction(array $groupIDs, $action) : Application_User_Rights_Right
    {
        foreach($groupIDs as $groupID)
        {
            $this->grantGroupsAction($groupID, $action);
        }

        return $this;
    }

    /**
     * Specifies that this is a "VIEW" right, for viewing things in readonly mode.
     *
     * @return Application_User_Rights_Right
     */
    public function actionView() : Application_User_Rights_Right
    {
        return $this->setAction(self::ACTION_VIEW);
    }

    /**
     * Specifies that this is an "Administration" right, for configuring
     * application settings and other admin-only tasks.
     *
     * @return $this
     */
    public function actionAdministrate() : Application_User_Rights_Right
    {
        return $this->setAction(self::ACTION_ADMINISTRATE);
    }

    /**
     * Special right action for the {@see Application_User::RIGHT_LOGIN} right.
     * @return $this
     */
    public function actionAuthenticate() : Application_User_Rights_Right
    {
        return $this->setAction(self::ACTION_AUTHENTICATE);
    }

    /**
     * Specifies that this is an "EDIT" right, for editing existing records.
     *
     * @return Application_User_Rights_Right
     */
    public function actionEdit() : Application_User_Rights_Right
    {
        return $this->setAction(self::ACTION_EDIT);
    }

    /**
     * Specifies that this is a "CREATE" right, for adding new records.
     *
     * @return Application_User_Rights_Right
     */
    public function actionCreate() : Application_User_Rights_Right
    {
        return $this->setAction(self::ACTION_CREATE);
    }

    /**
     * Specified that this is a "DELETE" right, for deleting existing records.
     *
     * @return Application_User_Rights_Right
     */
    public function actionDelete() : Application_User_Rights_Right
    {
        return $this->setAction(self::ACTION_DELETE);
    }

    public function setAction(string $action) : Application_User_Rights_Right
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @var string[]|null
     */
    private ?array $cachedOwnGrantIDs = null;

    private function resetGrantCache() : void
    {
        $this->cachedOwnGrantIDs = null;
        $this->cachedGrants = null;
    }

    /**
     * Resolves a list of right IDs that this right grants directly, non-recursive.
     * Checks single rights that were added, as well as group rights.
     *
     * @return string[]
     * @throws Application_Exception
     */
    private function resolveOwnGrantIDs() : array
    {
        if(isset($this->cachedOwnGrantIDs))
        {
            return $this->cachedOwnGrantIDs;
        }

        $this->cachedOwnGrantIDs = $this->rightGrants;

        foreach($this->groupGrants as $groupID => $actions)
        {
            $group = $this->manager->getGroupByID($groupID);

            foreach($actions as $action)
            {
                $rights = $group->getRights()->getByAction($action)->getAll();

                foreach($rights as $right)
                {
                    $this->cachedOwnGrantIDs[] = $right->getID();
                }
            }
        }

        $this->cachedOwnGrantIDs = array_unique($this->cachedOwnGrantIDs);

        sort($this->cachedOwnGrantIDs);

        return $this->cachedOwnGrantIDs;
    }

    /**
     * Retrieves a list of the rights that this right grants directly (non-recursive).
     *
     * @return Application_User_Rights_Container
     * @throws Application_Exception
     */
    public function getGrants() : Application_User_Rights_Container
    {
        $ids = $this->resolveOwnGrantIDs();
        $result = new Application_User_Rights_Container();

        foreach($ids as $id)
        {
            $result->add($this->manager->getRightByID($id));
        }

        return $result;
    }

    private ?Application_User_Rights_Container $cachedGrants = null;

    /**
     * Retrieves a list of all rights that this right grants, recursively checking
     * any rights that the granted rights may grant.
     *
     * @return Application_User_Rights_Container
     */
    public function resolveGrants() : Application_User_Rights_Container
    {
        if(isset($this->cachedGrants)) {
            return $this->cachedGrants;
        }

        $this->cachedGrants = new Application_User_Rights_Container();

        $this->findGrantsRecursive($this, $this, $this->cachedGrants);

        return $this->cachedGrants;
    }

    private function findGrantsRecursive(Application_User_Rights_Right $subject, Application_User_Rights_Right $origin, Application_User_Rights_Container $collection) : void
    {
        $originID = $origin->getID();
        $grants = $subject->getGrants()->getAll();

        foreach($grants as $right)
        {
            $rightID = $right->getID();

            if($rightID === $originID)
            {
                continue;
            }

            $collection->add($right);

            $this->findGrantsRecursive($right, $origin, $collection);
        }
    }

    public function toArray() : array
    {
        return array(
            'label' => $this->getLabel(),
            'descr' => $this->getDescription(),
            'group' => $this->group->getLabel(),
            'grants' => $this->resolveGrants()->getIDs()
        );
    }

    public function getActionIcon() : string
    {
        switch($this->action)
        {
            case self::ACTION_EDIT: return (string)UI::icon()->edit();
            case self::ACTION_CREATE: return (string)UI::icon()->add();
            case self::ACTION_VIEW: return (string)UI::icon()->view();
            case self::ACTION_DELETE: return (string)UI::icon()->delete();
            case self::ACTION_ADMINISTRATE: return (string)UI::icon()->developer();
            case self::ACTION_AUTHENTICATE: return (string)UI::icon()->logIn();
        }

        return '';
    }

    /**
     * Icon label with the description in a tooltip.
     *
     * @return string
     * @throws UI_Exception
     */
    public function getIconLabel() : string
    {
        return (string)sb()
            ->add($this->getActionIcon())
            ->tooltip($this->getLabel(), $this->getDescription());
    }

    public function hasGrant(string $rightName) : bool
    {
        return $this->getGrants()->idExists($rightName);
    }
}
