<?php
/**
 * @package UserInterface
 * @subpackage Templates
 * @see  template_default_frame_header
 */

declare(strict_types=1);

use AppUtils\HTMLTag;
use UI\AppLauncher\AppLauncher;
use UI\CSSClasses;

/**
 * Application logo image, with the AppLauncher functionality
 * if enabled.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class template_default_frame_header_appswitcher extends UI_Page_Template_Custom
{
    private AppLauncher $launcher;

    protected function generateOutput() : void
    {
        $anchor = $this->getLogoAnchor();

        ?>
            <ul class="nav navbar-nav">
                <li>
                    <?php echo $anchor->renderOpen() ?>
                        <img src="<?php echo imageURL('logo-navigation-standalone.png') ?>"
                             alt="<?php pt('Logo of the %1$s app.', $this->driver->getAppNameShort()) ?>"
                        >
                    <?php echo $anchor->renderClose() ?>
                </li>
            </ul>
        <?php
    }

    private function getLogoAnchor() : HTMLTag
    {
        $tag = HTMLTag::create('a')
            ->addClass('brand')
            ->id(nextJSID());

        // No launcher apps defined or enabled: We link to the
        // user's configured starting page (no page = user's page).
        if (!$this->launcher->isEnabled()) {
            return $tag
                ->href(APP_URL)
                ->attr('title', t('Open your configured starting page for the %1$s app.', $this->driver->getAppNameShort()));
        }

        AppLauncher::injectJS($this->ui);

        JSHelper::tooltipify($tag->attributes->getAttribute('id'), JSHelper::TOOLTIP_BOTTOM);

        return $tag
            ->addClass(CSSClasses::CLICKABLE)
            ->attr('title', t('Open the app launcher to choose from the available applications.'))
            ->attr('onclick', $this->launcher->getJSOpen().';return false;');
    }

    protected function preRender(): void
    {
        $this->launcher = AppLauncher::getInstance();
    }
}
