<?php
/**
 * File containing the {@link Application_CustomProperties} class.
 * 
 * @package Application
 * @subpackage Custom properties
 * @see Application_CustomProperties
 */

use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;

/**
 * Manages the collection of custom properties for the specified
 * owner type and key: The owner type being the type of records
 * for which to store properties, e.g. products. The owner key
 * is a namespace within the owner type. For example a product
 * id, or a compound key. 
 * 
 * To add the properties collection to a revisionable, DB item or
 * other records, the following conditions have to be met:
 * 
 * 1) Use the trait {@link Application_Traits_Propertizable} 
 * and implement the {@link Application_Interfaces_Propertizable} 
 * interface in the propertizable records.
 * 
 * 2) In the driver implementation, add a method to retrieve
 * the instance of the owner record of a custom property. See
 * the {@link Application_Driver::resolveCustomPropertiesOwner()}
 * method for details. 
 * 
 * @package Application
 * @subpackage Custom properties
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @method Application_CustomProperties_Property[] getAll()
 * @method Application_CustomProperties_Property getByID($property_id)
 * @method Application_CustomProperties_Property createNewRecord(array $data=array(), bool $silent=false)
 */
class Application_CustomProperties extends DBHelper_BaseCollection
{
   /**
    * @var Application_Interfaces_Propertizable
    */
    protected $record;
    
    protected $ownerType;
    
    protected $ownerKey;
    
    public function bindRecord(Application_Interfaces_Propertizable $record)
    {
        $this->record = $record;
        $this->ownerType = $record->getPropertiesOwnerType();
        $this->ownerKey = $record->getPropertiesOwnerKey();
        
        $this->setForeignKey('owner_type', $this->ownerType);
        $this->setForeignKey('owner_key', $this->ownerKey);
        
        $this->setIDTable('custom_properties');
    }
    
   /**
    * @return Application_Interfaces_Propertizable
    */
    public function getRecord()
    {
        return $this->record;
    }
    
    public function getOwnerType()
    {
        return $this->ownerType;
    }
    
    public function getOwnerKey()
    {
        return $this->ownerKey;
    }
    
    public function getRecordSearchableColumns() : array
    {
        return array(
            'name' => t('Name'),
            'label' => t('Label'),
            'value' => t('Value'),
            'default_value' => t('Default value')
        );
    }
    
    public function getRecordClassName() : string
    {
        return 'Application_CustomProperties_Property';
    }
    
    public function getRecordDefaultSortKey() : string
    {
        return 'label';
    }
    
    public function getRecordFiltersClassName() : string
    {
        return 'Application_CustomProperties_FilterCriteria';
    }
    
    public function getRecordFilterSettingsClassName() : string
    {
        return 'Application_CustomProperties_FilterSettings';
    }
    
    public function getRecordTypeName() : string
    {
        return 'property';
    }
    
    public function getRecordTableName() : string
    {
        return 'custom_properties_data';
    }
    
    public function getRecordPrimaryName() : string
    {
        return 'property_id';
    }
    
   /**
    * Adds a new property to the collection, and returns the new instance.
    * 
    * @param string $label
    * @param string $name
    * @param string $value
    * @param string $defaultValue
    * @param boolean $isStructural
    * @param Application_CustomProperties_Presets_Preset $preset
    * @return Application_CustomProperties_Property
    */
    public function addProperty($label, $name, $value, $defaultValue='', $isStructural=false, Application_CustomProperties_Presets_Preset $preset=null)
    {
        $preset_id = null;
        if($preset) {
            $preset_id = $preset->getID();
        }
        
        $property = $this->createNewRecord(array(
            'label' => $label,
            'name' => ConvertHelper::transliterate($name),
            'value' => $value,
            'default_value' => $defaultValue,
            'is_structural' => AppUtils\ConvertHelper::bool2string($isStructural, true),
            'preset_id' => $preset_id
        ));
        
        $this->record->handle_propertyCreated($property);
        
        return $property;
    }
    
   /**
    * @var Application_CustomProperties_Presets
    */
    protected $presets;
    
   /**
    * Creates the presets collection used to manage presets for
    * this collection's owner type.
    * 
    * @return Application_CustomProperties_Presets
    */
    public function getPresets()
    {
        if(!isset($this->presets)) {
            require_once 'Application/CustomProperties/Presets.php';
            $this->presets = new Application_CustomProperties_Presets();
            $this->presets->bindProperties($this);
        }
        
        return $this->presets;
    }
    
    protected $injected = array();
    
   /**
    * Injects the javascript code to enable clientside support
    * for this properties collection.
    * 
    * @param UI $ui
    */
    public function injectJS(UI $ui)
    {
        self::injectLibraries($ui);
        
        $instanceID = $ui->getInstanceKey();
        if(isset($this->injected[$instanceID])) {
            return;
        }
        
        $this->injected[$instanceID] = true;
        
        $varName = $this->getJSCollectionVarName();
        
        $ui->addJavascriptHeadStatement(
            sprintf('var %s = new Application_CustomProperties_Collection', $varName),
            $this->getOwnerType(),
            $this->getOwnerKey(),
            $this->record->getPropertiesTypeNameSingular(),
            $this->record->getPropertiesTypeNamePlural(),
            $this->record->isPropertiesOwnerPublishable()
        );
        
        $properties = $this->getAll();
        foreach($properties as $property) {
            $property->injectJS($ui);
        }
    }
    
