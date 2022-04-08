<?php
/**
 * File containing the class {@see \Application\WhatsNew\AppVersion\VersionLanguage\DEV}.
 *
 * @package Application
 * @subpackage WhatsNew
 * @see \Application\WhatsNew\AppVersion\VersionLanguage\DEV
 */

declare(strict_types=1);

namespace Application\WhatsNew\AppVersion\VersionLanguage;

use Application\WhatsNew\AppVersion\VersionLanguage;

/**
 * For developer entries.
 *
 * @package Application
 * @subpackage WhatsNew
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
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
