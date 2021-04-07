<?php
/**
 * File containing the template class {@see template_default_frame_content_section}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_frame_content_section
 */

declare(strict_types=1);

/**
 * Template for the section blocks in the sidebar area: can have an
 * optional title and can be configured further using options.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_Page_Section
 */
class template_default_frame_sidebar_section extends template_default_frame_content_section
{
    protected function configureButton($button): void
    {
        $button->makeMini();
    }
}
