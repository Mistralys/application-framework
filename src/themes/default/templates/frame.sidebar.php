<?php
/**
 * File containing the template class {@see template_default_frame_sidebar}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_frame_sidebar
 */

declare(strict_types=1);

/**
 * Template for the sidebar in the page.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Page_Sidebar
 */
class template_default_frame_sidebar extends UI_Page_Template_Custom
{
    protected function generateOutput(): void
    {
        ?>
            <<?php echo $this->subject->getTagName() ?>
                    class="<?php echo implode(' ', $this->subject->getClasses()) ?>"
                    id="sidebar"
                    style="<?php if($this->subject->isCollapsed()) { echo 'display:none'; } ?>">
                <div class="sidebar-wrap">
                    <?php
                        $items = $this->subject->getItems();

                        foreach ($items as $item) {
                            $item->display();
                        }
                    ?>
                </div>
            </<?php echo $this->subject->getTagName() ?>>
        <?php
    }

    /**
     * @var UI_Page_Sidebar
     */
    private $subject;

    protected function preRender(): void
    {
        $this->subject = $this->getObjectVar('sidebar', UI_Page_Sidebar::class);
    }
}
