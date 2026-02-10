<?php

declare(strict_types=1);

namespace Application\AI\Cache;

use Application\AI\Tools\AIToolInterface;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

interface AICacheStrategyInterface extends StringPrimaryRecordInterface
{
    public function isCacheValid(AIToolInterface $tool) : bool;

    /**
     * @param AIToolInterface $tool
     * @return array<int|string,mixed>
     */
    public function getFromCache(AIToolInterface $tool) : array;

    /**
     * @param AIToolInterface $tool
     * @param array<int|string,mixed> $data
     * @return self
     */
    public function saveCache(AIToolInterface $tool, array $data) : self;
}
