<?php
/**
 * Template for the Bootstrap BigSelection UI element.
 * 
 * @package Application
 * @subpackage UserInterface
 * @template ui-bootstrap-big-selection
 * 
 * @see BigSelectionWidget
 */

declare(strict_types=1);

use AppUtils\AttributeCollection;
use UI\Bootstrap\BigSelection\BigSelectionCSS;
use UI\Bootstrap\BigSelection\BigSelectionWidget;

class template_default_ui_bootstrap_big_selection extends UI_Page_Template_Custom
{
    protected function generateOutput(): void
    {
        $this->ui->addStylesheet(BigSelectionCSS::RESOURCES_STYLE_SHEET);

        $this->selection->addClass(BigSelectionCSS::WIDGET);

        $items = $this->selection->getItems();
        $jsID = $this->selection->getID();

        $this->wrapperAttributes->addClass(BigSelectionCSS::WIDGET_WRAPPER);
        $this->wrapperAttributes->id($jsID.'-wrapper');

        $maxHeight = $this->selection->getMaxHeight();
        if($maxHeight !== null)
        {
            $this->wrapperAttributes->addClass(BigSelectionCSS::WIDGET_HEIGHT_LIMITED);
            $this->wrapperAttributes->style('max-height', $maxHeight->toCSS(), false);
        }

        if($this->selection->isFilteringInUse())
        {
            $this->wrapperAttributes->addClass(BigSelectionCSS::FILTERING_ENABLED);
        }

    ?>
<div<?php echo $this->wrapperAttributes->render() ?>>
    <?php 
        if($this->selection->isFilteringInUse())
        {
            $this->ui->addJavascript(BigSelectionCSS::RESOURCES_JS_HANDLER);
            
            $this->ui->addJavascriptOnload(sprintf("(new UI_BigSelection_Static('%s')).Start()", $jsID));
            
            ?>
            	<div class="<?php echo BigSelectionCSS::FILTERING_CONTAINER ?>">
            		<div class="input-append">
            			<input class="<?php echo BigSelectionCSS::SEARCH_INPUT ?> input-xxlarge" type="text" id="<?php echo $jsID ?>-search" value="" placeholder="<?php pt('Type your search here...') ?>">
            			<?php
            			    UI::button('')
                                ->setID($jsID.'-btn')
                                ->addClass(BigSelectionCSS::CLEAR_BUTTON)
                                ->setIcon(UI::icon()->delete()->makeMuted())
                                ->display();

                            $help = UI::button('')
                                ->setStyle('cursor', 'help')
                                ->setIcon(UI::icon()->help()->makeInformation());

                            $help->getPopover()
                                ->setTitle(t('Search howto'))
                                ->setPlacementTop()
                                ->setContent(sb()
                                    ->ul(array(
                                        t('Start typing to filter the list.'),
                                        t('Separate search terms with spaces.'),
                                        t('Searches in the label and description.'),
                                        t('Search is case insensitive.')
                                    ))
                                );

                            $help->display();
            			?>
        			</div>
            	</div>
            <?php 
        }
    ?>
    <ul<?php echo $this->selection->renderAttributes() ?>>
    	<?php
            foreach($items as $item)
            {
                $item->display();
            }
        ?>
    </ul>
</div>
<?php
    }

    protected BigSelectionWidget $selection;
    protected AttributeCollection $wrapperAttributes;

    protected function preRender(): void
    {
        $this->selection = $this->getObjectVar('selection', BigSelectionWidget::class);
        $this->wrapperAttributes = AttributeCollection::create();
    }
}
