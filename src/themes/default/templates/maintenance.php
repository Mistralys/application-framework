<?php
/**
 * File containing the template class {@see template_default_maintenance}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_maintenance
 */

declare(strict_types=1);

/**
 * Template for the maintenance screen shown to users when the maintenance
 * mode is enabled.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class template_default_maintenance extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        $ui = UI::createInstance($this->driver->getApplication());
        $ui->addBootstrap();
        $ui->addFontAwesome();

?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title><?php echo $this->driver->getAppNameShort(); ?> - <?php pt('Maintenance') ?></title>
		<link rel="shortcut icon" href="favicon.ico"/>
        <?php
            echo $ui->renderHeadIncludes();
        ?>
	</head>
	<body>
		<br/>
		<br/>
		<div class="container">
			<div class="hero-unit">
				<img src="<?php echo $this->getImageURL('logo_big.png') ?>" class="pull-left" style="margin-right:30px;"/>
				<h1><?php pt('Maintenance') ?></h1>
				<p><i class="fa fa-info-circle text-info"></i> <?php pt('%1$s is currently in maintenance mode.', $this->driver->getAppNameShort())  ?></p>
			</div>
			<p>
				<?php pt('Downtime is scheduled to last another %1$s, and is liable to be extended if required.', AppUtils\ConvertHelper::interval2string($this->plan->getTimeLeft())); ?>
			</p>
			<?php 
			     if($this->plan->hasInfoText()) {
			         echo '<p>'.$this->plan->getInfoText().'</p>';
			     }
			?>
			<p>
				<?php pt('We thank you for your patience,') ?>
			</p>
			<p>
				<?php pt('The %1$s team.', $this->driver->getAppNameShort()) ?>
			</p>
		</div>
	</body>
</html><?php

    }

    /**
     * @var Application_Maintenance_Plan
     */
    private $plan;

    protected function preRender() : void
    {
        $this->plan = $this->getObjectVar('plan', Application_Maintenance_Plan::class);
    }
}

