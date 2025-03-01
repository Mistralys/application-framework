<?php
/**
 * @package Tagging
 * @subpackage Collection
 */

declare(strict_types=1);

namespace Application\Tags;

use Application\AppFactory;
use Application\Area\BaseTagsScreen;
use Application\Exception\DisposableDisposedException;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Tags\Taggables\TaggableInterface;
use Application_Formable;
use Application\Area\Tags\BaseCreateTagScreen;
use Application\Area\Tags\BaseTagListScreen;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use DBHelper;
use DBHelper_BaseCollection;
use DBHelper_Exception;
use UI;

/**
 * @package Tagging
 * @subpackage Collection
 *
 * @method TagCriteria getFilterCriteria()
 * @method TagFilterSettings getFilterSettings()
 * @method TagRecord getByID(int $record_id)
 */
class TagCollection extends DBHelper_BaseCollection
{
    public const ERROR_CANNOT_CREATE_ROOT_WITH_INHERIT_SORTING = 152201;

    public const TABLE_NAME = 'tags';
    public const TABLE_REGISTRY = 'tags_registry';
    public const PRIMARY_NAME = 'tag_id';
    public const COL_LABEL = 'label';
    public const COL_PARENT_TAG_ID = 'parent_tag_id';
    public const COL_SORT_TYPE = 'sort_type';
    public const COL_SORT_WEIGHT = 'sort_weight';
    public const COL_WEIGHT = 'weight';

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

    /**
     * @param array<string,mixed> $data
     * @param bool $silent
     * @param array<string,mixed> $options
     * @return TagRecord
     *
     * @throws BaseClassHelperException
     * @throws DisposableDisposedException
     * @throws DBHelper_Exception
     */
    public function createNewRecord(array $data = array(), bool $silent = false, array $options = array()) : TagRecord
    {
        return ClassHelper::requireObjectInstanceOf(
            TagRecord::class,
            parent::createNewRecord($this->injectSortingDefault($data), $silent, $options)
        );
    }

    private function injectSortingDefault(array $data) : array
    {
        $root = empty($data[self::COL_PARENT_TAG_ID]);

        if($root) {
            $sortDefault = TagSortTypes::getInstance()->getDefaultForRootID();
        } else {
            $sortDefault = TagSortTypes::getInstance()->getDefaultID();
        }

        if(empty($data[self::COL_SORT_TYPE])) {
            $data[self::COL_SORT_TYPE] = $sortDefault;
            return $data;
        }

        if($data[self::COL_SORT_TYPE] !== TagSortTypes::SORT_INHERIT) {
            return $data;
        }

        throw new TaggingException(
            'Cannot create a root tag set to inherit sorting options.',
            '',
            self::ERROR_CANNOT_CREATE_ROOT_WITH_INHERIT_SORTING
        );
    }

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

    public function injectJS() : void
    {
        UI::getInstance()->addJavascript('ui/tags/tagging-dialog.js');
    }

    public function createCollectionRegistry() : TagCollectionRegistry
    {
        return TagCollectionRegistry::getInstance();
    }

    public function getTaggableByUniqueID(string $uniqueID) : TaggableInterface
    {
        return $this->createCollectionRegistry()->getTaggableByUniqueID($uniqueID);
    }

    public function uniqueIDExists(string $uniqueID) : bool
    {
        return $this->createCollectionRegistry()->uniqueIDExists($uniqueID);
    }

    private ?ClientsideTagging $clientside = null;

    public function createClientsideTagging() : ClientsideTagging
    {
        if(!isset($this->clientside)) {
            $this->clientside = new ClientsideTagging(AppFactory::createUI());
        }

        return $this->clientside;
    }

    protected function _registerKeys(): void
    {
        $this->keys->register(self::COL_LABEL)
            ->makeRequired();

        $this->keys->register(self::COL_SORT_TYPE)
            ->makeRequired();
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_PAGE] = BaseTagsScreen::URL_NAME;

        return AppFactory::createRequest()
            ->buildURL($params);
    }

    public function getAdminListURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = BaseTagListScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminCreateURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = BaseCreateTagScreen::URL_NAME;

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
