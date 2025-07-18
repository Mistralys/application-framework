<?php

declare(strict_types=1);

namespace Application\TimeTracker\TimeSpans\SpanTypes;

use Application\TimeTracker\TimeSpans\TimeSpanSettingsManager;
use Application_Formable_RecordSelector_Entry;
use Application_Formable_Selector;

class TimeSpanTypeSelector extends Application_Formable_Selector
{
    protected function configureEntry(Application_Formable_RecordSelector_Entry $entry): void
    {

    }

    protected function getDefaultName(): string
    {
        return TimeSpanSettingsManager::SETTING_TYPE;
    }

    protected function getDefaultLabel(): string
    {
        return t('Type');
    }

    protected function _loadEntries(): void
    {
        foreach(TimeSpanTypes::getInstance()->getAll() as $type) {
            $this->registerEntry(
                $type->getID(),
                $type->getLabel()
            );
        }
    }
}
