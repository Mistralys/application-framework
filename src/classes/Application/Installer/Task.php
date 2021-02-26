<?php
/**
 * File containing the class {@see Application_Installer_Task}.
 *
 * @package Application
 * @subpackage Installer
 * @see Application_Installer_Task
 */

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\OperationResult;

/**
 * Base class for individual installer tasks.
 *
 * @package Application
 * @subpackage Installer
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Installer_Task implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    /**
     * @var Application_Installer
     */
    protected $installer;

    /**
     * @var OperationResult
     */
    protected $result;

    public function __construct(Application_Installer $installer)
    {
        $this->installer = $installer;
    }

    public function getID() : string
    {
        return getClassTypeName($this);
    }

    public function process() : OperationResult
    {
        $this->result = new OperationResult($this);

        $this->log('Processing the task.');

        $this->_process();

        $this->log('Done. Result is valid: '.strtoupper(ConvertHelper::bool2string($this->result->isValid())));

        return $this->result;
    }

    /**
     * Retrieves a list of task IDs that this task depends on: ensures
     * that the dependent task gets processed first.
     *
     * @return string[]
     */
    abstract public function getTaskDependencies() : array;

    abstract protected function _process() : void;

    public function getLogIdentifier(): string
    {
        return sprintf(
            'AppInstaller | Task [%s]',
            $this->getID()
        );
    }
}
