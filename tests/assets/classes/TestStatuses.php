<?php

declare(strict_types=1);

class TestStatuses extends UI_Statuses
{
    public function getStatusClass() : string
    {
        return TestStatus::class;
    }

    protected function registerStatuses() : void
    {
        // Add statuses in a shuffled alphabetical order.

        $this->registerStatus('trivial', 'Trivial')
            ->makeInactive();

        $this->registerStatus('important', 'Important')
            ->makeWarning();

        $this->registerStatus('default', 'Default');
    }
}
