<?php
/**
 * @package Application
 * @subpackage Environments
 * @see \Application\Environments\Events\EnvironmentActivated
 */

declare(strict_types=1);

namespace Application\Environments\Events;

use Application\Environments;
use Application\Environments\Environment;
use Application\EventHandler\Eventables\BaseEventableEvent;

/**
 * Class for the {@see Environments::EVENT_ENVIRONMENT_ACTIVATED} event.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Environment::onActivated
 */
class EnvironmentDetected extends BaseEventableEvent
{
    public const string EVENT_NAME = 'EnvironmentDetected';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function __construct(string $name, Environments $subject, array $args = array())
    {
        parent::__construct($name, $subject, $args);
    }

    public function getEnvironment() : Environment
    {
        return $this->getArgumentObject(0, Environment::class);
    }
}
