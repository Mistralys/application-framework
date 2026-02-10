<?php

declare(strict_types=1);

namespace Application\AI\Tools;

use Application\AI\Cache\AICacheStrategyInterface;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

interface AIToolInterface extends StringPrimaryRecordInterface
{
    public function getCacheStrategy() : AICacheStrategyInterface;
    public function execute(): array;
}
