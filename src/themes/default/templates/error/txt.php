<?php

/* @var $error Application_ErrorDetails */

use AppUtils\ClassHelper;

if(!isset($error))
{
    return;
}

$error = ClassHelper::requireObjectInstanceOf(Application_ErrorDetails::class, $error);

?>

==============================================
<?php echo strtoupper($error->getTitle()).PHP_EOL ?>
==============================================

<?php echo $error->renderException() ?>


<?php echo $error->renderTrace() ?>

-----------------------------------------------
Content sent before the error
-----------------------------------------------

<?php
    $content = $error->getSentContent();

    if(!empty($content)) {
        echo $content;
    } else {
        echo '(empty string)';
    }

    $previous = $error->getExceptionStack();

    if(!empty($previous))
    {
        echo PHP_EOL.PHP_EOL;
        echo '-----------------------------------------------'.PHP_EOL;
        echo 'Previous exceptions'.PHP_EOL;
        echo '-----------------------------------------------'.PHP_EOL.PHP_EOL;

        foreach($previous as $ex) {
            echo renderExceptionInfo($ex).PHP_EOL;
            echo renderTrace($ex).PHP_EOL;
            echo '-----------------------------------------------'.PHP_EOL.PHP_EOL;
        }
    }
?>
