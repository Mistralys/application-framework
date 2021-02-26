<p><br></p>
<?php

    /* @var $this UI_Page_Template */

    $this->ui->createMessage(
        '<b>'.t('You are not authorized to view this page.').'</b>'
    )
    ->makeNotDismissable()
    ->makeLargeLayout()
    ->enableIcon()
    ->display();
    
    $roles = $this->user->getRequestedRoles();
    $missing = array();
    foreach($roles as $role) {
        if(!$this->user->can($role)) {
            $missing[] = $role;
        }
    }
    
?>
<p>
	<?php pt('This usually happens when your user account is missing rights for a specific task or %1$s page.', $this->driver->getAppNameShort()) ?>
</p>
<p>
	<?php pt('The following rights were requested in this page, which your user account lacks:') ?>
</p>
<ul>
	<li><?php echo implode('</li><li>', $missing) ?></li>
</ul>
<p>
	<?php pts('It cannot be determined which of these exactly is the culprit.'); 
	pts('Some rights like "%1$s" for example, are always queried, but are not required.', '<span class="monospace">Developer</span>');
	pts('However, this may give you an indication which rights you need.') ?>
</p>
<p>
	<?php 
	    pts('Note:');
	    pt(
	       'You can review your account\'s rights in %1$syour profile page%2$s.',
	        '<a href="?page=settings">',
	        '</a>'
	    ); 
    ?>
</p>
