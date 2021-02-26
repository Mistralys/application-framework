<?php

/* @var $this UI_Page_Template */

// in some cases, the UI framework (css and js) may not
// have been configured yet, for example in the health
// monitor which by default disabled the UI layer. So we
// configure it here as needed.
if(!$this->driver->isUIFrameworkConfigured()) 
{
    $this->driver->configureAdminUIFramework();
}

$this->ui->addStylesheet('ui-clean-frame.css');

?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title><?php echo $this->driver->getAppName() ?></title>
        <link rel="shortcut icon" href="favicon.ico"/>
        <?php echo $this->ui->renderHeadIncludes() ?>    
    </head>
    <body class="clean-frame">
		<div id="content_area">
			<div class="container">
        		<div id="content_frame">
                    <?php 
                        echo $this->getVar('content');
                    ?>
				</div>
			</div>        
        </div>
	</body>
</html>