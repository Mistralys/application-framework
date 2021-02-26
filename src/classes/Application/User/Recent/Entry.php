<?php

declare(strict_types=1);

class Application_User_Recent_Entry implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    /**
     * @var Application_User_Recent_Category
     */
    private $category;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $url;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $id;

    public function __construct(Application_User_Recent_Category $category, string $id, string $label, string $url, DateTime $date)
    {
        $this->category = $category;
        $this->label = $label;
        $this->url = $url;
        $this->date = $date;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getID(): string
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

    /**
     * @return Application_User_Recent_Category
     */
    public function getCategory(): Application_User_Recent_Category
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function toArray() : array
    {
        return array(
            'id' => $this->getID(),
            'label' => $this->getLabel(),
            'date' => $this->getDate()->format('Y-m-d H:i:s'),
            'url' => $this->getUrl()
        );
    }

    public function getLogIdentifier(): string
    {
        return sprintf(
            '%s | Entry [%s %s]',
            $this->category->getLogIdentifier(),
            $this->getID(),
            $this->getDate()->format('Y-m-d H:i:s')
        );
    }
}
