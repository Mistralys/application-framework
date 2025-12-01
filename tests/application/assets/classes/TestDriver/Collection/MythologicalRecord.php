<?php
/**
 * @package TestDriver
 * @supackage Collection
 */

declare(strict_types=1);

namespace TestDriver\Collection;

use Application\Collection\StringCollectionItemInterface;

/**
 * Collection record with a string as primary key value.
 *
 * @package TestDriver
 * @supackage Collection
 */
class MythologicalRecord implements StringCollectionItemInterface
{
    public const string KEY_ID = 'id';
    public const string KEY_LABEL = 'label';

    private string $id;
    private string $label;

    public function __construct(string $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
    }

    public function getID(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function toArray() : array
    {
        return array(
            self::KEY_ID => $this->getID(),
            self::KEY_LABEL => $this->getLabel(),
        );
    }
}
