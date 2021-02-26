<?php
/**
 * Template for the application rating widget.
 * 
 * @package Application
 * @subpackage Core
 * @see Application_Ratings::renderWidget()
 */

    /* @var $this UI_Page_Template */
    /* @var $ratings Application_Ratings */

    $ratings = $this->getVar('ratings');
    $max = $ratings->getMaxRating();

    $helpIcon = UI::icon()->help()
    ->setTooltip(t('Give us feedback on the current page.').' '.t('Also allows adding comments if you think something can be improved.'))
    ->cursorHelp()
    ->addClass('rating-help');
    
?>
<div id="rating-widget" class="noprint">
	<div class="rating-container">
		<div class="rating-preview">
			<i class="fa fa-angle-double-up"></i> 
			<span><?php pt('Rate this page') ?></span>
			<i class="fa fa-angle-double-up"></i>
		</div>
		<div class="rating-btn">
			<?php echo $helpIcon; ?>
			<?php pt('Rate this page:') ?> 
			<span class="rating-stars">
				<?php 
				    for($i=1; $i <= $max; $i++) 
				    {
				        $jsID = nextJSID();
				        
				        echo '<i class="fa fa-star rating-star star-'.$i.'" data-number="'.$i.'" title="'.$ratings->getRatingLabel($i).'" id="'.$jsID.'"></i> ';
				        
				        JSHelper::tooltipify($jsID);
				    }
				?>
			</span>
		</div>
	</div>
</div>
