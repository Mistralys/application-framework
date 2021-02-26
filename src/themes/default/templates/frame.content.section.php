<?php
/**
 * Template for the section blocks in the content area: can have an
 * optional title and can be configured further using options. 
 * 
 * @package Application
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see UI_Page_Section
 */

	/* @var $this UI_Page_Template */
    /* @var $quick UI_QuickSelector */
    /* @var $section UI_Page_Section */
	
	$title = $this->getVar('title');
	$abstract = $this->getVar('abstract');
	$tagline = $this->getVar('tagline');
	$section = $this->getVar('_section');
	$id = $this->getVar('id');
	$type = $this->getVar('type');
	$clientName = 'SC'.$id;
	$hasTabs = $section->hasTabs();
	
	$sectionAtts = array();
	$sectionAtts['id'] = $id;
    $sectionAtts['class'] = implode(' ', $this->getVar('classes'));
    
    $quick = $this->getVar('quick-selector');
    $contextButtons = $this->getVar('context-buttons');
    $collapsible = $this->getVar('collapsible', false);
    $collapsed = $this->getVar('collapsed', false);
    
    $toolbar = false;
    if(!empty($quick) || !empty($contextButtons)) {
        $toolbar = true;
    }
    
    $this->ui->addJavascript('ui/section.js');
    $this->ui->addJavascriptHeadStatement(
        sprintf('var %s = UI.RegisterSection', $clientName), 
        $id, 
        $type, 
        $collapsible, 
        $collapsed,
        $section->getGroup()
    );

    $anchor = $this->getStringVar('anchor');
    if(empty($anchor))
    {
        $anchor = \AppUtils\ConvertHelper::transliterate(strip_tags($title));
    }
    
	$html =
	'<section'.AppUtils\ConvertHelper::array2attributeString($sectionAtts).'>'.
        '<a id="'.$anchor.'" name="'.$anchor.'"></a>';
	
        // -----------------------------------------------------------------
        // TOOLBAR
        // -----------------------------------------------------------------
        if($toolbar) {
           $html .= 
            '<div class="btn-toolbar pull-right">';
                if($quick) {
                    if(empty($title)) {
                        $title = '&nbsp;';
                    }
                    
                    $html .=
        	       '<div class="btn-group">'.
        	           $quick->render().
                   '</div>';
                }
        
                if(!empty($contextButtons)) {
                    if(empty($title)) {
                        $title = '&nbsp;';
                    }
                    
                    foreach($contextButtons as $contextButton) {
                        $contextButton->makeSmall();
                    }
                     
                    $html .=
                    '<div class="btn-group section-context-buttons">'.
                        implode('', $contextButtons).
                    '</div>';
                }
            
                $html .=
            '</div>';
        }
        
	    // -----------------------------------------------------------------
	    // TITLE BAR
	    // -----------------------------------------------------------------
		if(!empty($title)) 
		{
		    $taglineHTML = '';
		    $headerAtts = array();
		    $headerClasses = array($type.'-header');
			if(!empty($tagline)) {
			    $headerClasses[] = 'with-tagline';
			    $taglineHTML =
			    '<small class="'.$type.'-tagline">'.$tagline.'</small>';
			}	
			
			if($collapsible) {
			    $headerClasses[] = 'collapsible';
			    $headerAtts['id'] = $id.'-header';
			    $headerAtts['data-toggle'] = 'collapse';
			    $headerAtts['data-target'] = '#' . $id . '-body';
			    
			     
			    if($collapsed) {
			        $headerClasses[] = 'collapsed';
			        $icon = UI::icon()->expand()
			        ->makeMuted()
			        ->setID($id.'-caret');
			    } else {
			        $icon = UI::icon()->collapse()
			        ->makeMuted()
			        ->setID($id.'-caret');
			    }
			    
			    $title = $title.' '.$icon->render();
			} else {
			    $headerClasses[] = 'regular';
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
		
		// -----------------------------------------------------------------
		// TABS NAVIGATION
		// -----------------------------------------------------------------
		if($hasTabs) 
		{
		    $tabs = $section->getTabs();
		    
		    $html .=
		    '<ul class="tabs-section">';
		        foreach($tabs as $tab) 
		        {
		            $classes = array(
		                'tab',
		                'tab-'.$tab->getName()
		            );
		            
		            if($tab->isActive()) {
		                $classes[] = 'tab-active';
		            } else {
		                $classes[] = 'tab-default';
		            }
		            
		            $html .=
		            '<li class="'.implode(' ', $classes).'">';
		                if($tab->isLink()) {
		                    $html .=
		                    '<a href="'.$tab->getURL().'">'.$tab->renderLabel().'</a>';
		                } else {
		                    $html .= $tab->renderLabel();
		                }
		                $html .=
		            '</li>';
		        }
		        $html .=
	        '</ul>';
		}
		
		// -----------------------------------------------------------------
		// SECTION BODY
		// -----------------------------------------------------------------
		$bodyAtts = array();
		$bodyClasses = array();
		
		$bodyAtts['id'] = $id . '-body';
		$bodyClasses[] = $type.'-body';
		
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