<?php
/**
 * Class containing the {@see InvalidationHandler} class.
 *
 * @package Application
 * @subpackage Administration
 * @see InvalidationHandler
 */

declare(strict_types=1);

namespace Application\Admin\Wizard;

/**
 * Class for invalidation process data.
 *
 * @package Application
 * @subpackage Administration
 * @author Emre Celebi <emre.celebi@ionos.com>
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
     * This parameter is used for checking which step started invalidation check.
     * @see Application_Traits_Admin_Wizard
     * handle_stepUpdated function is calling recursively so system must decide
     * which step is started this progress.
     *
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
     * @return InvalidationHandler
     */
    public function setIsInvalidated(bool $isInvalidated) : self
    {
        $this->isInvalidated = $isInvalidated;
        return $this;
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
     * @return InvalidationHandler
     */
    public function setInvalidationMessage(string $invalidationMessage) : self
    {
        $this->invalidationMessage = $invalidationMessage;
        return $this;
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
     * @return InvalidationHandler
     */
    public function setInvalidationURL(string $invalidationURL) : self
    {
        $this->invalidationURL = $invalidationURL;
        return $this;
    }

    /**
     * @return int
     */
    public function getInvalidationCallingStep() : int
    {
        return $this->invalidationCallingStep;
    }

    /**
     * This parameter is used for checking which step started invalidation check.
     * Application_Traits_Admin_Wizard->handle_stepUpdated function is calling recursively
     * so system must decide which step is started this progress.
     * @param int $invalidationCallingStep
     * @return InvalidationHandler
     * @see Application_Traits_Admin_Wizard
     */
    public function setInvalidationCallingStep(int $invalidationCallingStep) : self
    {
        $this->invalidationCallingStep = $invalidationCallingStep;
        return $this;
    }
}