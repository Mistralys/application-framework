<?php
/**
 * Renders the content of a page, without sidebar,
 * from the breadcrumb to the actual page content.
 *
 * @package Application
 * @subpackage Themes
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see frame.content.upper-scaffold Renders the upper part of the page.
 */

	/* @var $this UI_Page_Template */

    echo $this->renderTemplate('frame.content.upper-scaffold');
	
?>
<div class="content-wrap without-sidebar">
	<div id="content">
	    <div class="body-wrap slim">
    		<?php
    		    echo $this->renderer->getContent();
    		?>
		</div>
	</div>
</div>
