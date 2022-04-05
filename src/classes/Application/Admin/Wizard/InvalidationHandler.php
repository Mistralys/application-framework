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
     * This parameter is used for checking which step started invalidation check.
     * @see Application_Traits_Admin_Wizard
     * handle_stepUpdated function is calling recursively so system must decide
     * which step is started this progress.
     *
     * @param int $invalidationCallingStep
     */
    public function setInvalidationCallingStep(int $invalidationCallingStep) : void
    {
        $this->invalidationCallingStep = $invalidationCallingStep;
    }
}