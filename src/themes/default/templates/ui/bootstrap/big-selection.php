<?php
/**
 * Template for the Bootstrap BigSelection UI element.
 * 
 * @package Application
 * @subpackage UserInterface
 * @template ui-bootstrap-big-selection
 * 
 * @see UI_Bootstrap_BigSelection
 */

declare(strict_types=1);

use AppUtils\AttributeCollection;

class template_default_ui_bootstrap_big_selection extends UI_Page_Template_Custom
{
    protected function generateOutput(): void
    {
        $this->ui->addStylesheet('ui-bigselection.css');

        $this->selection->addClass('bigselection');

        $items = $this->selection->getItems();
        $jsID = $this->selection->getID();

        $this->wrapperAttributes->addClass('bigselection-wrapper');
        $this->wrapperAttributes->id($jsID.'-wrapper');

        $maxHeight = $this->selection->getMaxHeight();
        if($maxHeight !== null)
        {
            $this->wrapperAttributes->addClass('bigselection-height-limited');
            $this->wrapperAttributes->style('max-height', $maxHeight->toCSS(), false);
        }

        if($this->selection->isFilteringInUse())
        {
            $this->wrapperAttributes->addClass('bigselection-filtering-enabled');
        }

    ?>
<div<?php echo $this->wrapperAttributes->render() ?>>
    <?php 
        if($this->selection->isFilteringInUse())
        {
            $this->ui->addJavascript('ui/bigselection/static.js');
            
            $this->ui->addJavascriptOnload(sprintf("(new UI_BigSelection_Static('%s')).Start()", $jsID));
            
            ?>
            	<div class="bigselection-filtering">
            		<div class="input-append">
            			<input class="bigselection-search-terms input-xxlarge" type="text" id="<?php echo $jsID ?>-search" value="" placeholder="<?php pt('Type your search here...') ?>">
            			<?php 
            			    UI::button('')
                                ->setID($jsID.'-btn')
                                ->addClass('bigselection-clear-btn')
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

    protected UI_Bootstrap_BigSelection $selection;
    protected AttributeCollection $wrapperAttributes;

    protected function preRender(): void
    {
        $this->selection = $this->getObjectVar('selection', UI_Bootstrap_BigSelection::class);
        $this->wrapperAttributes = AttributeCollection::create();
    }
}
