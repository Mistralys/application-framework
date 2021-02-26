<?php
/**
 * File containing the {@link Application_CustomProperties_Presets} class.
 * 
 * @package Application
 * @subpackage Custom properties
 */

/**
 * Manages presets for a properties collection. Presets can be 
 * selected in the user interface for creating properties from
 * them quickly, also offering more control since presets can
 * disallow editing the property created from them.
 * 
 * The presets are namespaced to the owner type, so that all 
 * entries within that type can use them.
 * 
 * @package Application
 * @subpackage Custom properties
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @method Application_CustomProperties_Presets_Preset getByID($presetID)
 * @method Application_CustomProperties_Presets_Preset[] getAll()
 * @method Application_CustomProperties_FilterCriteria getFilterCriteria()
 * @method Application_CustomProperties_Presets_Preset createNewRecord(array $data=array(), bool $silent=false)
 */
class Application_CustomProperties_Presets extends DBHelper_BaseCollection
{
   /**
    * @var Application_CustomProperties
    */
    protected $properties;
    
    public function bindProperties(Application_CustomProperties $properties)
    {
        $this->properties = $properties;
        
        $this->setForeignKey('owner_type', $properties->getOwnerType());
    }
    
    public function getRecordSearchableColumns()
    {
        return array(
            'name' => t('Name'),
            'label' => t('Label'),
            'default_value' => t('Default value')
        );
    }
    
    public function getRecordClassName()
    {
        return 'Application_CustomProperties_Presets_Preset';
    }
    
    public function getRecordDefaultSortKey()
    {
        return 'label';
    }
    
    public function getRecordFiltersClassName()
    {
        return 'Application_CustomProperties_Presets_FilterCriteria';
    }
    
    public function getRecordFilterSettingsClassName()
    {
        return 'Application_CustomProperties_Presets_FilterSettings';
    }
    
    public function getRecordTypeName()
    {
        return 'preset';
    }
    
    public function getRecordTableName()
    {
        return 'custom_properties_presets';
    }
    
    public function getRecordPrimaryName()
    {
        return 'preset_id';
    }
    
   /**
    * Adds a new preset, and returns the new preset instance.
    * 
    * @param string $label
    * @param string $name
    * @param boolean $isStructural
    * @param boolean $isEditable
    * @param string $defaultValue
    * @return Application_CustomProperties_Presets_Preset
    */
    public function addPreset(string $label, string $name, bool $isStructural=false, bool $isEditable=false, string $defaultValue='') : Application_CustomProperties_Presets_Preset
    {
        return $this->createNewRecord(array(
            'label' => $label,
            'name' => $name,
            'is_structural' => $isStructural,
            'editable' => $isEditable,
            'default_value' => $defaultValue
        ));
    }

    public function getCollectionLabel()
    {
        return t('Custom property presets');
    }

    public function getRecordLabel()
    {
        return t('Custom property preset');
    }

    public function getRecordProperties()
    {
        return array();
    }
}