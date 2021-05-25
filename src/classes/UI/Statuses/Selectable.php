<?php
/**
 * File containing the class {@see UI_Statuses_Selectable}.
 *
 * @package User Interface
 * @subpackage Statuses
 * @see UI_Statuses_Selectable
 */

declare(strict_types=1);

/**
 * This adds the functionality to select an active state to
 * the statuses manager.
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_Statuses_Selectable extends UI_Statuses
{
    const ERROR_CANNOT_SELECT_INVALID_STATUS = 87401;

    /**
     * @var string
     */
    protected $activeID = '';

    abstract public function getDefaultID() : string;

    /**
     * Retrieves the default status to use, when none
     * has been specifically selected.
     *
     * @return UI_Interfaces_Statuses_Status
     * @throws UI_Exception
     */
    public function getDefault() : UI_Interfaces_Statuses_Status
    {
        return $this->getByID($this->getDefaultID());
    }

    /**
     * Makes a status active by its ID.
     *
     * @param string $id
     * @return $this
     * @throws UI_Exception
     */
    public function selectByID(string $id)
    {
        return $this->select($this->getByID($id));
    }

    /**
     * Makes the specified status the active one.
     *
     * @param UI_Interfaces_Statuses_Status $status
     * @return $this
     * @throws UI_Exception
     *
     * @see UI_Statuses_Selectable::ERROR_CANNOT_SELECT_INVALID_STATUS
     */
    public function select(UI_Interfaces_Statuses_Status $status)
    {
        if($this->idExists($status->getID()))
        {
            $this->activeID = $status->getID();
            return $this;
        }

        throw new UI_Exception(
            'Cannot select status, it is invalid.',
            sprintf(
               'Cannot select the status [%s], only IDs [%s] are allowed.',
                $status->getID(),
                implode(', ', $this->getIDs())
            ),
            self::ERROR_CANNOT_SELECT_INVALID_STATUS
        );
    }

    /**
     * Whether a status has been specifically selected.
     * @return bool
     */
    public function hasActive() : bool
    {
        return !empty($this->activeID);
    }

    public function getActiveID() : string
    {
        if(!empty($this->activeID)) {
            return $this->activeID;
        }

        return $this->getDefaultID();
    }

    /**
     * Retrieves the currently active status, or the default
     * status if none has been specifically selected.
     *
     * @return UI_Interfaces_Statuses_Status
     * @throws UI_Exception
     */
    public function getActive() : UI_Interfaces_Statuses_Status
    {
        return $this->getByID($this->getActiveID());
    }
}
