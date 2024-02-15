<?php
/**
 * @package Application
 * @subpackage Tags
 */

declare(strict_types=1);

namespace Application\Tags;

use Application\AppFactory;
use Application\Area\BaseTagsScreen;
use Application_Admin_ScreenInterface;
use Application_Formable;
use Application\Area\Tags\BaseCreateTagScreen;
use Application\Area\Tags\BaseTagListScreen;
use DBHelper;
use DBHelper_BaseCollection;

/**
 * @package Application
 * @subpackage Tags
 *
 * @method TagRecord createNewRecord(array $data = array(), bool $silent = false, array $options = array())
 * @method TagCriteria getFilterCriteria()
 * @method TagFilterSettings getFilterSettings()
 * @method TagRecord getByID(int $record_id)
 */
class TagCollection extends DBHelper_BaseCollection
{
    public const TABLE_NAME = 'tags';
    public const TABLE_REGISTRY = 'tags_registry';
    public const PRIMARY_NAME = 'tag_id';
    public const COL_LABEL = 'label';
    public const COL_PARENT_TAG_ID = 'parent_tag_id';

    // region: X - Interface methods

    public function getRecordFiltersClassName(): string
    {
        return TagCriteria::class;
    }

    public function getRecordTypeName(): string
    {
        return 'tag';
    }

    public function getRecordClassName(): string
    {
        return TagRecord::class;
    }

    public function getRecordFilterSettingsClassName(): string
    {
        return TagFilterSettings::class;
    }

    public function getRecordDefaultSortKey(): string
    {
        return self::COL_LABEL;
    }

    public function getRecordSearchableColumns(): array
    {
        return array(
            self::COL_LABEL => t('Label')
        );
    }

    public function getRecordTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getRecordPrimaryName(): string
    {
        return self::PRIMARY_NAME;
    }

    public function getCollectionLabel(): string
    {
        return t('Tags');
    }

    public function getRecordLabel(): string
    {
        return t('Tag');
    }

    public function getRecordProperties(): array
    {
        return array();
    }

    // endregion

    public function createNewTag(string $name, ?TagRecord $parent=null) : TagRecord
    {
        $parentID = null;
        if($parent !== null) {
            $parentID = $parent->getID();
        }

        return $this->createNewRecord(array(
            self::COL_LABEL => $name,
            self::COL_PARENT_TAG_ID => $parentID
        ));
    }

    public function createSettingsManager(Application_Formable $formable, ?TagRecord $record) : TagSettingsManager
    {
        return new TagSettingsManager($formable, $this, $record);
    }

    public function getAdminCreateSubTagURL(TagRecord $parentTag, array $params=array()) : string
    {
        $params[BaseCreateTagScreen::REQUEST_PARAM_PARENT_TAG] = $parentTag->getID();

        return $this->getAdminCreateURL($params);
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_LABEL)
            ->makeRequired();
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE] = BaseTagsScreen::URL_NAME;

        return AppFactory::createRequest()
            ->buildURL($params);
    }

    public function getAdminListURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseTagListScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminCreateURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseCreateTagScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public static function getTagIDChain(int $id, array $result=array()) : array
    {
        $statement = AppFactory::createTags()
            ->getFilterCriteria()
            ->statement('SELECT * FROM {table_tags} WHERE {tag_primary} = :primary');

        $data = DBHelper::fetch($statement, array('primary' => $id));

        if(empty($data)) {
            return $result;
        }

        $result[] = $id;

        if(!empty($data[self::COL_PARENT_TAG_ID])) {
            return self::getTagIDChain((int)$data[self::COL_PARENT_TAG_ID], $result);
        }

        return $result;
    }

    private static ?bool $tableExists = null;

    public static function tableExists() : bool
    {
        if(!isset(self::$tableExists)) {
            self::$tableExists = DBHelper::tableExists(self::TABLE_NAME);
        }

        return self::$tableExists;
    }
}
