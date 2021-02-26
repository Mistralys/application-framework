<?php

    /* @var $this UI_Page_Template */

    $messages = $this->getVar('messages');
    
    ?>
    	<div id="messages-container">
    		<?php 
                foreach ($messages as $message) 
                {
                    echo $this->renderMessage($message['text'], $message['type']);
                }
            ?>
        </div>
    
