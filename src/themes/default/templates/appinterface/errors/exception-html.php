<?php

try
{
    throw new Application_Exception(
        'HTML exception',
        'Dear developer, this does not work!',
        123456
    );
}
catch(Exception $e)
{
    displayError($e);
}