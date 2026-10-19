<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\Media\API\MediaMetadataAPIInterface;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;
use TestDriver\API\TestMediaMetadataMethod;

/**
 * Covers the media metadata piece ({@see MediaMetadataAPIInterface}/
 * {@see \Application\Media\API\MediaMetadataAPITrait}), through a
 * {@see TestMediaMetadataMethod} instance — the DB-free isolation harness
 * for this piece.
 */
final class MediaMetadataPieceTest extends MediaTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $_REQUEST = array();
        $_POST = array();
        $_GET = array();
    }

    private function createMethod() : TestMediaMetadataMethod
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestMediaMetadataMethod::METHOD_NAME;

        return new TestMediaMetadataMethod(APIManager::getInstance());
    }

    public function test_resolvesSubmittedValues() : void
    {
        $_REQUEST[MediaMetadataAPIInterface::PARAM_NAME] = 'Submitted Name';
        $_REQUEST[MediaMetadataAPIInterface::PARAM_DESCRIPTION] = 'Submitted description.';
        $_REQUEST[MediaMetadataAPIInterface::PARAM_KEYWORDS] = 'one, two, three';

        $data = $this->createMethod()->processReturn()->getData();

        $this->assertSame('Submitted Name', $data[MediaMetadataAPIInterface::PARAM_NAME]);
        $this->assertSame('Submitted description.', $data[MediaMetadataAPIInterface::PARAM_DESCRIPTION]);
        $this->assertSame('one, two, three', $data[MediaMetadataAPIInterface::PARAM_KEYWORDS]);
    }

    public function test_omittedNameUsesDefault() : void
    {
        // 'name' is deliberately left unset.
        $data = $this->createMethod()->processReturn()->getData();

        $this->assertSame(TestMediaMetadataMethod::DEFAULT_NAME, $data[MediaMetadataAPIInterface::PARAM_NAME]);
    }

    public function test_htmlInMetadataIsRejected() : void
    {
        $_REQUEST[MediaMetadataAPIInterface::PARAM_DESCRIPTION] = '<b>bold</b>';

        $response = $this->createMethod()->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $response);
        $this->assertSame(MediaMetadataAPIInterface::VALIDATION_HTML_NOT_ALLOWED, $response->getErrorCode());
    }
}
