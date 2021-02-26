<?php
/**
 * Renders the content of a page, with the sidebar enabled, 
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
	
	$this->sidebar->setTagName('td');
	
?>
<div class="content-wrap with-sidebar <?php if($this->sidebar->isLarge()) {echo 'sidebar-large';} ?>">
    <table>
        <tbody>
            <tr>
               <td id="content">
                  <div class="body-wrap slim">
    		          <?php
    			          echo $this->renderer->getContent(); 
    		          ?>
		          </div>
		       </td>
		       <?php echo '{SIDEBAR}'; ?>
	       </tr>
	   </tbody>
	</table>
</div>
