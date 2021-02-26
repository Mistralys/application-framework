<?php

    /* @var $this UI_Page_Template */
    /* @var $help UI_Page_Help */

    $help = $this->getVar('help');
    
    $this->ui->addStylesheet('ui-page-help.css');
    $this->ui->addJavascript('ui/page-help.js');
    $this->ui->addJavascriptOnload('UI_PageHelp.Start()');
    
    if(!$help->hasSummary() && !$help->hasItems()) {
        return;
    }
    
?>
<div id="page-help">
    <div class="help-opener">
        <?php echo UI::label(UI::icon()->help().' '.t('Explain this screen'))->makeInfo()->cursorHelp() ?>
    </div>
	<div class="help-contents">
		<div class="help-contents-wrapper">
        	<?php 
                if($help->hasSummary()) 
                {
                    ?>
                        <div class="help-summary">
                            <?php echo $help->getSummary() ?>
                        </div>
                    <?php 
                }
                
                if($help->hasItems())
                {
                    ?>
                    	<div class="help-body" display>    
                            <?php 
                                $items = $help->getItems(); 
                                
                                foreach($items as $item) 
                                {
                                    $item->display();
                                }
                            ?>
                            <p>
                            	<?php
                            	   UI::button(t('Close'))
                            	   ->setIcon(UI::icon()->delete())
                            	   ->click("$('#page-help .help-contents').hide()")
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
