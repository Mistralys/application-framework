<?php
/**
 * @package API
 * @subpackage UI
 */

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\API;

use Application\AppFactory;
use UI\Page\Navigation\TextLinksNavigation;
use UI_Page_Navigation;
use UI_Page_Template_Custom;

/**
 * Renders the API Methods meta navigation bar.
 *
 * @package API
 * @subpackage UI
 */
class APIMethodsMetaNav extends UI_Page_Template_Custom
{
    private UI_Page_Navigation $nav;

    protected function preRender(): void
    {
        $nav = new TextLinksNavigation($this->page, 'api-meta-nav');

        $nav->addURL(t('Back to %1$s', $this->driver->getAppNameShort()), APP_URL);
        $nav->addURL(t('API Clients management'), AppFactory::createAPIClients()->adminURL()->list());

        $this->nav = $nav;
    }

    protected function generateOutput(): void
    {
        ?>
        <div class="pull-right">
            <p>
                <?php
                echo $this->nav->render();
                ?>
            </p>
        </div>
        <div class="clear"></div>
        <?php
    }
}
