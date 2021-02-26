<?php

	/* @var $lastUpload DateTime */
    /* @var $this UI_Page_Template */

	$lastUpload = $this->getVar('last-uploaded');

	if($lastUpload) {
		$duration = AppUtils\ConvertHelper::duration2string(AppUtils\ConvertHelper::date2timestamp($lastUpload));
	} else {
		$duration = '<span class="muted">'.t('Never').'</span>';
	}

	echo
	UI::icon()->upload().' '.
	t('Last uploaded:').' '.$duration.
	'<br/>';
