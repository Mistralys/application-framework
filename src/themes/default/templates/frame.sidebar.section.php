<?php
/**
 * Template for the section blocks in the sidebar area: can have an
 * optional title and can be configured further using options. 
 * 
 * @package Application
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Page_Section
 */

	/* @var $this UI_Page_Template */
    /* @var $section UI_Page_Section */
	
	$title = $this->getVar('title');
	$abstract = $this->getVar('abstract');
	$tagline = $this->getVar('tagline');
	$id = $this->getVar('id');
	$collapsible = $this->getVar('collapsible');
	$collapsed = $this->getVar('collapsed');
	$section = $this->getVar('_section');
	
	$sectionAtts = array();
	$sectionAtts['id'] = $id;
    $sectionAtts['class'] = implode(' ', $this->getVar('classes'));
	
	$html =
	'<section'.AppUtils\ConvertHelper::array2attributeString($sectionAtts).'>';
	    $contextButtons = $this->getVar('context-buttons');
	    if(!empty($contextButtons)) {
	        // we need the title for the context buttons, even if it's just a space.
	        if(empty($title)) {
	            $title = '&nbsp;';
	        }
	        
	        $html .=
	        '<div class="btn-group pull-right sidebar-context-buttons">'.
	           implode('', $contextButtons).
            '</div>';
	    }
	
		if(!empty($title)) 
		{
		    $taglineHTML = '';
		    $headerAtts = array();
		    $headerClasses = array('section-header');
			if(!empty($tagline)) {
			    $headerClasses[] = 'with-tagline';
			    $taglineHTML =
			    '<small class="section-tagline">'.$tagline.'</small>';
			}	
			
			if(!empty($abstract)) {
			    $headerClasses[] = 'with-abstract';
			}
			
			if($collapsible) {
			    $headerClasses[] = 'collapsible';
			    $headerAtts['data-toggle'] = 'collapse';
			    $headerAtts['data-target'] = '#' . $id . '-body';
			    $title = 
			    '<span class="section-caret pull-right">'.
			         UI::icon()->caretDown().
			    '</span>'.
			    $title;
			    
			    if($collapsed) {
			        $headerClasses[] = 'collapsed';
			    }
			}
			
			$headerAtts['class'] = implode(' ', $headerClasses);
			
			if($section->hasIcon()) {
			    $title = $section->getIcon().' '.$title;
			}
			
		    $html .=
			'<h3' . compileAttributes($headerAtts) . '>'.
		        $title.
		        $taglineHTML.
		    '</h3>';
		}
		
		$bodyAtts = array();
		$bodyClasses = array();
		
		$bodyAtts['id'] = $id . '-body';
		$bodyClasses[] = 'section-body';
		
		if($collapsible) {
		    $bodyClasses[] = 'collapse';
		    if(!$collapsed) {
		        $bodyClasses[] = 'in';
		    }
		}
		
		$bodyAtts['class'] = implode(' ', $bodyClasses);
		
		$wrapperAtts = array();
		$wrapperAtts['class'] = 'body-wrapper';
		
		$maxBodyHeight = $section->getMaxBodyHeight();
		if($maxBodyHeight && $maxBodyHeight > 0) {
		    $wrapperAtts['style'] = 'max-height:'.$maxBodyHeight.'px;overflow:auto';
		}
		
		$html .=
		'<div' . compileAttributes($bodyAtts) . '>'.
		    '<div'.compileAttributes($wrapperAtts).'>';
        		if(!empty($abstract)) {
        		    $html .= 
        		    '<p class="abstract">'.$abstract.'</p>';
        		}
        		$html .=
        		$this->getVar('content').
    		'</div>'.
		'</div>'.
	'</section>';
		
	echo $html;