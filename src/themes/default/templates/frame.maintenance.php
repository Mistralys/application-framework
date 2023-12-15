<?php

declare(strict_types=1);

/* @var $this UI_Page_Template */

use Application\AppFactory;
use AppUtils\ConvertHelper;
use AppUtils\OutputBuffering;

if (!Application_Driver::isMaintenanceMode()) {
    return;
}
    
$plan = AppFactory::createMaintenance()->getActivePlan();
$url = $this->request->buildURL(array('page' => 'devel', 'mode' => 'maintenance'));

OutputBuffering::start();

?>
    <?php echo mb_strtoupper(t('Maintenance mode')) ?>
    -
    <?php pt('Expires in %1$s', ConvertHelper::interval2string($plan->getTimeLeft())) ?>

    <a href="<?php echo $url ?>" class="btn btn-mini" style="margin-left:15px;"><?php pt('Manage...') ?></a>
<?php

echo UI::systemHint(OutputBuffering::get());
