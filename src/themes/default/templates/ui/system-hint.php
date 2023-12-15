<?php

declare(strict_types=1);

use UI\SystemHint;

class template_default_ui_system_hint extends UI_Page_Template_Custom
{
    private const LAYOUT_CLASSES = array(
        SystemHint::LAYOUT_SYSTEM => 'hint-system',
        SystemHint::LAYOUT_SUCCESS => 'hint-success',
        SystemHint::LAYOUT_DEVELOPER => 'hint-developer'
    );

    protected function generateOutput(): void
    {
        ?>
        <div class="hint-message <?php echo implode(' ', $this->getClasses()) ?>">
            <?php echo $this->getStringVar(SystemHint::OPTION_CONTENT) ?>
        </div>
        <?php
    }

    protected function getClasses() : array
    {
        $classes = $this->getArrayVar(SystemHint::OPTION_CLASSES);
        $classes[] = $this->resolveClass();

        return $classes;
    }

    protected function resolveClass() : string
    {
        $layout = $this->getStringVar(SystemHint::OPTION_LAYOUT);
        if(empty($layout)) {
            $layout = SystemHint::DEFAULT_LAYOUT;
        }

        return self::LAYOUT_CLASSES[$layout] ?? '';
    }

    protected function preRender(): void
    {
    }
}
