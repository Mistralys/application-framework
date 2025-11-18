<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\Interfaces\StringableInterface;
use UI\TooltipInfo;

class UI_PrettyBool implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    public const string COLORS_DEFAULT = 'default';
    public const string COLORS_NEUTRAL = 'neutral';
    public const string COLORS_INVERTED = 'inverted';

    public const string LAYOUT_BADGE = 'badge';
    public const string LAYOUT_ICON = 'icon';

    public const string CRITICALITY_SUCCESS = 'success';
    public const string CRITICALITY_WARNING = 'warning';
    public const string CRITICALITY_DANGEROUS = 'dangerous';

    private string $criticality = self::CRITICALITY_SUCCESS;
    private bool $bool;
    private string $labelTrue;
    private string $labelFalse;
    private string $colors = self::COLORS_DEFAULT;
    private string $layout = self::LAYOUT_BADGE;
    private bool $falseHasColor = false;
    private bool $iconWithLabel = false;
    private bool $useIcon = true;

    private ?UI_Icon $iconTrue = null;
    private ?UI_Icon $iconFalse = null;
    private ?TooltipInfo $tooltipTrue = null;
    private ?TooltipInfo $tooltipFalse = null;

    /**
     * @param mixed $boolean
     * @throws ConvertHelper_Exception
     */
    public function __construct(mixed $boolean)
    {
        $this->bool = ConvertHelper::string2bool($boolean);

        $this->setLabels(t('True'), t('False'));
    }

    /**
     * @return string
     */
    public function render() : string
    {
        if ($this->layout === self::LAYOUT_ICON)
        {
            return $this->renderIcon();
        }

        return $this->renderBadge();
    }

    public function getIconTrue() : UI_Icon
    {
        return $this->iconTrue ?? UI::icon()->enabled();
    }

    public function getIconFalse() : UI_Icon
    {
        return $this->iconFalse ?? UI::icon()->disabled();
    }

    public function getIcon() : UI_Icon
    {
        if($this->bool) {
            return $this->getIconTrue();
        }

        return $this->getIconFalse();
    }

    public function getLabel() : string
    {
        if($this->bool) {
            return $this->labelTrue;
        }

        return $this->labelFalse;
    }

    public function getTooltip() : string
    {
        if($this->bool && $this->tooltipTrue !== null) {
            return (string)$this->tooltipTrue;
        }

        if(!$this->bool && $this->tooltipFalse !== null) {
            return (string)$this->tooltipFalse;
        }

        return '';
    }

    private function renderIcon() : string
    {
        $icon = $this->getIcon();

        $this->checkColors($icon);

        return (string)sb()
            ->icon($icon->setTooltip($this->getTooltip()))
            ->ifTrue($this->iconWithLabel, $this->getLabel());
    }

    private function renderBadge() : string
    {
        $label = UI::label('');

        if($this->useIcon) {
            $label->setIcon($this->getIcon());
        }

        $this->checkColors($label);

        return (string)$label
            ->setLabel($this->getLabel())
            ->setTooltip($this->getTooltip());
    }

    /**
     * @param UI_Icon|UI_Badge $subject
     */
    private function checkColors(UI_Icon|UI_Badge $subject) : void
    {
        // Default is to render both as muted/inactive,
        // which catches the "inverted" color mode - and
        // by default, the false value is rendered as muted.
        if($subject instanceof UI_Icon)
        {
            $subject->makeMuted();
        }
        else
        {
            $subject->makeInactive();
        }

        if($this->colors === self::COLORS_NEUTRAL)
        {
            return;
        }

        $bool = $this->bool;

        if($this->colors === self::COLORS_INVERTED)
        {
            $bool = !$this->bool;
        }

        if ($bool)
        {
            switch ($this->criticality)
            {
                case self::CRITICALITY_SUCCESS:
                    $subject->makeSuccess();
                    break;

                case self::CRITICALITY_DANGEROUS:
                    $subject->makeDangerous();
                    break;

                case self::CRITICALITY_WARNING:
                    $subject->makeWarning();
                    break;
            }
        }
        else if($this->falseHasColor)
        {
            switch ($this->criticality)
            {
                case self::CRITICALITY_SUCCESS:
                    $subject->makeDangerous();
                    break;

                case self::CRITICALITY_WARNING:
                case self::CRITICALITY_DANGEROUS:
                    $subject->makeSuccess();
                    break;
            }
        }
    }

    /**
     * Invert the colors so that true = dangerous instead
     * of the default false = dangerous.
     *
     * @return $this
     */
    public function makeColorsInverted() : UI_PrettyBool
    {
        $this->colors = self::COLORS_INVERTED;
        return $this;
    }

    /**
     * Sets that true = success, or false = success in inverted mode.
     *
     * @return $this
     */
    public function makeSuccess() : UI_PrettyBool
    {
        return $this->setCriticality(self::CRITICALITY_SUCCESS);
    }

    /**
     * Sets that true = warning, or false = warning in inverted mode.
     *
     * @return $this
     */
    public function makeWarning() : UI_PrettyBool
    {
        return $this->setCriticality(self::CRITICALITY_WARNING);
    }

    /**
     * Sets that true = dangerous, or false = dangerous in inverted mode.
     *
     * @return $this
     */
    public function makeDangerous() : UI_PrettyBool
    {
        return $this->setCriticality(self::CRITICALITY_DANGEROUS);
    }

    /**
     * @param string $criticality
     * @return $this
     */
    public function setCriticality(string $criticality) : UI_PrettyBool
    {
        $this->criticality = $criticality;
        return $this;
    }

    public function getCriticality() : string
    {
        return $this->criticality;
    }

    /**
     * By default, the false state is rendered in an inactive
     * color, so the true state stands out. With this option,
     * both true and false will be colorized.
     *
     * @return UI_PrettyBool
     */
    public function enableFalseColor() : UI_PrettyBool
    {
        $this->falseHasColor = true;
        return $this;
    }

    public function disableIcon(bool $disable=true) : UI_PrettyBool
    {
        $this->useIcon = !$disable;
        return $this;
    }

    /**
     * Turns the layout into a badge (default).
     * @return $this
     */
    public function makeBadge() : UI_PrettyBool
    {
        $this->layout = self::LAYOUT_BADGE;
        return $this;
    }

    /**
     * Turns the layout into an icon only.
     * @return $this
     */
    public function makeIcon(bool $withLabel=true) : UI_PrettyBool
    {
        $this->layout = self::LAYOUT_ICON;
        $this->iconWithLabel = $withLabel;
        return $this;
    }

    /**
     * Use the same colors for true and false.
     * @return $this
     */
    public function makeColorsNeutral() : UI_PrettyBool
    {
        $this->colors = self::COLORS_NEUTRAL;
        return $this;
    }

    /**
     * Use "Yes" and "No" as labels for true and false.
     * @return $this
     */
    public function makeYesNo() : UI_PrettyBool
    {
        return $this->setLabels(t('Yes'), t('No'));
    }

    /**
     * Use "Enabled" and "Disabled" as labels for true and false.
     * @return $this
     */
    public function makeEnabledDisabled() : UI_PrettyBool
    {
        return $this->setLabels(t('Enabled'), t('Disabled'));
    }

    /**
     * Use "Active" and "Inactive" as labels for true and false.
     * @return $this
     */
    public function makeActiveInactive() : UI_PrettyBool
    {
        return $this->setLabels(t('Active'), t('Inactive'));
    }

    /**
     * Set custom labels for true and false.
     *
     * @param string $labelTrue
     * @param string $labelFalse
     * @return $this
     */
    public function setLabels(string $labelTrue, string $labelFalse) : UI_PrettyBool
    {
        $this->labelTrue = $labelTrue;
        $this->labelFalse = $labelFalse;
        return $this;
    }

    public function setIcons(UI_Icon $iconTrue, UI_Icon $iconFalse) : UI_PrettyBool
    {
        $this->iconTrue = $iconTrue;
        $this->iconFalse = $iconFalse;
        return $this;
    }

    /**
     * @param string|int|float|StringableInterface|TooltipInfo|NULL $tooltipTrue
     * @param string|int|float|StringableInterface|TooltipInfo|NULL $tooltipFalse
     * @return self
     * @throws UI_Exception
     */
    public function setTooltip(string|int|float|StringableInterface|TooltipInfo|NULL $tooltipTrue, string|int|float|StringableInterface|TooltipInfo|NULL $tooltipFalse) : self
    {
        $this->tooltipTrue = UI::tooltip($tooltipTrue);
        $this->tooltipFalse = UI::tooltip($tooltipFalse);
        return $this;
    }
}
