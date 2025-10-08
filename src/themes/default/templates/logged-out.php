<?php
/**
 * File containing the template class {@see template_default_logged_out}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_logged_out
 */

declare(strict_types=1);

/**
 * Template for the logout screen shown to users when they have logged out.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Bootstrap_Screen_LoggedOut
 */
class template_default_logged_out extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        $this->ui->addStylesheet('ui-logged-out.css');

        ob_start();

        if($this->message)
        {
            $this->message->display();
        }
?>
<br/>
<br/>
<div class="hero-unit">
    <img src="<?php echo $this->getImageURL('logo_big.png') ?>" class="pull-left" style="margin-right:30px;max-width:90px;max-height:90px"/>
    <h1><?php echo $this->driver->getAppName() ?></h1>
    <p><span class="text-info"><?php
        echo UI::icon()->information()->makeInformation().' ';
        pt('You have been logged out.')
    ?></span></p>
    <?php echo implode(' ', $this->getAdditionalMessages()) ?>
</div>
<hr/>
<p>
    <?php
        UI::button(t('Log in again'))
        ->link(APP_URL)
        ->makePrimary()
        ->setIcon(UI::icon()->logIn())
        ->display();
    ?>

    <?php echo implode(' ', $this->getAdditionalButtons()) ?>
</p>
<?php

        echo $this->renderCleanFrame(ob_get_clean());
    }

    /**
     * @var UI_Message|null
     */
    protected $message = null;

    protected function preRender(): void
    {
        $message = $this->getVar('reason-message');

        if($message instanceof UI_Message)
        {
            $this->message = $message;
        }
    }

    /**
     * Method can be extended in an application template.
     *
     * @return UI_Button[]
     */
    protected function getAdditionalButtons() : array
    {
        return array();
    }

    /**
     * Method can be extended in an application template.
     *
     * NOTE: Return messages wrapped in paragraph tags, or
     * any other block level tags.
     *
     * @return array
     */
    protected function getAdditionalMessages() : array
    {
        return array();
    }
}
