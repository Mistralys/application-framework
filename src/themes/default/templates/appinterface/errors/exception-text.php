<?php

try
{
    header('Content-Type:text/plain; charset=UTF-8');
    
    throw new Application_Exception(
        'TXT exception',
        'Dear developer, this does not work!',
        123456
    );
}
catch(Exception $e)
{
    displayError($e);
}