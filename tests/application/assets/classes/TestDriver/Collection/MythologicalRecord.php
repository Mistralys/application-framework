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
}
