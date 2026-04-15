<?php
/**
 * @package API
 * @subpackage UI
 */

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\API;

use Application\AppFactory;
use Application\API\OpenAPI\GetOpenAPISpec;
use UI\Page\Navigation\TextLinksNavigation;
use UI_Page_Navigation;
use UI_Page_Template_Custom;

/**
 * Renders the API Methods meta navigation bar.
 *
 * Displayed on both the API overview page (via {@see APIMethodsOverviewTmpl}) and
 * individual method detail pages (via {@see APIMethodDetailTmpl}). The bar contains
 * three links:
 *
 * 1. **Back to {AppName}** — returns to the application root (`APP_URL`).
 * 2. **API Clients management** — goes to the API Clients admin list.
 * 3. **OpenAPI Specification** — link to the `GetOpenAPISpec` API method endpoint,
 *    which serves the pre-generated `api/openapi.json` as raw JSON over HTTP.
 *    URL is built via {@see GetOpenAPISpec::getSpecURL()}.
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
        $nav->addURL(t('OpenAPI Specification'), GetOpenAPISpec::getSpecURL());

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
