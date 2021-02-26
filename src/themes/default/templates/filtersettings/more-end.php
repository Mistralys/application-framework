<?php
/**
 * Snippet for the end of the more link 
 * in the filter settings widget to display the
 * hidden filter form fields.
 * 
 * @see Application_FilterSettings::addMoreEnd()
 */


    /* @var $this UI_Page_Template */

    /* @var $settings Application_FilterSettings */
    $settings = $this->getVar('settings');

?>
    </div>
    <div class="more-collapse">
		<?php echo UI::icon()->caretUp() ?>
		<?php pt('Hide additional filters') ?>
		<?php echo UI::icon()->caretUp() ?>
    </div>
</div>