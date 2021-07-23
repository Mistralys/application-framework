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

    /* @var $this UI_Page_Template */
    /* @var $selection UI_Bootstrap_BigSelection */

    $selection = $this->getVar('selection');
    
    $this->ui->addStylesheet('ui-bigselection.css');

    $selection->addClass('bigselection');

    $items = $selection->getItems();
    $jsID = $selection->getID();
    
    $wrapperClasses = array('bigselection-wrapper');
    
    if($selection->isHeightLimited()) 
    { 
        $wrapperClasses[] = 'bigselection-height-limited'; 
    }
    
    if($selection->isFilteringInUse())
    {
        $wrapperClasses[] = 'bigselection-filtering-enabled';
    }
        
?>
<div class="<?php echo implode(' ', $wrapperClasses) ?>" id="<?php echo $jsID ?>-wrapper">
    <?php 
        if($selection->isFilteringInUse())
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
    <ul<?php echo $selection->renderAttributes() ?>>
    	<?php
            foreach($items as $item)
            {
                $item->display();
            }
        ?>
    </ul>
</div>
