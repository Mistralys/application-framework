<?php
/**
 * File containing the class {@see UI_Statuses}.
 *
 * @package User Interface
 * @subpackage Statuses
 * @see UI_Statuses
 */

declare(strict_types=1);

/**
 * Base class for handling states of an object, with different
 * levels of criticality.
 *
 * This is meant to be extended so it can be customized, for
 * example by implementing a custom status class. If this is
 * not needed, look at the {@see UI_Statuses_Generic} class
 * instead, which can be used without extending it.
 *
 * @package User Interface
 * @subpackage Statuses
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Statuses_Generic
 */
abstract class UI_Statuses
{
    const ERROR_INVALID_STATUS_CLASS = 87101;
    const ERROR_STATUS_ID_DOES_NOT_EXIST = 87102;

    /**
     * @var array<string,UI_Interfaces_Statuses_Status>
     */
    protected $statuses = array();

    public function __construct()
    {
        $this->registerStatuses();
    }

    /**
     * Registers the available statuses, using the
     * `registerStatus()` method.
     *
     * @see UI_Statuses::registerStatus()
     */
    abstract protected function registerStatuses() : void;

    /**
     * Overridable: Returns the name of the status class that
     * should be used for the status instances. Must be a class
     * that implements the {@see UI_Interfaces_Statuses_Status} interface.
     *
     * @return string
     * @see UI_Interfaces_Statuses_Status
     */
    public function getStatusClass() : string
    {
        return UI_Statuses_Status::class;
    }

    /**
     * @param string $id
     * @param string $label
     * @return UI_Interfaces_Statuses_Status
     * @throws UI_Exception
     */
    protected final function registerStatus(string $id, string $label) : UI_Interfaces_Statuses_Status
    {
        $status = $this->createStatus($id, $label);

        $this->statuses[$id] = $status;

        return $status;
    }

    /**
     * @param string $id
     * @param string $label
     * @return UI_Interfaces_Statuses_Status
     * @throws UI_Exception
     */
    protected final function createStatus(string $id, string $label) : UI_Interfaces_Statuses_Status
    {
        $class = $this->getStatusClass();

        $status = new $class($id, $label);

        if($status instanceof UI_Interfaces_Statuses_Status) {
            return $status;
        }

        throw new UI_Exception(
            'Invalid status class',
            sprintf(
                'The status class [%s] does not implement the necessary interface [%s].',
                $class,
                UI_Interfaces_Statuses_Status::class
            ),
            self::ERROR_INVALID_STATUS_CLASS
        );
    }

    /**
     * @return UI_Interfaces_Statuses_Status[]
     */
    public function getAll() : array
    {
        return array_values($this->statuses);
    }

    /**
     * @param string $statusID
     * @return UI_Interfaces_Statuses_Status
     * @throws UI_Exception
     */
    public function getByID(string $statusID) : UI_Interfaces_Statuses_Status
    {
        if(isset($this->statuses[$statusID])) {
            return $this->statuses[$statusID];
        }

        throw new UI_Exception(
            'Unknown status ID.',
            sprintf(
                'Cannot find status by ID [%s]. Available statuses are [%s] (of type [%s]).',
                $statusID,
                implode(', ', $this->getIDs()),
                $this->getStatusClass()
            ),
            self::ERROR_STATUS_ID_DOES_NOT_EXIST
        );
    }

    /**
     * Retrieves a list of all available status IDs,
     * sorted alphabetically.
     *
     * @return string[]
     */
    public function getIDs() : array
    {
        $ids = array_keys($this->statuses);
        sort($ids);

        return $ids;
    }
}
