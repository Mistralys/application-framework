<?php
/**
 * @package Application
 * @subpackage Environments
 * @see \Application\Environments\Events\EnvironmentActivated
 */

declare(strict_types=1);

namespace Application\Environments\Events;

use Application\Environments\Environment;
use Application\EventHandler\Eventables\BaseEventableEvent;
use Application\EventHandler\Eventables\StandardEventableEvent;

/**
 * Class for the {@see \Application\Environments::EVENT_ENVIRONMENT_ACTIVATED} event.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Environment::onActivated
 */
class EnvironmentActivated extends BaseEventableEvent
{
    public const string EVENT_NAME = 'EnvironmentActivated';

    private Environment $environment;

    public function __construct(string $name, Environment $subject, array $args = array())
    {
        parent::__construct($name, $subject, $args);

        $this->environment = $subject;
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getEnvironment() : Environment
    {
        return $this->environment;
    }
}
