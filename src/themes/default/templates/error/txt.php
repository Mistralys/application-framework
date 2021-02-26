<?php

    /* @var $error Application_ErrorDetails */

if(!isset($error))
{
    return;
}

$error = ensureType(Application_ErrorDetails::class, $error);

?>

==============================================
<?php echo strtoupper($error->getTitle()) ?>
==============================================

<?php echo $error->renderException() ?>


<?php echo $error->renderTrace() ?>

Content sent before the error:

<?php $error->getSentContent() ?>
