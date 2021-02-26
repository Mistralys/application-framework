<?php

    /* @var $this UI_Page_Template */

?>
    <h1><?php pt('Development console') ?></h1>
	<pre class="console">
		<?php echo $this->page->getConsoleOutput() ?>
	</pre>