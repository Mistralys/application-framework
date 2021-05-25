<?php

declare(strict_types=1);

interface UI_Interfaces_Statuses_Status extends Application_Interfaces_Iconizable
{
    /**
     * The ID of the status, as specified when it was created, e.g. "warning".
     *
     * @return string
     */
    public function getID() : string;

    public function getLabel() : string;

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label);

    /**
     * @param mixed|UI_Renderable_Interface $tooltip
     * @return $this
     * @throws Application_Exception
     */
    public function setTooltip($tooltip);

    public function getBadge() : UI_Label;

    /**
     * @return $this
     */
    public function makeInformation();

    /**
     * @return $this
     */
    public function makeWarning();

    /**
     * @return $this
     */
    public function makeSuccess();

    /**
     * @return $this
     */
    public function makeDangerous();

    /**
     * @return $this
     */
    public function makeInactive();

    /**
     * @param string $criticality
     * @return $this
     * @see UI_CriticalityEnum
     */
    public function setCriticality(string $criticality);

    public function getCriticality() : string;

    public function isCriticality(string $criticality) : bool;

    public function isSuccess() : bool;

    public function isWarning() : bool;

    public function isInformation() : bool;

    public function isInactive() : bool;

    public function isDangerous() : bool;
}
