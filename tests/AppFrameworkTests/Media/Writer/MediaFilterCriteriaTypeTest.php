<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\Writer;

use Application\Application;
use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Writer\MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

/**
 * Covers {@see \Application\Media\Collection\MediaFilterCriteria::selectMediaType()}
 * and {@see \Application\Media\Collection\MediaFilterCriteria::selectMediaTypes()}.
 */
final class MediaFilterCriteriaTypeTest extends MediaTestCase
{
    private static string $pngBytes;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::$pngBytes = (string)file_get_contents(__DIR__.'/../../../files/Media/example-image.png');
    }

    private function createImageRecord(string $name) : int
    {
        $record = (new MediaDocumentWriter())->create(
            MediaFileContent::fromBinary($name.'.png', self::$pngBytes),
            $name,
            Application::getUser()
        );

        $this->testMedia[] = $record->getMediaDocument();

        return $record->getID();
    }

    private function createPDFRecord(string $name) : int
    {
        $record = (new MediaDocumentWriter())->create(
            MediaFileContent::fromBinary($name.'.pdf', '%PDF-1.4'."\n%synthetic content"),
            $name,
            Application::getUser()
        );

        $this->testMedia[] = $record->getMediaDocument();

        return $record->getID();
    }

    public function test_selectMediaTypeFiltersToSingleType() : void
    {
        $imageID = $this->createImageRecord('Image Doc');
        $pdfID = $this->createPDFRecord('PDF Doc');

        $ids = AppFactory::createMediaCollection()
            ->getFilterCriteria()
            ->selectMediaType('Image')
            ->getIDs();

        $this->assertContains($imageID, $ids);
        $this->assertNotContains($pdfID, $ids);
    }

    public function test_selectMediaTypesFiltersToMultipleTypes() : void
    {
        $imageID = $this->createImageRecord('Image Doc');
        $pdfID = $this->createPDFRecord('PDF Doc');

        $ids = AppFactory::createMediaCollection()
            ->getFilterCriteria()
            ->selectMediaTypes(array('Image', 'PDF'))
            ->getIDs();

        $this->assertContains($imageID, $ids);
        $this->assertContains($pdfID, $ids);
    }

    public function test_noTypeFilterReturnsAllTypes() : void
    {
        $imageID = $this->createImageRecord('Image Doc');
        $pdfID = $this->createPDFRecord('PDF Doc');

        $ids = AppFactory::createMediaCollection()
            ->getFilterCriteria()
            ->getIDs();

        $this->assertContains($imageID, $ids);
        $this->assertContains($pdfID, $ids);
    }
}
