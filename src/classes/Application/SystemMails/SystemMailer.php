<?php

declare(strict_types=1);

namespace Application\SystemMails;

class SystemMailer
{
    public function createMail() : SystemMail
    {
        return new SystemMail();
    }
}
