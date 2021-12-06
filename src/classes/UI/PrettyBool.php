<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;

class UI_PrettyBool implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    public const TYPE_TRUE_FALSE = 'true_false';
    public const TYPE_YES_NO = 'yes_no';
    public const TYPE_ENABLED_DISABLED = 'enabled_disabled';
    public const TYPE_ACTIVE_INACTIVE = 'active_inactive';

    public const COLORS_DEFAULT = 'default';
    public const COLORS_NEUTRAL = 'neutral';
    public const COLORS_INVERTED = 'inverted';

    public const LAYOUT_BADGE = 'badge';
    public const LAYOUT_ICON = 'icon';

    public const CRITICALITY_SUCCESS = 'success';
    public const CRITICALITY_WARNING = 'warning';
    public const CRITICALITY_DANGEROUS = 'dangerous';

    /**
     * @var string
     */
    private $criticality = self::CRITICALITY_SUCCESS;

    /**
     * @var bool
     */
    private $bool;

    /**
     * @var string
     */
    private $labelTrue;

    /**
     * @var string
     */
    private $labelFalse;

    /**
     * @var string
     */
    private $type = self::TYPE_TRUE_FALSE;

    /**
     * @var string
     */
    private $colors = self::COLORS_DEFAULT;

    /**
     * @var string
     */
    private $layout = self::LAYOUT_BADGE;

    /**
     * @var bool
     */
    private $falseHasColor = false;

    /**
     * @var bool
     */
    private $iconWithLabel = false;

    /**
     * @var bool
     */
    private $useIcon = true;

    /**
     * @var string
     */
    private $iconTrue;

    /**
     * @var string
     */
    private $iconFalse;

    /**
     * @param string|bool $boolean
     * @throws ConvertHelper_Exception
     */
    public function __construct($boolean)
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

    private function renderIcon() : string
    {
        if($this->bool)
        {
            $label = $this->labelTrue;

            $icon = UI::icon()->enabled()
                ->setTooltip($this->labelTrue)
                ->makeSuccess();
        }
        else
        {
            $label = $this->labelFalse;

            $icon = UI::icon()->disabled()
                ->setTooltip($this->labelFalse)
                ->makeMuted();
        }

        $this->checkColors($icon);

        return (string)sb()
            ->icon($icon)
            ->ifTrue($this->iconWithLabel, $label);
    }

    private function renderBadge() : string
    {
        $label = UI::label('');

        if($this->bool)
        {
            $label->setLabel($this->labelTrue);
            $icon = UI::icon()->ok();
        }
        else
        {
            $label->setLabel($this->labelFalse);
            $icon = UI::icon()->disabled();
        }

        if($this->useIcon)
        {
            $label->setIcon($icon);
        }

        $this->checkColors($label);

        return (string)$label;
    }

    /**
     * @param UI_Icon|UI_Badge $subject
     */
    private function checkColors($subject) : void
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
        $this->type = self::TYPE_YES_NO;
        return $this->setLabels(t('Yes'), t('No'));
    }

    /**
     * Use "Enabled" and "Disabled" as labels for true and false.
     * @return $this
     */
    public function makeEnabledDisabled() : UI_PrettyBool
    {
        $this->type = self::TYPE_ENABLED_DISABLED;
        return $this->setLabels(t('Enabled'), t('Disabled'));
    }

    /**
     * Use "Active" and "Inactive" as labels for true and false.
     * @return $this
     */
    public function makeActiveInactive() : UI_PrettyBool
    {
        $this->type = self::TYPE_ACTIVE_INACTIVE;
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
        $this->iconTrue = $iconTrue->getType();
        $this->iconFalse = $iconFalse->getType();
        return $this;
    }

}
