<?php
/**
 * File containing the class {@see UI_Statuses_Status}.
 *
 * @package User Interface
 * @subpackage Statuses
 * @see UI_Statuses_Status
 */

declare(strict_types=1);

/**
 * Container for an individual status.
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Statuses_Status implements UI_Interfaces_Statuses_Status
{
    use Application_Traits_Iconizable;

    const DEFAULT_CRITICALITY = UI_CriticalityEnum::INFO;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $criticality = self::DEFAULT_CRITICALITY;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $tooltip = '';

    public function __construct(string $id, string $label)
    {
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
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param mixed|UI_Renderable_Interface $tooltip
     * @return $this
     * @throws Application_Exception
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = toString($tooltip);
        return $this;
    }

    /**
     * Converts the status to a badge/label.
     *
     * @return UI_Label
     * @throws Application_Exception
     */
    public function getBadge() : UI_Label
    {
        $badge = UI::label($this->label)
        ->makeType($this->criticality);

        if(!empty($this->tooltip)) {
            $badge->setTooltip($this->tooltip);
        }

        if(isset($this->icon)) {
            $badge->setIcon($this->icon);
        }

        return $badge;
    }

    /**
     * @return $this
     */
    public function makeInformation()
    {
        return $this->setCriticality(UI_CriticalityEnum::INFO);
    }

    /**
     * @return $this
     */
    public function makeWarning()
    {
        return $this->setCriticality(UI_CriticalityEnum::WARNING);
    }

    /**
     * @return $this
     */
    public function makeSuccess()
    {
        return $this->setCriticality(UI_CriticalityEnum::SUCCESS);
    }

    /**
     * @return $this
     */
    public function makeDangerous()
    {
        return $this->setCriticality(UI_CriticalityEnum::DANGEROUS);
    }

    /**
     * @return $this
     */
    public function makeInactive()
    {
        return $this->setCriticality(UI_CriticalityEnum::INACTIVE);
    }

    /**
     * @param string $criticality
     * @return $this
     */
    public function setCriticality(string $criticality)
    {
        $this->criticality = $criticality;
        return $this;
    }

    public function getCriticality() : string
    {
        return $this->criticality;
    }

    public function isCriticality(string $criticality) : bool
    {
        return $this->criticality === $criticality;
    }

    public function isSuccess() : bool
    {
        return $this->isCriticality(UI_CriticalityEnum::SUCCESS);
    }

    public function isWarning() : bool
    {
        return $this->isCriticality(UI_CriticalityEnum::WARNING);
    }

    public function isInformation() : bool
    {
        return $this->isCriticality(UI_CriticalityEnum::INFO);
    }

    public function isInactive() : bool
    {
        return $this->isCriticality(UI_CriticalityEnum::INACTIVE);
    }

    public function isDangerous() : bool
    {
        return $this->isCriticality(UI_CriticalityEnum::DANGEROUS);
    }
}