<?php
/**
 * File containing the class {@see \Application\WhatsNew\XMLFileWriter}.
 *
 * @package Application
 * @subpackage WhatsNew
 * @see \Application\WhatsNew\XMLFileWriter
 */

declare(strict_types=1);

namespace Application\WhatsNew;

use Application\WhatsNew;
use AppUtils\FileHelper;

/**
 * Writes the what's new versions list to a target XML file.
 *
 * @package Application
 * @subpackage WhatsNew
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class XMLFileWriter
{
    private WhatsNew $whatsNew;

    public function __construct(WhatsNew $whatsNew)
    {
        $this->whatsNew = $whatsNew;
    }

    public function write(string $targetPath) : void
    {
        FileHelper::saveFile(
            $targetPath,
            $this->whatsNew->toXML()
        );
    }
}
