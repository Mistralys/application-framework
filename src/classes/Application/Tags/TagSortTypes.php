<?php

declare(strict_types=1);

namespace Application\Tags;

use AppUtils\Collections\BaseStringPrimaryCollection;
use DBHelper;

/**
 * @package Application
 * @subpackage Tags
 *
 * @method TagSortType getByID(string $id)
 * @method TagSortType[] getAll()
 * @method TagSortType getDefault()
 */
class TagSortTypes extends BaseStringPrimaryCollection
{
    public const SORT_ALPHA_ASC = 'alpha_asc';
    public const SORT_ALPHA_DESC = 'alpha_desc';
    public const SORT_WEIGHT_ASC = 'weight_asc';
    public const SORT_WEIGHT_DESC = 'weight_desc';
    public const SORT_INHERIT = 'inherit';

    public const SORT_DEFAULT = self::SORT_INHERIT;
    public const SORT_DEFAULT_ROOT = self::SORT_ALPHA_ASC;

    public static ?TagSortTypes $instance = null;

    public static function getInstance() : TagSortTypes
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getDefaultID(): string
    {
        return self::SORT_DEFAULT;
    }

    public function getWeightASC() : TagSortType
    {
        return $this->getByID(self::SORT_WEIGHT_ASC);
    }

    public function getWeightDESC() : TagSortType
    {
        return $this->getByID(self::SORT_WEIGHT_DESC);
    }

    public function getAlphaASC() : TagSortType
    {
        return $this->getByID(self::SORT_ALPHA_ASC);
    }

    public function getAlphaDESC() : TagSortType
    {
        return $this->getByID(self::SORT_ALPHA_DESC);
    }

    protected function registerItems(): void
    {
        $colLabel = DBHelper::escapeTableColumn(TagCollection::TABLE_NAME, TagCollection::COL_LABEL);
        $colWeight = DBHelper::escapeTableColumn(TagCollection::TABLE_NAME, TagCollection::COL_WEIGHT);

        $this->registerItem(new TagSortType(self::SORT_ALPHA_ASC, $colLabel, t('Alphabetical, ascending')));
        $this->registerItem(new TagSortType(self::SORT_ALPHA_DESC, $colLabel, t('Alphabetical, descending'), false));
        $this->registerItem(new TagSortType(self::SORT_WEIGHT_ASC, $colWeight, t('By weight, ascending')));
        $this->registerItem(new TagSortType(self::SORT_WEIGHT_DESC, $colWeight, t('By weight, descending'), false));
        $this->registerItem(new TagSortType(self::SORT_INHERIT, null, t('Inherit from parent')));
    }

    public function getDefaultForRootID() : string
    {
        return self::SORT_DEFAULT_ROOT;
    }

    public function getDefaultForRoot() : TagSortType
    {
        return $this->getByID($this->getDefaultForRootID());
    }

    public function getForRoot() : array
    {
        $items = $this->getAll();
        $result = array();

        foreach($items as $item) {
            if ($item->getID() !== self::SORT_INHERIT) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
