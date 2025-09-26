<?php

declare(strict_types=1);

namespace Application\Validation;

use Application_Interfaces_Loggable;

interface ValidationLoggableInterface extends ValidationResultInterface, Application_Interfaces_Loggable
{

}
