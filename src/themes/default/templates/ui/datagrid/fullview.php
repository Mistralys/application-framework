<?php

    /* @var $this UI_Page_Template */

    $this->ui->addBootstrap();
    $this->ui->addFontAwesome();
    $this->ui->addJquery();
    
    $this->ui->addStylesheet('driver.css');
    $this->ui->addStylesheet('ui-datagrid.css');
    $this->ui->addStylesheet('ui-datagrid-fullview.css');

?><!DOCTYPE html>
<html lang="en">
    <head>
    	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    	<title><?php pt('List view') ?></title>
    	<?php echo $this->ui->renderHeadIncludes() ?>
    </head>
    <body>
        <div id="content_area" style="width:100%;height:100%;">
            {CONTENT}
        </div>
    </body>
</html>
