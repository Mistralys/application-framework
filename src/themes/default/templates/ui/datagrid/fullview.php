<?php
/**
 * File containing the template class {@see template_default_ui_datagrid_fullview}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_ui_datagrid_fullview
 */

declare(strict_types=1);

/**
 * Generates the HTML for the dynamically opened full view data grid.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_AjaxMethods_GetGridFullViewHTML
 */
class template_default_ui_datagrid_fullview extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
?><!DOCTYPE html>
<html lang="en">
    <head>
    	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    	<title><?php pt('%1$s - %2$s', $this->driver->getAppNameShort(), $this->title) ?></title>
    	<?php echo $this->ui->renderHeadIncludes() ?>
        <script>
            /* simulate the t() function */
            function t() {}
        </script>
    </head>
    <body>
        <div id="content_area" style="width:100%;height:100%;">
            <?php
                if(empty($this->grids))
                {
                    echo $this->ui->createMessage(sb()
                        ->bold(t('No data grid content found to display.'))
                    )
                        ->makeError()
                        ->makeNotDismissable()
                        ->render();
                }
                else
                {
                    foreach($this->grids as $grid)
                    {
                        ?>
                            <div class="datagrid-title"><?php echo $grid->getTitle() ?></div>
                            <table class="table table-bordered">
                                <?php echo $grid->getHTML() ?>
                            </table>
                        <?php
                    }
                }
            ?>
        </div>
    </body>
</html>
<?php
    }

    /**
     * @var Application_AjaxMethods_GetGridFullViewHTML_Grid[]
     */
    protected $grids;

    /**
     * @var string
     */
    protected $title;

    protected function preRender() : void
    {
        $this->ui->addBootstrap();
        $this->ui->addFontAwesome();
        $this->ui->addJquery();
        $this->ui->addJavascript('application.js');

        $this->ui->addStylesheet('driver.css');
        $this->ui->addStylesheet('ui-datagrid.css');
        $this->ui->addStylesheet('ui-datagrid-fullview.css');

        $this->grids = $this->getArrayVar('grids');

        if(!empty($this->grids)) {
            $title = $this->grids[0]->getTitle();
            if(!empty($title)) {
                $this->title = $title;
            }
        }

        if(empty($this->title)) {
            $this->title = t('List view');
        }
    }
}
