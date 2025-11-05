<?php

declare(strict_types=1);

namespace Application\Themes\Default\Templates\Updaters;

use Application\Updaters\UpdaterInterface;
use Application\Updaters\UpdatersCollection;
use UI_Page_Template;
use UI_Page_Template_Custom;

class UpdatersSelectionTmpl extends UI_Page_Template_Custom
{
    public const string KEY_UPDATERS_INSTANCE = 'instance';
    private UpdatersCollection $updaters;
    private string $currentVersion;

    protected function preRender(): void
    {
        $this->updaters = $this->getObjectVar(self::KEY_UPDATERS_INSTANCE, UpdatersCollection::class);
        $this->currentVersion = $this->driver->getVersion();
    }

    protected function generateOutput() : void
    {
?>
<div class="hero-unit">
	<img src="<?php echo $this->theme->getImageURL('logo_big.png') ?>" class="pull-left" style="margin-right:30px;max-width: 100px;" alt=""/>
	<h1><?php pt('Maintenance scripts') ?></h1>
	<p>
		<?php pt('Regular maintenance and update scripts.') ?><br/>
	</p>
</div>
<div>
	<h2><?php pt('Select a maintenance operation:')?></h2>
	<?php 
		$entries = $this->updaters->getForVersion($this->currentVersion);
		if(!empty($entries)) {
		    $this->renderUpdatersList(
		        $this,
				t('Specific for %1$s version %2$s', $this->driver->getAppNameShort(), $this->currentVersion),
		        $entries
			);
		}
		
		$entries = $this->updaters->getAll();

		$this->renderUpdatersList(
		    $this,
			t('All operations'),
		    $entries
		);
	?>
</div>

<?php 
    }

    /**
     *
     * @param string $title
     * @param UpdaterInterface[] $updaters
     */
    private function renderUpdatersList(UI_Page_Template $template, $title, $updaters)
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
                        '<b><a href="'.$updater->buildURL().'">'.
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
}