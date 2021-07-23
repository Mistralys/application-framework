<?php

declare(strict_types=1);

class UI_Bootstrap_Popover extends UI_Bootstrap
{
    const ERROR_INVALID_PLACEMENT = 89801;

    const TEMPLATE_ID = 'ui/bootstrap/popover';

    const PLACEMENT_RIGHT = 'right';
    const PLACEMENT_LEFT = 'left';
    const PLACEMENT_TOP = 'top';
    const PLACEMENT_BOTTOM = 'bottom';
    const TEMPLATE_KEY_POPOVER = 'popover';
    const TEMPLATE_KEY_ATTACH_TO_ID = 'attachToID';
    const TEMPLATE_KEY_CONTENT = 'content';
    const TEMPLATE_KEY_PLACEMENT = 'placement';
    const TEMPLATE_KEY_TITLE = 'title';

    /**
     * @var string
     */
    private $elementID = '';

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $content = '';

    /**
     * @var string
     */
    private $placement = self::PLACEMENT_RIGHT;

    /**
     * @var string[]
     */
    private static $validPlacements = array(
        self::PLACEMENT_BOTTOM,
        self::PLACEMENT_LEFT,
        self::PLACEMENT_RIGHT,
        self::PLACEMENT_TOP
    );

    /**
     * @param string $title
     * @return UI_Bootstrap_Popover
     */
    public function setTitle(string $title) : UI_Bootstrap_Popover
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * Sets the ID of the element to attach the popover to.
     *
     * @param string $attachToID
     * @return UI_Bootstrap_Popover
     */
    public function setAttachToID(string $attachToID) : UI_Bootstrap_Popover
    {
        $this->elementID = $attachToID;
        return $this;
    }

    public function getAttachToID() : string
    {
        return $this->elementID;
    }

    /**
     * Sets the content to display in the body of
     * the popover. May contain HTML.
     *
     * @param scalar|UI_Renderable_Interface $content
     * @return UI_Bootstrap_Popover
     * @throws UI_Exception
     *
     * @see UI::ERROR_NOT_A_RENDERABLE
     */
    public function setContent($content) : UI_Bootstrap_Popover
    {
        $this->content = toString($content);
        return $this;
    }

    public function getContent() : string
    {
        return $this->content;
    }

    public function getValidPlacements() : array
    {
        return self::$validPlacements;
    }

    public function isValidPlacement(string $placement) : bool
    {
        return in_array($placement, self::$validPlacements);
    }

    /**
     * Sets the placement to use for the popover.
     *
     * @param string $placement
     * @return $this
     * @throws UI_Exception
     *
     * @see UI_Bootstrap_Popover::PLACEMENT_TOP
     * @see UI_Bootstrap_Popover::PLACEMENT_BOTTOM
     * @see UI_Bootstrap_Popover::PLACEMENT_LEFT
     * @see UI_Bootstrap_Popover::PLACEMENT_RIGHT
     *
     * @see UI_Bootstrap_Popover::ERROR_INVALID_PLACEMENT
     */
    public function setPlacement(string $placement) : UI_Bootstrap_Popover
    {
        if($this->isValidPlacement($placement))
        {
            $this->placement = $placement;
            return $this;
        }

        throw new UI_Exception(
            'Invalid popover placement',
            sprintf(
                'The placement [%s] is not a valid placement. Possible values are [%s].',
                $placement,
                implode(', ', $this->getValidPlacements())
            ),
            self::ERROR_INVALID_PLACEMENT
        );
    }

    /**
     * Places the popover on the left of the target element.
     *
     * @return $this
     * @throws UI_Exception
     */
    public function setPlacementLeft() : UI_Bootstrap_Popover
    {
        return $this->setPlacement(self::PLACEMENT_LEFT);
    }

    /**
     * Places the popover on the right of the target element.
     *
     * @return $this
     * @throws UI_Exception
     */
    public function setPlacementRight() : UI_Bootstrap_Popover
    {
        return $this->setPlacement(self::PLACEMENT_RIGHT);
    }

    /**
     * Places the popover above the target element.
     *
     * @return $this
     * @throws UI_Exception
     */
    public function setPlacementTop() : UI_Bootstrap_Popover
    {
        return $this->setPlacement(self::PLACEMENT_TOP);
    }

    /**
     * Places the popover below the target element.
     *
     * @return $this
     * @throws UI_Exception
     */
    public function setPlacementBottom() : UI_Bootstrap_Popover
    {
        return $this->setPlacement(self::PLACEMENT_BOTTOM);
    }

    public function getPlacement() : string
    {
        return $this->placement;
    }

    public function getShowStatement() : string
    {
        return sprintf(
            "$('#%s').popover('show')",
            $this->getAttachToID()
        );
    }

    public function getHideStatement() : string
    {
        return sprintf(
            "$('#%s').popover('hide')",
            $this->getAttachToID()
        );
    }

    public function getToggleStatement() : string
    {
        return sprintf(
            "$('#%s').popover('toggle')",
            $this->getAttachToID()
        );
    }

    /**
     * @return string
     * @see template_default_ui_bootstrap_popover
     */
    protected function _render() : string
    {
        return $this->ui->createTemplate(self::TEMPLATE_ID)
            ->setVar(self::TEMPLATE_KEY_POPOVER, $this)
            ->setVar(self::TEMPLATE_KEY_ATTACH_TO_ID, $this->getAttachToID())
            ->setVar(self::TEMPLATE_KEY_CONTENT, $this->getContent())
            ->setVar(self::TEMPLATE_KEY_PLACEMENT, $this->getPlacement())
            ->setVar(self::TEMPLATE_KEY_TITLE, $this->getTitle())
            ->render();
    }
}
