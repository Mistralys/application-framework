<?php

declare(strict_types=1);

namespace Application\Interfaces\Admin;

interface AdminSubmodeInterface extends AdminScreenInterface
{
    public function getMode() : AdminModeInterface;

    /**
     * Checks whether this submode has separate action classes.
     *
     * @return boolean
     */
    public function hasActions() : bool;

    public function getDefaultAction() : string;

    /**
     * Retrieves the ID of the currently selected action.
     *
     * @return string
     */
    public function getActionID() : string;

    /**
     * Retrieves the currently active action, or null if no actions are available.
     * @return AdminActionInterface|NULL
     */
    public function getAction() : ?AdminActionInterface;
}
