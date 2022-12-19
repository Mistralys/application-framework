<?php
/**
 * @package Application
 * @subpackage WhatsNew
 * @see \Application\WhatsNew\WhatsNewImage
 */

declare(strict_types=1);

namespace Application\WhatsNew;

use Application_Driver;
use AppUtils\FileHelper\FileInfo;
use AppUtils\ImageHelper;
use AppUtils\ImageHelper_Size;

/**
 * Information class for an image placed in the `themes/default/img/whatsnew` folder,
 * and which can be displayed in the What's new dialog.
 *
 * @package Application
 * @subpackage WhatsNew
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class WhatsNewImage
{
    private FileInfo $file;
    private string $id;
    public function __construct(string $path)
    {
        $this->file = FileInfo::factory($path);
        $this->id = nextJSID();
    }

    public function getID() : string
    {
        return $this->id;
    }

    public function getURL() : string
    {
        return sprintf(
            '%s/whatsnew/%s',
            Application_Driver::getInstance()->getTheme()->getDriverImagesURL(),
            $this->getName()
        );
    }

    public function getName() : string
    {
        return $this->file->getName();
    }

    public function getWidth() : int
    {
        return $this->getSize()->getWidth();
    }

    public function getHeight() : int
    {
        return $this->getSize()->getHeight();
    }

    public function getSize() : ImageHelper_Size
    {
        return ImageHelper::getImageSize($this->file->getPath());
    }
}
