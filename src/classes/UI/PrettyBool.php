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

    public const LAYOUT_BADGE = 'badge';
    public const LAYOUT_ICON = 'icon';

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
    private $inverted = false;

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

            $icon = UI::icon()->ok()
                ->setTooltip($this->labelTrue)
                ->makeSuccess();

            $this->checkColorsTrue($icon);
        }
        else
        {
            $label = $this->labelFalse;

            $icon = UI::icon()->disabled()
                ->setTooltip($this->labelFalse)
                ->makeDangerous();

            $this->checkColorsFalse($icon);
        }

        if($this->colors === self::COLORS_NEUTRAL)
        {
            $icon->makeMuted();
        }

        return (string)sb()
            ->icon($icon)
            ->add($label);
    }

    private function renderBadge() : string
    {
        $label = UI::label('');

        if($this->bool)
        {
            $label
                ->setLabel($this->labelTrue)
                ->setIcon(UI::icon()->ok())
                ->makeSuccess();

            $this->checkColorsTrue($label);
        }
        else
        {
            $label
                ->setLabel($this->labelFalse)
                ->setIcon(UI::icon()->disabled())
                ->makeDangerous();

            $this->checkColorsFalse($label);
        }

        if($this->colors === self::COLORS_NEUTRAL)
        {
            $label->makeInactive();
        }

        return (string)$label;
    }

    /**
     * @param UI_Icon|UI_Badge $subject
     */
    private function checkColorsTrue($subject) : void
    {
        if($this->inverted)
        {
            $subject->makeDangerous();
            return;
        }

        $this->checkColorType($subject);
    }

    /**
     * @param UI_Icon|UI_Badge $subject
     */
    private function checkColorsFalse($subject) : void
    {
        if($this->inverted)
        {
            $subject->makeSuccess();
            return;
        }

        $this->checkColorType($subject);
    }

    /**
     * @param UI_Icon|UI_Badge $subject
     */
    private function checkColorType($subject) : void
    {
        switch ($this->type)
        {
            case self::TYPE_ACTIVE_INACTIVE:
            case self::TYPE_ENABLED_DISABLED:
                if($subject instanceof UI_Icon)
                {
                    $subject->makeMuted();
                }
                else
                {
                    $subject->makeInactive();
                }
                break;
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
        $this->inverted = false;
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
    public function makeIcon() : UI_PrettyBool
    {
        $this->layout = self::LAYOUT_ICON;
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
}
