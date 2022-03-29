<?php
/**
 * Class containing the {@link InvalidationHandler} class.
 *
 * @package Application
 * @subpackage Administration
 * @see InvalidationHandler
 */

declare(strict_types=1);

namespace Application\Admin\Wizard;

/**
 * Base class for individual steps in a wizard. Based on the application
 * skeleton for administration pages, this allows for easy form handling
 * and the base structure handles all the data flow and necessary updates.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Admin_Wizard
 */
class InvalidationHandler
{
    /**
     * @var bool
     */
    protected bool $isInvalidated;

    /**
     * @var string
     */
    protected string $invalidationMessage;

    /**
     * @var string
     */
    protected string $invalidationURL;

    /**
     * @var int
     */
    protected int $invalidationCallingStep;

    /**
     * @return bool
     */
    public function isInvalidated() : bool
    {
        return $this->isInvalidated;
    }

    /**
     * @param bool $isInvalidated
     */
    public function setIsInvalidated(bool $isInvalidated) : void
    {
        $this->isInvalidated = $isInvalidated;
    }

    /**
     * @return string
     */
    public function getInvalidationMessage() : string
    {
        return $this->invalidationMessage;
    }

    /**
     * @param string $invalidationMessage
     */
    public function setInvalidationMessage(string $invalidationMessage) : void
    {
        $this->invalidationMessage = $invalidationMessage;
    }

    /**
     * @return string
     */
    public function getInvalidationURL() : string
    {
        return $this->invalidationURL;
    }

    /**
     * @param string $invalidationURL
     */
    public function setInvalidationURL(string $invalidationURL) : void
    {
        $this->invalidationURL = $invalidationURL;
    }

    /**
     * @return int
     */
    public function getInvalidationCallingStep() : int
    {
        return $this->invalidationCallingStep;
    }

    /**
     * @param int $invalidationCallingStep
     */
    public function setInvalidationCallingStep(int $invalidationCallingStep) : void
    {
        $this->invalidationCallingStep = $invalidationCallingStep;
    }
}