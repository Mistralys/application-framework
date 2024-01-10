<?php
/**
 * Template for collapsible help blocks in the sidebar, which can contain
 * short blocks of text with helpful information regarding the current page.
 * 
 * @package Application
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Page_Sidebar::addHelp()
 */

	/* @var $this UI_Page_Template */

    $jsID = nextJSID();
    
    $collapseClass = '';
    if($this->getVar('collapsed')===false) {
    	$collapseClass = 'in';
    }
    
    echo
    '<div class="accordion sidebar-help" id="'.$jsID.'">'.
       '<div class="accordion-group">'.
           '<div class="accordion-heading">'.
    	       '<a class="accordion-toggle" data-toggle="collapse" data-parent="'.$jsID.'" href="#'.$jsID.'A">'.
    	           '<span style="float:right;">'.UI::icon()->help().'</span>'.
    	           $this->getVar('title').' '.
    	           '<i class="fa fa-caret-down"></i>'.
    	       '</a>'.
    	   '</div>'.
    	   '<div id="'.$jsID.'A" class="accordion-body collapse '.$collapseClass.'">'.
    	       '<div class="accordion-inner">'.
        	       $this->getVar('content').
    	       '</div>'.
    	   '</div>'.
       '</div>'.
    '</div>';

    // add events to adjust the layout, so the sidebar does not overflow over the 
    // end of the page after opening the help block.
    $this->ui->addJavascriptOnload("$('#".$jsID."').on('shown', function() {application.adjustLayout();})");    
    $this->ui->addJavascriptOnload("$('#".$jsID."').on('hidden', function() {application.adjustLayout();})");
    