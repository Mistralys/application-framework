<?php

declare(strict_types=1);

namespace UI\PropertiesGrid\Property;

use Application\MarkdownRenderer;
use UI_PropertiesGrid_Property_Merged;
use UI_StringBuilder;

class MarkdownGridProperty extends UI_PropertiesGrid_Property_Merged
{
    protected function filterValue($value) : UI_StringBuilder
    {
        return sb()->add(MarkdownRenderer::create()->render((string)$value));
    }
}
