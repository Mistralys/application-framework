<?php

declare(strict_types=1);

namespace tests\TestDriver\Admin;

use Application_Admin_Area;
use AppUtils\FileHelper;
use AppUtils\Highlighter;
use TestDriver_User;

/**
 * @property TestDriver_User $user
 */
abstract class BaseArea extends Application_Admin_Area
{
    public static function renderCodeExample(string $file) : string
    {
        $code = FileHelper::readContents($file);

        $start = '// StartExample';
        $end = '// EndExample';

        $code = substr($code, strpos($code, $start) + strlen($start));
        $code = substr($code, 0, strpos($code, $end));
        $code = trim($code);

        return Highlighter::php($code);
    }
}
