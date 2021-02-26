<?php

declare(strict_types=1);

class Application_User_Rights_Container
{
    /**
     * @var Application_User_Rights_Right[]
     */
    private $rights = array();

    /**
     * @var bool
     */
    private $isSorted = false;

    public function __construct()
    {
    }

    /**
     * @param Application_User_Rights_Right $right
     * @return $this
     */
    public function add(Application_User_Rights_Right $right)
    {
        $this->isSorted = false;
        $this->rights[$right->getID()] = $right;

        return $this;
    }

    /**
     * @param Application_User_Rights_Container $container
     * @return $this
     */
    public function addContainer(Application_User_Rights_Container $container)
    {
        $this->rights = array_merge($this->rights, $container->getAll());
        return $this;
    }

    protected function sortRights() : void
    {
        if($this->isSorted) {
            return;
        }

        ksort($this->rights);
        $this->isSorted = true;
    }

    /**
     * @return string[]
     */
    public function getIDs() : array
    {
        $this->sortRights();

        $result = array();

        foreach ($this->rights as $right)
        {
            $result[] = $right->getID();
        }

        return $result;
    }

    /**
     * @return Application_User_Rights_Right[]
     */
    public function getAll() : array
    {
        $this->sortRights();

        return $this->rights;
    }

    public function getByAction(string $action) : Application_User_Rights_Container
    {
        if($action === Application_User_Rights_Right::ACTION_ALL)
        {
            return $this;
        }

        $result = new Application_User_Rights_Container();

        foreach($this->rights as $right)
        {
            if($right->getAction() === $action)
            {
                $result->add($right);
            }
        }

        return $result;
    }

    public function getByID(string $rightID) : ?Application_User_Rights_Right
    {
        foreach($this->rights as $right)
        {
            if($right->getID() === $rightID)
            {
                return $right;
            }
        }

        return null;
    }

    public function resolveAllRights() : Application_User_Rights_Container
    {
        $rights = $this->getAll();

        $collected = new Application_User_Rights_Container();

        foreach($rights as $right)
        {
            $collected->addContainer($right->resolveGrants());
        }

        $collected->addContainer($this);

        return $collected;
    }

    public function resolveLeftoverRights(Application_User_Rights $manager) : Application_User_Rights_Container
    {
        $granted = $this->resolveAllRights()->getIDs();
        $all = $manager->getRights()->getAll();
        $leftover = new Application_User_Rights_Container();

        foreach($all as $right)
        {
            if(!in_array($right->getID(), $granted))
            {
                $leftover->add($right);
            }
        }

        return $leftover;
    }
}
