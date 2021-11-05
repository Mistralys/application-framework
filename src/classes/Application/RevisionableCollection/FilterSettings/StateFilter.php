<?php

class Application_RevisionableCollection_FilterSettings_StateFilter
{
    public const ERROR_INVALID_STATE_IN_PRESET = 16001;
    
    /**
     * @var Application_RevisionableCollection_FilterSettings
     */
    protected $settings;
    
    /**
     * @var Application_RevisionableCollection
     */
    protected $collection;
    
    protected $states = array();
    
    protected $presets = array();
    
    public function __construct(Application_RevisionableCollection_FilterSettings $settings)
    {
        $this->settings = $settings;
        $this->collection = $settings->getCollection();
        
        $states = $this->collection->createDummyRecord()->getStateHandler()->getStates();
        foreach($states as $state) {
            $this->states[$state->getName()] = $state;
        }
        
        $this->addPreset('any', t('Any state'));
    }
    
    /**
     * Add a preset to add to the dropdown element: each defines a set
     * of states that will be included/excluded when selecting the filter.
     *
     * @param string $presetName
     * @param string $presetLabel
     * @param string[] $includeStates State names to limit the list to. Leave empty to select all states.
     * @param string[] $excludeStates
     */
    public function addPreset($presetName, $presetLabel, $includeStates=array(), $excludeStates=array())
    {
        $this->presets[$presetName] = array(
            'label' => $presetLabel,
            'include' => $includeStates,
            'exclude' => $excludeStates
        );
        
        // check that the specified states actually exist
        foreach($includeStates as $stateName) { $this->requireState($stateName); }
        foreach($excludeStates as $stateName) { $this->requireState($stateName); }
        
        return $this;
    }
    
    protected function requireState($stateName)
    {
        if(isset($this->states[$stateName])) {
            return;
        }
        
        throw new Application_Exception(
            'Invalid state for preset',
            sprintf(
                'The revisionable of type [%s] does not have the state [%s]. Available states are [%s].',
                $this->collection->getRecordTypeName(),
                $stateName,
                implode(', ', array_keys($this->states))
                ),
            self::ERROR_INVALID_STATE_IN_PRESET
            );
    }
    
    public function configure(Application_RevisionableCollection_FilterCriteria $filterCriteria)
    {
        $value = $this->settings->getSetting('state');
        
        // it's a preset
        if(isset($this->presets[$value]))
        {
            $preset = $this->presets[$value];
            
            foreach($preset['include'] as $stateName) {
                $filterCriteria->selectState($stateName);
            }
            
            foreach($preset['exclude'] as $stateName) {
                $filterCriteria->selectState($stateName, true);
            }
        }
        else if(isset($this->states[$value]))
        {
            $filterCriteria->selectState($value);
        }
    }
    
    /**
     * Adds the state filter selection element to the specified form container.
     *
     * @param HTML_QuickForm2_Container $container
     * @return HTML_QuickForm2_Element_Select
     */
    public function injectElement(HTML_QuickForm2_Container $container)
    {
        $element = $this->settings->addElementSelect('state', $container);
        
        $presetGroup = $element->addOptgroup(t('Filter presets'));
        foreach($this->presets as $name => $def) {
            $presetGroup->addOption($def['label'], $name);
        }
        
        $statesGroup = $element->addOptgroup(t('Specific states'));
        foreach($this->states as $state) {
            $statesGroup->addOption($state->getLabel(), $state->getName());
        }
        
        return $element;
    }
}
