<?php

declare(strict_types=1);

namespace UI\PropertiesGrid\Property;

use Parsedown;
use UI_PropertiesGrid_Property_Merged;
use UI_StringBuilder;

class MarkdownGridProperty extends UI_PropertiesGrid_Property_Merged
{
    protected function filterValue($value) : UI_StringBuilder
    {
        $parser = new Parsedown();
        $parser->setSafeMode(true);

        return sb()->add($parser->parse((string)$value));
    }
}
