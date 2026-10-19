<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\Delivery;

use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Delivery\MediaResponseHeaders;
use Application_Media_Document_Image;
use AppFrameworkTestClasses\ApplicationTestCase;
use AppUtils\FileHelper\FileInfo;
use DBHelper;
use PHPUnit\Framework\Attributes\Group;

/**
 * Live-HTTP regression test: serves a committed media document through the
 * real `tests/application/media.php` entry point, proving
 * {@see MediaResponseHeaders} is applied over real HTTP dispatch — an SVG
 * document gets both `nosniff` and the sandboxing CSP, a PNG document gets
 * `nosniff` only.
 *
 * Deliberately extends plain {@see ApplicationTestCase}, not
 * `AppFrameworkTestClasses\MediaTestCase` (whose
 * `ImageMediaTestTrait::setUpImageTestCase()` opens a transaction and wipes
 * the `media` table — committing a record inside that transaction would
 * commit the wipe too). This test creates and removes its own record and
 * file instead, since the record must be visible to the separate PHP
 * process serving `media.php`, which no base test class's lifecycle
 * supports.
 *
 * Requires a running webserver reachable at `APP_URL`, and the test
 * application's `TESTS_SESSION_TYPE` to be `'NoAuth'` (`media.php`'s only
 * supported test-environment authentication bypass) — excluded from the
 * default suite (see the `live-http` group exclusion in `phpunit.xml`).
 */
#[Group('live-http')]
final class MediaDeliveryHeadersLiveHTTPTest extends ApplicationTestCase
{
    /**
     * @var Application_Media_Document_Image[]
     */
    private array $createdMedia = array();

    protected function setUp(): void
    {
        parent::setUp();

        // Read through constant() rather than the bare constant: this
        // environment's test config always defines it as 'NoAuth', which
        // would make a direct literal comparison a statically-known-false
        // PHPStan error — but a different install's config may define it
        // as something else, which this guard exists to detect.
        $sessionType = defined('TESTS_SESSION_TYPE') ? constant('TESTS_SESSION_TYPE') : null;

        if($sessionType !== 'NoAuth') {
            $this->markTestSkipped("media.php's test-environment authentication requires TESTS_SESSION_TYPE === 'NoAuth'.");
        }
    }

    protected function tearDown(): void
    {
        foreach($this->createdMedia as $document) {
            $this->deleteCommittedMedia($document);
        }

        $this->createdMedia = array();

        parent::tearDown();
    }

    /**
     * Creates a media document from one of the example files under
     * `tests/files/Media/` and commits it, so it is visible to the separate
     * PHP process serving `media.php`. {@see Application_Media_Document::createNew()}
     * inserts directly via `DBHelper::insert()` with no transaction of its
     * own, so no explicit commit step is needed here — unlike deletion
     * (see {@see self::deleteCommittedMedia()}), which requires one.
     */
    private function createCommittedMedia(string $exampleFileName) : Application_Media_Document_Image
    {
        $file = __DIR__.'/../../../files/Media/'.$exampleFileName;

        $this->assertFileExists($file, sprintf('Example file [%s] must exist.', $exampleFileName));

        $document = AppFactory::createMedia()->createImageFromFile(
            'media-response-headers-test-'.$this->getTestCounter(),
            FileInfo::factory($file)
        );

        $this->assertFileExists($document->getPath(), "The document's file must have been written to disk.");

        $this->createdMedia[] = $document;

        return $document;
    }

    /**
     * {@see Application_Media_Document::delete()} requires an active
     * transaction, so one is opened and committed around the call
     * specifically for this cleanup step.
     */
    private function deleteCommittedMedia(Application_Media_Document_Image $document) : void
    {
        $alreadyStarted = DBHelper::isTransactionStarted();

        if(!$alreadyStarted) {
            DBHelper::startTransaction();
        }

        $document->delete();

        if(!$alreadyStarted) {
            DBHelper::commitTransaction();
        }
    }

    /**
     * @return array<string,string> Response header name => value, from a
     *         real HTTP request to `media.php` for the given media ID.
     */
    private function fetchHeaders(int $mediaID) : array
    {
        $url = APP_URL.'/media.php?'.http_build_query(array(
            'source' => MediaCollection::MEDIA_TYPE,
            MediaCollection::PRIMARY_NAME => $mediaID,
        ));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);

        $this->assertIsString($response, 'The request must have received a response.');

        $headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->assertSame(200, $httpCode, 'Serving a committed media document must succeed.');

        $headers = array();

        foreach(explode("\r\n", substr($response, 0, $headerSize)) as $line) {
            if(!str_contains($line, ':')) {
                continue;
            }

            [$name, $value] = explode(':', $line, 2);
            $headers[trim($name)] = trim($value);
        }

        return $headers;
    }

    public function test_svgIsServedInlineWithSandboxCSP() : void
    {
        $document = $this->createCommittedMedia('example-image.svg');

        $headers = $this->fetchHeaders($document->getID());

        $this->assertStringContainsString('image/svg+xml', $headers['Content-Type'] ?? '');
        $this->assertStringContainsString('inline', $headers['Content-Disposition'] ?? '', 'An SVG must be served inline, not as an attachment.');

        $this->assertSame('nosniff', $headers['X-Content-Type-Options'] ?? null);
        $this->assertSame(MediaResponseHeaders::ACTIVE_CONTENT_CSP, $headers['Content-Security-Policy'] ?? null);
    }

    public function test_pngIsServedWithNosniffOnly() : void
    {
        $document = $this->createCommittedMedia('example-image.png');

        $headers = $this->fetchHeaders($document->getID());

        $this->assertSame('nosniff', $headers['X-Content-Type-Options'] ?? null);
        $this->assertArrayNotHasKey('Content-Security-Policy', $headers);
    }
}
