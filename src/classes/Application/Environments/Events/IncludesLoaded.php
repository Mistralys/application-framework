<?php
/**
 * @package Application
 * @subpackage Environments
 * @see \Application\Environments\Events\IncludesLoaded
 */

declare(strict_types=1);

namespace Application\Environments\Events;

use Application\Environments\Environment;
use Application_EventHandler_EventableEvent;

/**
 * Class for the {@see \Application\Environments::EVENT_INCLUDES_LOADED} event.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see \Application\Environments\Environment::onIncludesLoaded()
 */
class IncludesLoaded extends Application_EventHandler_EventableEvent
{
    private Environment $environment;

    public function __construct(string $name, Environment $subject, array $args = array())
    {
        parent::__construct($name, $subject, $args);

        $this->environment = $subject;
    }

    public function getEnvironment() : Environment
    {
        return $this->environment;
    }
}