    protected $jsCollectionVarName;
    
    public function getJSCollectionVarName()
    {
        if(!isset($this->jsCollectionVarName)) {
            $this->jsCollectionVarName = 'CP'.nextJSID();
        }
        
        return $this->jsCollectionVarName;
    }

    protected static $librariesInjected = array();
    
   /**
    * Injects only the required javascript files for the 
    * custom properties clientside support. This is done
    * once per UI object.
    * 
    * @param UI $ui
    */
    public static function injectLibraries(UI $ui)
    {
        $key = $ui->getInstanceKey();
        if(isset(self::$librariesInjected[$key])) {
            return;
        }
        
        self::$librariesInjected[$key] = true;
        
        $ui->addJavascript('application/custom_properties/collection.js');
        $ui->addJavascript('application/custom_properties/property.js');
        $ui->addJavascript('application/custom_properties/dialog.js');
        $ui->addJavascript('application/custom_properties/dialog/list.js');
        $ui->addJavascript('application/custom_properties/dialog/add.js');
        $ui->addJavascript('application/custom_properties/dialog/edit.js');
        $ui->addJavascript('application/custom_properties/dialog/delete.js');
    }
    
   /**
    * Attempts to find and return the object instance of a property.
    * @param string $ownerType
    * @param string $ownerKey
    * @return Application_Interfaces_Propertizable|NULL
    */
    public static function resolveOwner($ownerType, $ownerKey)
    {
        return Application_Driver::getInstance()->resolveCustomPropertiesOwner($ownerType, $ownerKey);
    }
    
   /**
    * Utility method used to migrate properties when owner keys change. Copies
    * all existing properties using the specified keys table.
    * 
    * @param string $ownerType
    * @param array $keyTable Associative array with source key => target key pairs
    */
    public static function copyRecords($ownerType, $keyTable)
    {
        Application::log(sprintf('CustomProperties [%s] | Copying records for [%s] keys.', $ownerType, count($keyTable)));
        
        foreach($keyTable as $sourceKey => $targetKey) 
        {
            // each owner key can have any number of properties,
            // we fetch them all.
            $records = DBHelper::fetchAll(
                "SELECT
                    *
                FROM
                    `custom_properties_data`
                WHERE
                    `owner_type`=:owner_type
                AND
                    `owner_key`=:owner_key",
                array(
                    'owner_type' => $ownerType,
                    'owner_key' => $sourceKey
                )
            );
            
            // clear existing records in the target key
            // to enable deleting properties.
            DBHelper::deleteRecords(
                'custom_properties_data',
                array(
                    'owner_type' => $ownerType,
                    'owner_key' => $targetKey
                )
            );
            
            if(empty($records)) {
                Application::log(sprintf('CustomProperties [%s] | Source key [%s] not found, skipping.', $ownerType, $sourceKey));
                continue;
            }
            
            foreach($records as $record) 
            {
                $record['owner_key'] = $targetKey;
                
                DBHelper::insertOrUpdate(
                    'custom_properties_data', 
                    $record, 
                    array('property_id', 'owner_type', 'owner_key')
                );
            }

            Application::log(sprintf('CustomProperties [%s] | Copied [%s] properties for key [%s -> %s].', $ownerType, count($records), $sourceKey, $targetKey));
        }
    }
    
    public static function deleteKeys($ownerType, $keys)
    {
        if(empty($keys)) {
            return;
        }
        
        Application::log(sprintf('Custom properties [%s] | Deleting [%s] keys.', $ownerType, count($keys)));
        
        DBHelper::delete(
            "DELETE FROM
                `custom_properties_data`
            WHERE
                `owner_type`=:owner_type
            AND
                `owner_key` IN('".implode("','", $keys)."')",
            array(
                'owner_type' => $ownerType
            )
        );
    }
    
    public static function findProperties($ownerType, $ownerKeySearch)
    {
        return DBHelper::fetchAll(
            "SELECT
                `property_id`,
                `owner_type`,
                `owner_key`
            FROM
                `custom_properties_data`
            WHERE
                `owner_type`=:owner_type
            AND
                `owner_key` LIKE '".$ownerKeySearch."'",
            array(
                'owner_type' => $ownerType
            )
        );
    }
    
    public function deleteRecord(DBHelper_BaseRecord $record, bool $silent=false) : void
    {
        $record = ClassHelper::requireObjectInstanceOf(
            Application_CustomProperties_Property::class,
            $record
        );
        
        parent::deleteRecord($record);
        
        $this->record->handle_propertyDeleted($record);
    }
    
    public function getCollectionLabel() : string
    {
        return t('Custom properties');
    }

    public function getRecordLabel() : string
    {
        return t('Custom property');
    }

    public function getRecordProperties() : array
    {
        return array();
    }
}