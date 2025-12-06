<?php

    /* @var $this UI_Page_Template */
    /* @var $buttonBar Application_Countries_ButtonBar */

    $buttonBar = $this->getVar('bar');

    $countries = $buttonBar->getCountries();
    
    $group = $this->ui->createButtonGroup();
    $group->addClasses($buttonBar->getClasses());
    
    foreach($countries as $country)
    {
        $btn = UI::button(strtoupper($country->getISO()))
        ->link($buttonBar->getCountryLink($country));
        
        if($buttonBar->isSelected($country))
        {
            $btn->makePrimary();
        }
        
        $group->addButton($btn);
    }
    
    if(!$buttonBar->isLabelEnabled())
    {
        echo $group->render();
        return;
    }
    
    $this->ui->addStylesheet('ui-countries-buttonbar.css');
    
    $hasSubtitle = $this->driver->getActiveScreen()?->getRenderer()->hasSubtitle() ?? false;
    
?>
<div class="countries-buttonbar <?php if($hasSubtitle) { echo 'with-subtitle'; } ?>">
	<div class="wrapper">
		<div class="bar-label"><?php echo $buttonBar->getLabel() ?></div>
		<?php echo $group->render() ?>
	</div>
</div>
<?php 
