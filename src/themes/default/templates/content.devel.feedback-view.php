<?php

	/* @var $this UI_Page_Template */

	$data = $this->getVar('data');
	$requestParams = json_decode($data['request_params'], true);
	
	$users = Application_Driver::createUsers();
	$user = $users->getByID($data['user_id']);
	$date = new DateTime($data['date']);
	
?><section>
	<h3><?php pt('Reported by %1$s on %2$s', $user->getName(), $date->format('d.m.Y')); ?></h3>
	<p>
		Scope: <?php echo $data['feedback_scope'] ?><br/>
		Type: <?php echo $data['feedback_type'] ?><br/>
	</p>
	<p><?php pt('Feedback message:') ?></p>
	<p class="well">
		<?php echo nl2br($data['feedback']) ?>
	</p>
	<p><?php pt('Request parameters:') ?> (<a href="<?php echo $this->request->buildURL($requestParams) ?>"><?php pt('Go to page') ?></a>)</p>
	<p>
		<?php echo '<pre>'.print_r($requestParams, true).'</pre>'; ?>
	</p>
</section>