<?php 

/* @var $error Application_ErrorDetails */

use Application\AppFactory;
use AppUtils\ClassHelper;

if(!isset($error))
{
    return;
}

$error = ClassHelper::requireObjectInstanceOf(Application_ErrorDetails::class, $error);

$themeURL = $error->getThemeURL();
    
?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<title><?php echo $error->getTitle() ?></title>
		<?php
		    $favicon = $error->findFile('img/logo.png'); 
		    if($favicon) { 
		        ?><link rel="shortcut icon" href="<?php echo $favicon->getURL() ?>"/><?php 
		    } 
	    ?>
		<link rel="stylesheet" type="text/css" href="<?php echo $themeURL ?>/css/bootstrap.min.css" media="all"/>
		<link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" media="all"/>
        <link rel="stylesheet" type="text/css" href="<?php echo $themeURL ?>/css/ui-fonts.css" media="screen"/>
		<link rel="stylesheet" type="text/css" href="<?php echo $themeURL ?>/css/ui-errorpage.css" media="screen"/>
	</head>
	<body class="errorpage">
		<div id="content_frame">
			<h1>
				<?php 
				   $logo = $error->findFile('img/logo-big.png');
				   if($logo) {
				       echo '<img src="'.$logo->getURL().'" class="logo">';
				   }
				
				   echo $error->getTitle(); 
			   ?>
			</h1>
			<p class="abstract">
				<?php echo $error->getAbstract(); ?>
			</p>
			<p>
				<?php echo $error->renderException() ?>
			</p>
			<p>
				When contacting the technicians, make sure to provide these details: it will
				make finding the issue substantially faster.
			</p>
			<?php
				if($error->isDeveloperInfoEnabled())
				{
                    $sentContent = $error->getSentContent();
                    if(empty($sentContent)) {
                        $sentContent = '(no content sent)';
                    }

                    ?>
                        <h4 class="errorpage-header">Stack trace</h4>
                        <?php echo $error->renderTrace(); ?>

                        <h4 class="errorpage-header">Standard output</h4>
                        <p>The following content has been sent to standard output up to this point:</p>
                        <pre class="errorpage-sent-content"><?php echo htmlspecialchars($sentContent) ?></pre>
                    <?php

                    if($error->hasPreviousException())
                    {
                        ?>
                            <h4 class="errorpage-header">Previous exception</h4>
                            <p>This exception was thrown originally.</p>
                            <?php echo $error->renderPreviousException(); ?>
                        <?php
                    }

				    $logger = AppFactory::createLogger();
				    
				    if($logger->isLoggingEnabled() && $logger->getLogMode() !== Application_Logger::LOG_MODE_ECHO)
				    {
        				?>
        					<h4 class="errorpage-header">Application log</h4>
        					<p>The following are all log entries that have been added so far.</p>
        					<div class="errorpage-sent-content">
        						<?php 
        						    echo implode('<br>', $logger->getLog());
        						?>
        					</div>
    					<?php 
				    }
				}
			?>
			<hr>
			<p>
				<small>Note: For technical reasons, this page can only be displayed in english.</small>
			</p>
		</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script src="<?php echo $themeURL ?>/js/bootstrap.min.js"></script>
	</body>
</html>