<?php

declare(strict_types=1);

class Application_User_Rights_Role
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string[]
     */
    private $rights = array();

    /**
     * @var Application_User_Rights
     */
    private $manager;

    public function __construct(Application_User_Rights $manager, string $id, string $label)
    {
        $this->manager = $manager;
        $this->id = $id;
        $this->label = $label;
    }

    public function getID() : string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRightIDs() : array
    {
        return $this->rights;
    }

    public function getRights() : Application_User_Rights_Container
    {
        $container = new Application_User_Rights_Container();

        foreach($this->rights as $rightID)
        {
            $container->add($this->manager->getRightByID($rightID));
        }

        return $container;
    }

    public function getLeftoverRights() : Application_User_Rights_Container
    {
        return $this->getRights()->resolveLeftoverRights($this->manager);
    }

    public function addRight(string $right) : Application_User_Rights_Role
    {
        if(!in_array($right, $this->rights))
        {
            $this->rights[] = $right;
        }

        return $this;
    }

    /**
     * @param string ...$rights
     * @return Application_User_Rights_Role
     */
    public function addRights(...$rights) : Application_User_Rights_Role
    {
        foreach($rights as $right)
        {
            $this->addRight($right);
        }

        return $this;
    }
}
