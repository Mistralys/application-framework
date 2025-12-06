<?php

declare(strict_types=1);

namespace Application\Revisionable\Collection;

use Application\Revisionable\Collection\FilterSettings\Application_RevisionableCollection_FilterSettings_StateFilter;
use Application_FilterSettings;
use DBHelper_BaseFilterSettings;
use HTML_QuickForm2_Element_InputText;
use UI_Exception;

/**
 *
 * @property RevisionableFilterCriteriaInterface $filters
 * @property RevisionableCollectionInterface $collection
 */
abstract class BaseRevisionableFilterSettings extends DBHelper_BaseFilterSettings implements RevisionableFilterSettingsInterface
{
    public const string FILTER_STATE = 'state';

    public function __construct(RevisionableCollectionInterface $collection)
    {
        parent::__construct($collection);
    }

    public function getCollection() : RevisionableCollectionInterface
    {
        return $this->collection;
    }

    protected Application_RevisionableCollection_FilterSettings_StateFilter $stateConfig;

    /**
     * Registers the revisionable's state to be filterable.
     * Use the return value of the method to configure the
     * states that can be filtered.
     *
     * Afterwards, use the {@link inject_revisionable_state()} method
     * to inject the matching element into the form where you
     * want it.  The rest is automatic.
     *
     * @param mixed|null $default
     * @return Application_RevisionableCollection_FilterSettings_StateFilter
     * @throws UI_Exception
     */
    protected function registerStateSetting(mixed $default = null): Application_RevisionableCollection_FilterSettings_StateFilter
    {
        $this->stateConfig = new Application_RevisionableCollection_FilterSettings_StateFilter($this);

        $this->registerSetting(self::FILTER_STATE, t('State'), $default, Application_RevisionableCollection_FilterSettings_StateFilter::class);

        return $this->stateConfig;
    }

    protected function inject_search(): HTML_QuickForm2_Element_InputText
    {
        $searchFields = array_values($this->collection->getRecordSearchableColumns());

        return $this->addElementSearch($searchFields);
    }
}
