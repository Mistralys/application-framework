<?php

declare(strict_types=1);

namespace UI\AppLauncher;

use AppUtils\Interfaces\StringPrimaryRecordInterface;
use UI\Themes\ThemeImage;
use UI_Icon;
use UI_Interfaces_Conditional;

interface LauncherAppInterface extends StringPrimaryRecordInterface, UI_Interfaces_Conditional
{
    public function getIcon() : ?UI_Icon;
    public function getLogoImage() : ThemeImage;
    public function getLabel() : string;
    public function getDescription() : string;
}
