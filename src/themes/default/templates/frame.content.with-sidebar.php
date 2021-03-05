<?php
/**
 * File containing the template {@see template_default_frame_content_with_sidebar}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_frame_content_with_sidebar
 */

declare(strict_types=1);

/**
 * Renders the content of a page, with the sidebar enabled,
 * from the breadcrumb to the actual page content.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class template_default_frame_content_with_sidebar extends UI_Page_Template_Custom
{
    protected function generateOutput(): void
    {
        $this->displayTemplate('frame.content.upper-scaffold');

        $this->sidebar->setTagName('td');

        JSHelper::tooltipify('sidebar-toggle-grip');

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
                           <td id="sidebar-toggle" onclick="Sidebar.Toggle()" class="clickable <?php echo $this->sidebarState ?>">
                               <span id="sidebar-toggle-grip" title="<?php pt('Toggle the visibility of the sidebar') ?>">
                                   <?php
                                        UI::icon()->collapseRight()
                                            ->setID('sidebar-toggle-icon-collapse')
                                            ->setHidden($this->sidebar->isCollapsed())
                                            ->display();

                                        UI::icon()->expandLeft()
                                            ->setID('sidebar-toggle-icon-expand')
                                            ->setHidden(!$this->sidebar->isCollapsed())
                                            ->display();
                                    ?>
                               </span>
                           </td>
                           <?php echo '{SIDEBAR}'; ?>
                       </tr>
                   </tbody>
                </table>
            </div>
        <?php
    }

    protected $sidebarState = 'expanded';

    protected function preRender(): void
    {
        if($this->sidebar->isCollapsed()) {
            $this->sidebarState = 'collapsed';
        }
    }
}
