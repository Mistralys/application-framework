<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

/**
 * @see RecordCreateScreenInterface
 */
trait RecordCreateScreenTrait
{
    public function isUserAllowedEditing(): bool
    {
        return $this->isUserAllowed();
    }

    final public function isEditMode(): bool
    {
        return false;
    }
}
