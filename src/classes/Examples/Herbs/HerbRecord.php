<?php

declare(strict_types=1);

namespace Mistralys\Examples;

use AppUtils\Interfaces\IntegerPrimaryRecordInterface;

class HerbRecord implements IntegerPrimaryRecordInterface
{
    private int $id;
    private string $name;
    private int $grams;
    private bool $local;

    public function __construct(int $id, string $name, int $grams, bool $local)
    {
        $this->id = $id;
        $this->name = $name;
        $this->grams = $grams;
        $this->local = $local;
    }

    public function getID(): int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getGrams() : int
    {
        return $this->grams;
    }

    public function isLocal() : bool
    {
        return $this->local;
    }

    public function dryAndStore() : void
    {
        // Process the item
    }
}