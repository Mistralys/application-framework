<?php

declare(strict_types=1);

class Application_User_Rights_Group
{
    private string $id;
    private string $label;
    private Application_User_Rights_Container $rights;
    private Application_User_Rights $manager;
    private string $description = '';
    private bool $rightsRegistered = false;

    /**
     * @var callable
     */
    private $rightsCallback;

    public function __construct(Application_User_Rights $manager, string $id, string $label, callable $rightsCallback)
    {
        $this->manager = $manager;
        $this->id = $id;
        $this->label = $label;
        $this->rights = new Application_User_Rights_Container();

        $rightsCallback($this);
    }

    public function getManager() : Application_User_Rights
    {
        return $this->manager;
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

    /**
     * @param string|int|float|UI_Renderable_Interface $description
     * @return $this
     */
    public function setDescription($description) : self
    {
        $this->description = toString($description);
        return $this;
    }

    /**
     * @return Application_User_Rights_Container
     */
    public function getRights() : Application_User_Rights_Container
    {
        return $this->rights;
    }

    public function registerRight(string $id, string $label) : Application_User_Rights_Right
    {
        $right = new Application_User_Rights_Right($id, $label, $this);

        $this->rights->add($right);

        return $right;
    }

    public function toArray() : array
    {
        $result = array();
        $rights = $this->rights->getAll();

        foreach($rights as $right)
        {
            $result[$right->getID()] = $right->toArray();
        }

        return $result;
    }
}
