<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\Delivery;

use Application\Media\Delivery\MediaResponseHeaders;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Tests for {@see MediaResponseHeaders}, the response-header policy applied
 * to every file served through {@see \Application\Media\DocumentTrait}'s
 * shared serving seam.
 */
final class MediaResponseHeadersTest extends ApplicationTestCase
{
    // region: forExtension()

    public function test_nosniffForEveryExtension() : void
    {
        foreach(array('png', 'jpg', 'pdf', 'svg', 'svgz', 'gif', 'txt') as $extension) {
            $headers = MediaResponseHeaders::forExtension($extension);

            $this->assertArrayHasKey('X-Content-Type-Options', $headers, sprintf('Extension [%s] must carry nosniff.', $extension));
            $this->assertSame('nosniff', $headers['X-Content-Type-Options']);
        }
    }

    public function test_cspOnlyForActiveContent() : void
    {
        $this->assertArrayHasKey('Content-Security-Policy', MediaResponseHeaders::forExtension('svg'));
        $this->assertArrayHasKey('Content-Security-Policy', MediaResponseHeaders::forExtension('svgz'));

        $this->assertSame(
            MediaResponseHeaders::ACTIVE_CONTENT_CSP,
            MediaResponseHeaders::forExtension('svg')['Content-Security-Policy']
        );

        foreach(array('png', 'jpg', 'pdf', 'gif', 'txt') as $extension) {
            $headers = MediaResponseHeaders::forExtension($extension);

            $this->assertArrayNotHasKey(
                'Content-Security-Policy',
                $headers,
                sprintf('Extension [%s] must not carry the active-content CSP.', $extension)
            );
        }
    }

    public function test_activeContentMatchIsCaseInsensitive() : void
    {
        foreach(array('SVG', 'Svg', 'SVGZ', 'SvGz') as $extension) {
            $this->assertArrayHasKey(
                'Content-Security-Policy',
                MediaResponseHeaders::forExtension($extension),
                sprintf('Extension [%s] must be matched case-insensitively.', $extension)
            );
        }
    }

    // endregion

    // region: send()

    public function test_sendReportsWhetherHeadersWereEmitted() : void
    {
        // headers_sent() cannot be forced to true from inside a PHPUnit test
        // under the CLI SAPI (PHPUnit's own output buffering keeps it false
        // regardless of echoed test output), so the explicit override
        // parameter is used instead — see the decision recorded for this WP.
        $this->assertFalse(MediaResponseHeaders::send('png', true), 'send() must report false and emit nothing when headers were already sent.');
        $this->assertTrue(MediaResponseHeaders::send('png', false), 'send() must report true when headers had not been sent yet.');
    }

    // endregion
}
