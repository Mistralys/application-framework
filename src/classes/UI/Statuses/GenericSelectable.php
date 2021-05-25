<?php
/**
 * File containing the class {@see UI_Statuses_GenericSelectable}.
 *
 * @package User Interface
 * @subpackage Statuses
 * @see UI_Statuses_GenericSelectable
 */

declare(strict_types=1);

/**
 * Utility class for handling generic status cases, where
 * it is not necessary to create a custom status class. Supports
 * selecting an active state.
 *
 * Usage:
 *
 * 1. Instantiate the class.
 * 2. Register possible states with `addStatus()`.
 * 3. Set the default state to use with `setDefaultID()`.
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Statuses_GenericSelectable extends UI_Statuses_Selectable
{
    /**
     * @var string
     */
    protected $defaultID = '';

    protected function registerStatuses() : void
    {
        // Nothing to do here, they are added afterwards via addStatus().
    }

    public function addStatus(string $id, string $label) : UI_Interfaces_Statuses_Status
    {
        return $this->registerStatus($id, $label);
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setDefaultID(string $id)
    {
        $this->defaultID = $id;
        return $this;
    }

    public function getDefaultID() : string
    {
        return $this->defaultID;
    }
}
