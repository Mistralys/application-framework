<?php

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\Parameters\Rules\RuleInterface;

interface RuleHandlerInterface extends APIHandlerInterface
{
    public function register() : RuleInterface;

    public function getRule() : ?RuleInterface;
}
