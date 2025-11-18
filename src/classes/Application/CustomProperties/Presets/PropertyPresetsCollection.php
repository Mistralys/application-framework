<?php
/**
 * @package Application
 * @subpackage Custom properties
 */

declare(strict_types=1);

namespace Application\CustomProperties\Presets;

use Application\CustomProperties\PropertyFilterCriteria;
use Application_CustomProperties;
use Application\CustomProperties\Presets\PropertyPresetRecord;
use DBHelper_BaseCollection;

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
 * @method PropertyPresetRecord getByID($presetID)
 * @method PropertyPresetRecord[] getAll()
 * @method PropertyFilterCriteria getFilterCriteria()
 * @method PropertyPresetRecord createNewRecord(array $data = array(), bool $silent = false)
 */
class PropertyPresetsCollection extends DBHelper_BaseCollection
{
    const string RECORD_TYPE = 'preset';
    const string TABLE_NAME = 'custom_properties_presets';
    const string PRIMARY_NAME = 'preset_id';

    protected Application_CustomProperties $properties;

    public function bindProperties(Application_CustomProperties $properties): void
    {
        $this->properties = $properties;

        $this->setForeignKey('owner_type', $properties->getOwnerType());
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            'name' => t('Name'),
            'label' => t('Label'),
            'default_value' => t('Default value')
        );
    }

    public function getRecordClassName(): string
    {
        return PropertyPresetRecord::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return 'label';
    }

    public function getRecordFiltersClassName(): string
    {
        return PropertyPresetsFilterCriteria::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return PropertyPresetsFilterSettings::class;
    }

    public function getRecordTypeName(): string
    {
        return self::RECORD_TYPE;
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY_NAME;
    }

    /**
     * Adds a new preset, and returns the new preset instance.
     *
     * @param string $label
     * @param string $name
     * @param boolean $isStructural
     * @param boolean $isEditable
     * @param string $defaultValue
     * @return PropertyPresetRecord
     */
    public function addPreset(string $label, string $name, bool $isStructural = false, bool $isEditable = false, string $defaultValue = ''): PropertyPresetRecord
    {
        return $this->createNewRecord(array(
            'label' => $label,
            'name' => $name,
            'is_structural' => $isStructural,
            'editable' => $isEditable,
            'default_value' => $defaultValue
        ));
    }

    public function getCollectionLabel(): string
    {
        return t('Custom property presets');
    }

    public function getRecordLabel(): string
    {
        return t('Custom property preset');
    }
}
