<?php
/**
 * File containing the {@link Application_CustomProperties} class.
 * 
 * @package Application
 * @subpackage Custom properties
 * @see Application_CustomProperties
 */

use Application\CustomProperties\Presets\PropertyPresetRecord;
use Application\CustomProperties\Presets\PropertyPresetsCollection;
use Application\CustomProperties\PropertyFilterCriteria;
use Application\CustomProperties\PropertyFilterSettings;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use DBHelper\Interfaces\DBHelperRecordInterface;

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
    public const string TABLE_NAME = 'custom_properties';
    public const string PRIMARY_NAME = 'property_id';
    public const string RECORD_TYPE_NAME = 'property';
    public const string TABLE_NAME_DATA = 'custom_properties_data';

    protected Application_Interfaces_Propertizable $record;
    protected string $ownerType;
    protected string $ownerKey;
    
    public function bindRecord(Application_Interfaces_Propertizable $record) : void
    {
        $this->record = $record;
        $this->ownerType = $record->getPropertiesOwnerType();
        $this->ownerKey = $record->getPropertiesOwnerKey();
        
        $this->setForeignKey('owner_type', $this->ownerType);
        $this->setForeignKey('owner_key', $this->ownerKey);
        
        $this->setIDTable(self::TABLE_NAME);
    }
    
    public function getRecord() : Application_Interfaces_Propertizable
    {
        return $this->record;
    }
    
    public function getOwnerType() : string
    {
        return $this->ownerType;
    }
    
    public function getOwnerKey(): string
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
        return Application_CustomProperties_Property::class;
    }
    
    public function getRecordDefaultSortKey() : string
    {
        return 'label';
    }
    
    public function getRecordFiltersClassName() : string
    {
        return PropertyFilterCriteria::class;
    }
    
    public function getRecordFilterSettingsClassName() : string
    {
        return PropertyFilterSettings::class;
    }
    
    public function getRecordTypeName() : string
    {
        return self::RECORD_TYPE_NAME;
    }
    
    public function getRecordTableName() : string
    {
        return self::TABLE_NAME_DATA;
    }
    
    public function getRecordPrimaryName() : string
    {
        return self::PRIMARY_NAME;
    }
    
   /**
    * Adds a new property to the collection, and returns the new instance.
    * 
    * @param string $label
    * @param string $name
    * @param string $value
    * @param string $defaultValue
    * @param boolean $isStructural
    * @param PropertyPresetRecord|NULL $preset
    * @return Application_CustomProperties_Property
    */
    public function addProperty(string $label, string $name, string $value, string $defaultValue='', bool $isStructural=false, ?PropertyPresetRecord $preset=null) : Application_CustomProperties_Property
    {
        $property = $this->createNewRecord(array(
            'label' => $label,
            'name' => ConvertHelper::transliterate($name),
            'value' => $value,
            'default_value' => $defaultValue,
            'is_structural' => ConvertHelper::bool2string($isStructural, true),
            'preset_id' => $preset?->getID()
        ));
        
        $this->record->handle_propertyCreated($property);
        
        return $property;
    }
    
    protected ?PropertyPresetsCollection $presets = null;
    
   /**
    * Creates the presets collection used to manage presets for
    * this collection's owner type.
    * 
    * @return PropertyPresetsCollection
    */
    public function getPresets() : PropertyPresetsCollection
    {
        if(!isset($this->presets)) {
            $this->presets = new PropertyPresetsCollection();
            $this->presets->bindProperties($this);
        }
        
        return $this->presets;
    }
    
    protected array $injected = array();
    
   /**
    * Injects the javascript code to enable clientside support
    * for this property collection.
    * 
    * @param UI $ui
    */
    public function injectJS(UI $ui) : void
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
    
    protected ?string $jsCollectionVarName = null;
    
    public function getJSCollectionVarName() : string
    {
        if(!isset($this->jsCollectionVarName)) {
            $this->jsCollectionVarName = 'CP'.nextJSID();
        }
        
        return $this->jsCollectionVarName;
    }

    protected static array $librariesInjected = array();
    
   /**
    * Injects only the required javascript files for the 
    * custom properties clientside support. This is done
    * once per UI object.
    * 
    * @param UI $ui
    */
    public static function injectLibraries(UI $ui) : void
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
    public static function resolveOwner(string $ownerType, string $ownerKey) : ?Application_Interfaces_Propertizable
    {
        return Application_Driver::getInstance()->resolveCustomPropertiesOwner($ownerType, $ownerKey);
    }
    
   /**
    * Utility method used to migrate properties when owner keys change. Copies
    * all existing properties using the specified keys table.
    * 
    * @param string $ownerType
    * @param array<string,string> $keyTable Associative array with source key => target key pairs
    */
    public static function copyRecords(string $ownerType, array $keyTable): void
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
                self::TABLE_NAME_DATA,
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
                    self::TABLE_NAME_DATA,
                    $record, 
                    array(self::PRIMARY_NAME, 'owner_type', 'owner_key')
                );
            }

            Application::log(sprintf('CustomProperties [%s] | Copied [%s] properties for key [%s -> %s].', $ownerType, count($records), $sourceKey, $targetKey));
        }
    }
    
    public static function deleteKeys(string $ownerType, array $keys) : void
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
    
    public static function findProperties(string $ownerType, $ownerKeySearch) : array
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
    
    public function deleteRecord(DBHelperRecordInterface $record, bool $silent=false) : void
    {
        $property = ClassHelper::requireObjectInstanceOf(
            Application_CustomProperties_Property::class,
            $record
        );
        
        parent::deleteRecord($property);
        
        $this->record->handle_propertyDeleted($property);
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