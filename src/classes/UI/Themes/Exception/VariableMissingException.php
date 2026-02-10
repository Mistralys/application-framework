<?php

declare(strict_types=1);

namespace UI\Themes\Exception;

use UI\Interfaces\PageTemplateInterface;
use UI_Themes_Exception;

class VariableMissingException extends UI_Themes_Exception
{
    public const int ERROR_CODE = 104301;

    public function __construct(string $varName, PageTemplateInterface $template)
    {
        parent::__construct(
            'Template variable missing',
            sprintf(
                'Variable [%s] not set in template [%s].',
                $varName,
                get_class($template)
            ),
            self::ERROR_CODE
        );
    }
}
