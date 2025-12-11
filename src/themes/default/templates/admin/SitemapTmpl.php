<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\Themes\DefaultTemplate\Admin;

use Application\Admin\Index\AdminScreenIndex;
use Application\Admin\Index\ScreenDataInterface;
use AppUtils\ConvertHelper\JSONConverter;
use JSHelper;
use UI;
use UI_Page_Template_Custom;

/**
 * Renders the API method detail documentation page.
 *
 * @package Application
 * @subpackage Admin
 */
class SitemapTmpl extends UI_Page_Template_Custom
{
    public const string ELEMENT_ID_TITLE = 'sitemap-detail-title';
    public const string ID_PREFIX_PROPERTY = 'property-value-';

    private string $jsObj;

    protected function preRender(): void
    {
    }

    protected function generateOutput(): void
    {
        $this->jsObj = 'SM'.nextJSID();

        $this->ui->addJavascript('admin/sitemap.js');
        $this->ui->addStylesheet('admin/sitemap.css');

        $this->ui->addJavascriptHead(sprintf(
            "%s = new Sitemap('%s', '%s', %s);",
            $this->jsObj,
            self::ELEMENT_ID_TITLE,
            self::ID_PREFIX_PROPERTY,
            JSONConverter::var2json($this->getProperties())
        ));

        $this->ui->addJavascriptOnload(sprintf(
            "%s.Start();",
            $this->jsObj
        ));

        ?>
        <div style="display: flex; gap: 1rem;">
            <div style="flex: 0 0 60%">
                <?php $this->renderScreens(AdminScreenIndex::getInstance()->getTree(), 0); ?>
            </div>
            <div class="noprint" style="flex: 1 1 auto; position: sticky; top: 1rem; align-self: start; max-height: calc(100vh - 2rem); overflow: auto">
                <h2 id="<?php echo self::ELEMENT_ID_TITLE ?>" style="margin-top:0"></h2>
                <?php
                $grid = $this->ui->createPropertiesGrid();
                foreach($this->getProperties() as $key => $label) {
                    $grid->add($label, '<span id="'.self::ID_PREFIX_PROPERTY.$key.'"></span>');
                }
                echo $grid;
                ?>
            </div>
        </div>
        <?php
    }

    private ?array $properties = null;

    private function getProperties() : array
    {
        if(isset($this->properties)) {
            return $this->properties;
        }

        $this->properties = array(
            ScreenDataInterface::KEY_SCREEN_URL_PATH => t('URL Path'),
            ScreenDataInterface::KEY_SCREEN_REQUIRED_RIGHT => t('Required Right'),
            ScreenDataInterface::KEY_SCREEN_CLASS => t('Class Name'),
            ScreenDataInterface::KEY_SCREEN_PATH => t('Path'),
        );

        return $this->properties;
    }

    private function resolveScreenID(array $screenDef) : string
    {
        return str_replace('.', '_', $screenDef[ScreenDataInterface::KEY_SCREEN_URL_PATH]);
    }

    private function renderScreens(array $screens, int $depth) : void
    {
        usort($screens, static function(array $a, array $b) {
            return strnatcasecmp(
                $a[ScreenDataInterface::KEY_SCREEN_TITLE],
                $b[ScreenDataInterface::KEY_SCREEN_TITLE]
            );
        });

        ?>
        <ul class="nav nav-pills nav-stacked sitemap-screens depth-<?php echo $depth; ?>">
            <?php
            foreach($screens as $screenDef)
            {
                $screenID = $this->resolveScreenID($screenDef);

                $this->ui->addJavascriptHead(sprintf(
                    "%s.RegisterScreen('%s', %s, %s);",
                    $this->jsObj,
                    $screenID,
                    JSHelper::phpVariable2JS($screenDef[ScreenDataInterface::KEY_SCREEN_TITLE]),
                    JSONConverter::var2json($this->compileValues($screenDef)),
                ));

                ?>
                <li class="sitemap-entry">
                    <a href="#"
                       onclick="return false;"
                       onmouseenter="<?php echo $this->jsObj ?>.ShowScreenInfo('<?php echo $screenID ?>');"
                       onmouseleave="<?php echo $this->jsObj ?>.ClearScreenInfo();">
                        <?php
                        if($depth === 0) {
                            echo UI::icon()->collapseRight(). ' ';
                        } else {
                            echo '• ';
                        }
                        echo $screenDef[ScreenDataInterface::KEY_SCREEN_TITLE];
                        ?>
                    </a>
                    <?php
                    if (!empty($screenDef[ScreenDataInterface::KEY_SCREEN_SUBSCREENS])) {
                        $this->renderScreens($screenDef[ScreenDataInterface::KEY_SCREEN_SUBSCREENS], $depth + 1);
                    }

                    if($depth === 0) {
                        ?>
                        <hr>
                        <?php
                    }
                    ?>

                </li>
                <?php
            }
        ?>
        </ul>
        <?php
    }

    private function compileValues(array $screenDef) : array
    {
        $result = array();

        foreach($this->getProperties() as $key => $label)
        {
            $value = $screenDef[$key] ?? '';

            if ($key === ScreenDataInterface::KEY_SCREEN_URL_PATH) {
                $value = str_replace('.', ' '.sb()->info('.').' ', $value);
            } else if($key === ScreenDataInterface::KEY_SCREEN_CLASS) {
                $value = str_replace(array('\\', '_'), ' '.sb()->info('\\').' ', $value);
            } else if($key === ScreenDataInterface::KEY_SCREEN_PATH) {
                $value = str_replace('/', ' '.sb()->info('/').' ', $value);
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
