<?php

use AppUtils\BaseException;

/**
 * DBHelper-specific exception.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_Exception extends BaseException
{
    public function __construct(string $message, $details = null, $code = null, $previous = null)
    {
        if(Application::isUnitTestingRunning())
        {
            $message .= PHP_EOL.$details;

            if($previous)
            {
                $message .= PHP_EOL.
                    'Previous exception: [#'.$previous->getCode().'] '.$previous->getMessage();
            }
        }

        parent::__construct($message, $details, $code, $previous);
    }
}
