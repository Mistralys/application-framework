<?php

declare(strict_types=1);

namespace Application\AI;

use Application\AI\Tools\AIToolInterface;

abstract class BaseAIToolContainer
{
    public function __construct()
    {
        // Ensure that the application environment is initialized.
        EnvironmentRunner::run();
    }

    /**
     * Runs the given AI tool class or instance, handling caching as needed.
     *
     * @param string|AIToolInterface $classOrInstance Use an instance when parameters
     *      are needed to be passed on; Pass them as constructor arguments.
     * @return array<int|string, mixed>
     */
    protected function runTool(string|AIToolInterface $classOrInstance) : array
    {
        if(is_string($classOrInstance)) {
            $tool = new $classOrInstance();
        } else {
            $tool = $classOrInstance;
        }

        $cacheStrategy = $tool->getCacheStrategy();
        if($cacheStrategy->isCacheValid($tool)) {
            return $cacheStrategy->getFromCache($tool);
        }

        $data = $tool->execute();

        $cacheStrategy->saveCache($tool, $data);

        return $data;
    }
}
