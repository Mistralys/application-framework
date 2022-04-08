<?php
/**
 * File containing the class {@see \Application\WhatsNew\AppVersion\VersionLanguage\DE}.
 *
 * @package Application
 * @subpackage WhatsNew
 * @see \Application\WhatsNew\AppVersion\VersionLanguage\DE
 */

declare(strict_types=1);

namespace Application\WhatsNew\AppVersion\VersionLanguage;

use Application\WhatsNew\AppVersion\VersionLanguage;

/**
 * For german language entries.
 *
 * @package Application
 * @subpackage WhatsNew
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
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
