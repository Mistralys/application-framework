<?php

declare(strict_types=1);

namespace Application\API\Utilities;

class KeyPath implements KeyPathInterface
{
    /**
     * @var string[]
     */
    private array $parts = array();

    public function __construct(string $component)
    {
        $this->add($component);
    }

    public static function create(string|KeyPath $component) : self
    {
        if($component instanceof KeyPath) {
            return $component;
        }

        return new self($component);
    }

    public function add(string $component) : self
    {
        $this->parts[] = $component;
        return $this;
    }

    public function getPath() : string
    {
        return '/'.implode('.', $this->parts);
    }

    public function __toString() : string
    {
        return $this->getPath();
    }
}
