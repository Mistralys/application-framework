<?php

declare(strict_types=1);

namespace Application\WhatsNew\AppVersion\VersionLanguage;

use Application\WhatsNew\AppVersion\VersionLanguage;

class DEV extends VersionLanguage
{
    public function isDeveloperOnly() : bool
    {
        return true;
    }

    public function getMiscLabel() : string
    {
        return 'Developer';
    }
}
