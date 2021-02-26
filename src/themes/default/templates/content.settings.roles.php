<?php

	/* @var $this UI_Page_Template */
	/* @var $user Application_User */

	$user = $this->getVar('user');
	$groups = $user->getRoleGroups();
	$roles = $user->getGrantableRoles();
	foreach($groups as $group) {
		echo
		'<h3>'.$group.'</h3>'.
		'<ul class="unstyled">';
			foreach($roles as $roleName => $def) {
				if($def['group'] != $group) {
					continue;
				}

				echo
				'<li>';
					if($user->can($roleName)) {
						echo UI::icon()->ok()->makeSuccess().' ';
					} else {
						echo UI::icon()->notAvailable()->makeDangerous().' ';
					}
					echo
					'<acronym title="'.$def['descr'].'" rel="tooltip">'.$def['label'].'</acronym>'.
					' &nbsp; '.
					'<span class="monospace muted">'.$roleName.'</span>'.
				'</li>';
			}
			echo
		'</ul>';
	}
