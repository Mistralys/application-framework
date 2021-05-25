<?php
/**
 * File containing the class {@see UI_Statuses_Generic}.
 *
 * @package User Interface
 * @subpackage Statuses
 * @see UI_Statuses_Generic
 */

declare(strict_types=1);

/**
 * Utility class for handling generic status cases, where
 * it is not necessary to create a custom status class.
 *
 * Usage:
 *
 * 1. Instantiate the class
 * 2. Register possible states with `addStatus()`
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Statuses_Generic extends UI_Statuses
{
    protected function registerStatuses() : void
    {
        // Nothing to do here, they are added afterwards via addStatus().
    }

    public function addStatus(string $id, string $label) : UI_Interfaces_Statuses_Status
    {
        return $this->registerStatus($id, $label);
    }
}
