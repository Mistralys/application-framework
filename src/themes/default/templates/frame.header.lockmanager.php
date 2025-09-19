<?php

    /* @var $this UI_Page_Template */

    $lockManager = $this->page->getLockManager();
    $screen = $this->page->getActiveScreen();
    
    if(!$lockManager || !$screen instanceof Application_Interfaces_Admin_LockableScreen || !$screen->isLockable())
    {
        return;
    }
    
    $items = array();
    
    if ($screen->isLocked()) 
    {
        $items[] = '<li id="locking-time-to-unlock"></li>';
        $items[] = '<li>'.UI::icon()->locked() . ' ' . t('This page is being locked by %1$s.', $lockManager->getUser()->getName()).'</li>';
        $items[] = '<li id="locking-unlock-request-link"><a href="javascript:void(0)" onclick="LockManager.Visitor_DialogRequestUnlock()">'.UI::icon()->message().' '.t('Request unlock...').'</a></li>';
        $items[] = '<li class="pull-right" id="locking-unlock-requests"></li>';    
        $items[] = '<li class="pull-right" id="locking-active-locks"></li>';    
    } 
    else 
    {
        $items[] = '<li id="locking-time-to-unlock"></li>';
        $items[] = '<li>'.UI::icon()->locked(). ' <span id="locking-editing-time-elapsed"></span></li>';
        $items[] = '<li class="pull-right" id="locking-unlock-requests"></li>';    
        $items[] = '<li class="pull-right" id="locking-active-locks"></li>';    
    }
    
?>
<div class="locking-hint">
	<div class="container">
    	<ul>
    		<?php echo implode('', $items) ?>
    	</ul>
	</div>
</div>