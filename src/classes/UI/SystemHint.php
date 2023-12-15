<?php
/**
 * @package UserInterface
 * @subpackage Helpers
 * @see \UI\SystemHint
 */

declare(strict_types=1);

namespace UI;

use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Traits\ClassableTrait;
use AppUtils\Traits\OptionableTrait;
use UI_Renderable;

/**
 * System-internal hints, for use in the UI.
 *
 * @package UserInterface
 * @subpackage Helpers
 * @see \template_default_ui_system_hint
 */
class SystemHint extends UI_Renderable
    implements
    OptionableInterface,
    ClassableInterface
{
    use OptionableTrait;
    use ClassableTrait;

    public const LAYOUT_SYSTEM = 'system';
    public const LAYOUT_SUCCESS = 'success';
    public const LAYOUT_DEVELOPER = 'developer';
    public const DEFAULT_LAYOUT = self::LAYOUT_SYSTEM;

    public const OPTION_LAYOUT = 'layout';
    public const OPTION_CLASSES = 'classes';
    public const OPTION_CONTENT = 'content';

    /**
     * @return void
     * @see \template_default_ui_system_hint
     */
    protected function _render()
    {
        return $this->ui->createTemplate('ui/system-hint')
            ->setVar('hint', $this)
            ->setVar(self::OPTION_CLASSES, $this->getClasses())
            ->setVars($this->options);
    }

    public function makeSystem() : self
    {
        return $this->setLayout(self::LAYOUT_SYSTEM);
    }

    public function makeSuccess() : self
    {
        return $this->setLayout(self::LAYOUT_SUCCESS);
    }

    public function makeDeveloper() : self
    {
        return $this->setLayout(self::LAYOUT_DEVELOPER);
    }

    public function setLayout(string $layout) : self
    {
        $this->setOption(self::OPTION_LAYOUT, $layout);

        return $this;
    }

    /**
     * @param string|number|StringableInterface|NULL $content
     * @return $this
     */
    public function setContent($content) : self
    {
        return $this->setOption(self::OPTION_CONTENT, $content);
    }

    public function getDefaultOptions(): array
    {
        return array(
            self::OPTION_LAYOUT => self::DEFAULT_LAYOUT,
            self::OPTION_CONTENT => ''
        );
    }
}
