<?php

declare(strict_types=1);

namespace Application\WhatsNew\AppVersion\VersionLanguage;

use Application\WhatsNew\AppVersion\VersionLanguage;

class DE extends VersionLanguage
{
    public function isDeveloperOnly() : bool
    {
        return false;
    }

    public function getMiscLabel() : string
    {
        return 'Sonstiges';
    }
}
