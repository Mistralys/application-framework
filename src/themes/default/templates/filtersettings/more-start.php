<?php
/**
 * Snippet for the beginning of the more link 
 * in the filter settings widget to display the
 * hidden filter form fields.
 * 
 * @see Application_FilterSettings::addMore()
 */


    /* @var $this UI_Page_Template */

    /* @var $settings Application_FilterSettings */
    $settings = $this->getVar('settings');

?>
<div class="filter-settings-more">
    <div class="more-expand">
		<?php echo UI::icon()->caretDown() ?>
		<?php pt('Show more filters') ?>
		<?php echo UI::icon()->caretDown() ?>
    </div>
    <div class="more-elements">