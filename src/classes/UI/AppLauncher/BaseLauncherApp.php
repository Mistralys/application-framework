<?php

declare(strict_types=1);

namespace UI\AppLauncher;

use UI\Themes\ThemeImage;
use UI_Traits_Conditional;

abstract class BaseLauncherApp implements LauncherAppInterface
{
    use UI_Traits_Conditional;

    private ?ThemeImage $logoImage = null;

    public function getLogoImage(): ThemeImage
    {
        if(!isset($this->logoImage)) {
            $this->logoImage = ThemeImage::create(AppLauncher::THEME_IMAGE_FOLDER.'/'.$this->_getLogoFileName().'.png');
        }

        return $this->logoImage;
    }

    abstract protected function _getLogoFileName() : string;
}
