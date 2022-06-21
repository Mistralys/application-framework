<?php
/**
 * File containing the template class {@see template_default_frame_page_help}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_frame_page_help
 */

declare(strict_types=1);

use Application\ClassFinder;

/**
 * Handles the "Explain this screen" inline page help interface rendering.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Page_Help::_render()
 */
class template_default_frame_page_help extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        $this->ui->addStylesheet('ui-page-help.css');

        if(!$this->help->hasSummary() && !$this->help->hasItems()) {
            return;
        }

        $this->ui->addJavascript('ui/page-help.js');
        $this->ui->addJavascriptHead('var PageHelp = new UI_PageHelp()');
        $this->ui->addJavascriptOnload('PageHelp.Start()');

        ?>
<div id="page-help">
    <div class="help-opener clickable">
        <?php echo UI::label(UI::icon()->help().' '.t('Explain this screen'))->makeInfo() ?>
    </div>
	<div class="help-contents">
		<div class="help-contents-wrapper">
        	<?php 
                if($this->help->hasSummary())
                {
                    ?>
                        <div class="help-summary">
                            <?php echo $this->help->getSummary() ?>
                        </div>
                    <?php 
                }
                
                if($this->help->hasItems())
                {
                    ?>
                    	<div class="help-body">
                            <?php 
                                $items = $this->help->getItems();
                                
                                foreach($items as $item) 
                                {
                                    $item->display();
                                }
                            ?>
                            <p>
                            	<?php
                            	   UI::button(t('Close'))
                            	   ->setIcon(UI::icon()->delete())
                            	   ->click("PageHelp.Close()")
                            	   ->display();
                            	?>
                            </p>
                        </div>
                    <?php 
                }
            ?>
        </div>
    </div>
</div>        
<?php
    }

    private UI_Page_Help $help;

    protected function preRender() : void
    {
        $this->help = $this->getObjectVar('help', UI_Page_Help::class);
    }
}
