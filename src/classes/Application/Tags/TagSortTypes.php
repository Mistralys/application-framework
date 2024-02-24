<?php

declare(strict_types=1);

namespace Application\Tags;

use AppUtils\Collections\BaseStringPrimaryCollection;

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
    public const SORT_ALPHA = 'alpha';
    public const SORT_WEIGHT = 'weight';
    public const SORT_INHERIT = 'inherit';

    public const SORT_DEFAULT = self::SORT_INHERIT;
    public const SORT_DEFAULT_ROOT = self::SORT_ALPHA;

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

    protected function registerItems(): void
    {
        $this->registerItem(new TagSortType(self::SORT_ALPHA, t('Alphabetical')));
        $this->registerItem(new TagSortType(self::SORT_WEIGHT, t('By weight')));
        $this->registerItem(new TagSortType(self::SORT_INHERIT, t('Inherit from parent')));
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
