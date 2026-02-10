<?php

declare(strict_types=1);

namespace Application\AppSettings;

use AppUtils\Interfaces\StringPrimaryRecordInterface;

class AppSettingDef implements StringPrimaryRecordInterface
{
    public const string SETTING_TYPE_STRING = 'string';
    public const string SETTING_TYPE_BOOLEAN = 'boolean';
    public const string SETTING_TYPE_INTEGER = 'integer';
    public const string SETTING_TYPE_FLOAT = 'float';

    public string $name;
    public string $type;
    public string $description;

    public function __construct(string $name, string $type, string $description = '')
    {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
    }

    public function getID(): string
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
