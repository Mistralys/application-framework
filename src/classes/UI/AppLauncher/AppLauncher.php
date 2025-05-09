<?php
/**
 * @package User Interface
 * @subpackage AppLauncher
 */

declare(strict_types=1);

namespace UI\AppLauncher;

use AppUtils\OutputBuffering;
use UI;
use UI_Renderable_Interface;
use UI_Traits_RenderableGeneric;

/**
 * This class is responsible for managing and rendering the
 * app launcher in the UI.
 *
 * @package User Interface
 * @subpackage AppLauncher
 *
 * @see \template_default_frame_header_appswitcher
 */
class AppLauncher implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    public const THEME_IMAGE_FOLDER = 'app-launcher';
    public const CONTAINER_ID = 'app-launcher-container';

    private static ?AppLauncher $appLauncher = null;
    private string $objectName;

    public static function getInstance() : AppLauncher
    {
        if (!isset(self::$appLauncher)) {
            self::$appLauncher = new self();
        }

        return self::$appLauncher;
    }

    public function __construct()
    {
        $this->objectName = 'AL'.nextJSID();
    }

    public static function injectJS(UI $ui)
    {
        $ui->addJavaScript('app-launcher/launcher.js');
        $ui->addStylesheet('app-launcher/launcher.css');

        $ui->addJavascriptHead(sprintf(
            "const %s = new AppLauncher('%s');",
            self::getInstance()->objectName,
            self::CONTAINER_ID
        ));
    }

    public function isEnabled() : bool
    {
        return true;
    }

    public function getJSOpen() : string
    {
        return sprintf('%s.Show();', $this->objectName);
    }

    public function render(): string
    {
        OutputBuffering::start();

        ?>
        <div id="<?php echo self::CONTAINER_ID ?>" style="display:none;">
            <div class="launcher-apps"></div>
            <div class="launcher-tools"></div>
        </div>
        <?php

        return OutputBuffering::get();
    }
}
