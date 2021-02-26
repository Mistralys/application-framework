<?php

    /* @var $this UI_Page_Template */
    /* @var $updaters Application_Updaters */

    $updaters = $this->getVar('instance');
    $currentVersion = $this->driver->getVersion();

?>
<div class="hero-unit">
	<img src="<?php echo $this->theme->getImageURL('logo_big.png') ?>" class="pull-left" style="margin-right:30px;"/>
	<h1><?php pt('Maintenance scripts') ?></h1>
	<p>
		<?php pt('Regular maintenance and update scripts.') ?><br/>
	</p>
</div>
<div>
	<h2><?php pt('Select a maintenance operation:')?></h2>
	<?php 
		$entries = $updaters->getForVersion($currentVersion);
		if(!empty($entries)) {
		    updaters_selection_screen_renderUpdatersList(
		        $this,
				t('Specific for %1$s version %2$s', $this->driver->getAppNameShort(), $currentVersion), 
		        $entries
			);
		}
		
		$entries = $updaters->getAll(array($currentVersion));
		updaters_selection_screen_renderUpdatersList(
		    $this,
			t('All operations'),
		    $entries
		);
	?>
</div>

<?php 

   /**
    * 
    * @param string $title
    * @param Application_Updaters_Updater[] $updaters
    */
    function updaters_selection_screen_renderUpdatersList(UI_Page_Template $template, $title, $updaters)
	{
		echo 
		'<h3>'.$title.'</h3>';

		$list = '';
		foreach($updaters as $updater)
		{
			$versions = $updater->getValidVersions();
			if($versions != '*' && !is_array($versions)) {
			    $versions = array($versions);
			}
			
			$enabled = $updater->isEnabled();
			if($versions=='*') {
				$versions = t('Any');
			} else {
				$versions = implode(', ', $versions);
			}
			
			if($enabled) {
				$list .=
				'<li>'.
					'<b><a href="upgrade.php?updater_id='.$updater->getID().'">'.
					    $updater->getListLabel().
					'</a></b><br/>'.
					'<small class="muted">'.rtrim($updater->getDescription(), '.').'.<br/>'.
					t('For versions:').' '.$versions.'</small>'.
					'<br/><br/>'.
				'</li>';
			}
		}
		
		if(empty($list)) {
			echo
			'<div class="alert alert-info">'.
			    t('No operations are available for the current %1$s version.', $template->getAppNameShort()).
			'</div>';
			return;
		}
		
		echo
		'<ul class="unstyled">'.
			$list.
		'</ul>';
	}
