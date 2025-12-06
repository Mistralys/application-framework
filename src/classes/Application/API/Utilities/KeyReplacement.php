<?php

declare(strict_types=1);

namespace Application\API\Utilities;

class KeyReplacement implements KeyPathInterface
{
    private string $oldKey;
    private string $newKey;

    public function __construct(string|KeyPath $oldKey, string|KeyPath $newKey)
    {
        $this->oldKey = (string)KeyPath::create($oldKey);
        $this->newKey = (string)KeyPath::create($newKey);
    }

    public function getPath() : string
    {
        return $this->getOldKey();
    }

    public function getOldKey(): string
    {
        return $this->oldKey;
    }

    public function getNewKey(): string
    {
        return $this->newKey;
    }

    public static function create(string|KeyPath $oldKey, string|KeyPath $newKey): self
    {
        return new self($oldKey, $newKey);
    }

    public function __toString(): string
    {
        return $this->oldKey . ' -> ' . $this->newKey;
    }
}
