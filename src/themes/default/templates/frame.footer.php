<?php

	/* @var $this UI_Page_Template */

?><section>
	<h2><?php echo $this->driver->getAppNameShort() ?> V<?php echo $this->driver->getVersion() ?></h2>
	<ul class="unstyled">
		<li><a href="javascript:void(0);" onclick="application.dialogWhatsnew()"><?php pt('What\'s new') ?></a></li>
	</ul>
</section>
<section>
	<h2><?php echo mb_strtoupper(t('My account')) ?></h2>
	<ul class="unstyled">
		<li><a href="<?php echo $this->request->buildURL(array('page' => 'settings')) ?>"><?php pt('Settings') ?></a></li>
	</ul>
</section>
<?php

	if($this->user->isDeveloper()) 
	{
		?>
			<section>
				<h2><?php echo mb_strtoupper(t('Developer')) ?></h2>
				<ul class="unstyled">
					<li><a href="<?php echo APP_URL.'/changelog.php' ?>"><?php pt('Changelog') ?></a></li>
					<li><a href="<?php echo APP_URL.'/upgrade.php' ?>"><?php pt('Maintenance') ?></a></li>
					<li><a href="<?php echo APP_URL.'/xml/monitor/' ?>"><?php pt('Monitoring') ?></a></li>
					<li><a href="<?php echo APP_URL.'/cronjobs.php?output=yes' ?>"><?php pt('Run cronjob script') ?></a></li>
				</ul>
			</section>
		<?php
	}
