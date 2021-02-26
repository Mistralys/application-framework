<?php
    
    /* @var $this UI_Page_Template */

    if (!$this->driver->isMaintenanceMode()) {
        return;
    }
    
    $plan = $this->driver->getMaintenance()->getActivePlan();
    $url = $this->request->buildURL(array('page' => 'devel', 'mode' => 'maintenance'));
    
    ?>
    	<div id="maintenance_mode_hint">
        	<?php echo mb_strtoupper(t('Maintenance mode')) ?> 
        	- 
        	<?php pt('Expires in %1$s', AppUtils\ConvertHelper::interval2string($plan->getTimeLeft())) ?>
        	
        	<a href="<?php echo $url ?>" class="btn btn-mini" style="margin-left:15px;"><?php pt('Manage...') ?></a>
        </div>
