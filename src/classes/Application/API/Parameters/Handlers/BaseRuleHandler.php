<?php

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\Parameters\Rules\RuleInterface;

abstract class BaseRuleHandler extends BaseAPIHandler implements RuleHandlerInterface
{
    private ?RuleInterface $rule = null;

    public function register(): RuleInterface
    {
        if(!isset($this->rule)) {
            $this->rule = $this->createRule();
            $this->manager->registerRule($this->rule);
        }

        return $this->rule;
    }

    abstract protected function createRule() : RuleInterface;

    public function getRule(): ?RuleInterface
    {
        return $this->rule;
    }

    public function getParams(): array
    {
        if(isset($this->rule)) {
            return $this->rule->getParams();
        }

        return array();
    }
}
