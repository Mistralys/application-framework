<?php

declare(strict_types=1);

$message = UI::getInstance()
    ->createMessage()
    ->makeInfo()
    ->startCapture();
?>
<p>
    <strong>This is an information message.</strong>
</p>
<p>
    It can be as detailed as required.
</p>
<?php

$message->endCapture();

echo $message;
