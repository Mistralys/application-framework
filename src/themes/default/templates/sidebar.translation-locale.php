<?php

	/* @var $this UI_Page_Template */

	$appLocale = $this->getVar('applocale');

?>
<section>
	<p>
		<input type="hidden" name="applocale" value="<?php echo $appLocale->getName() ?>"/>
		<img src="<?php echo imageURL('flag-'.$appLocale->getName().'.png') ?>" alt="" class="flag-icon"/>
		<?php pt('You are editing content in %1s. Change the application locale:',$appLocale->getLabel()) ?>
	</p>
	<ul id="locale_selector" class="unstyled">
		<?php
			$locales = \AppLocalize\Localization::getAppLocales();
			foreach($locales as $locale)
			{
				if($locale->getName()==$appLocale->getName()) {
					continue;
				}

				$params = array(
					'applocale'=>$locale->getName()
				);

				$url = $this->page->getURL($params);

				?>
					<li>
						<img src="<?php echo imageURL('flag-'.$locale->getName().'.png') ?>" alt="" class="flag-icon"/>
						<a href="<?php echo $url ?>"><?php echo $locale->getLabel() ?></a>
					</li>
				<?php
			}
		?>
	</ul>
</section>
