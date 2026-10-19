<?php
/**
 * @package Application
 * @subpackage Media
 */

declare(strict_types=1);

namespace Application\Media\Delivery;

/**
 * Response-header policy for files served through {@see \Application\Media\DocumentTrait}'s
 * shared serving seam ({@see \Application\Media\DocumentTrait::serveFromRequest()} and
 * {@see \Application\Media\DocumentTrait::sendFile()}), used by both media documents and
 * uploads.
 *
 * Every served file gets `X-Content-Type-Options: nosniff`, preventing a browser from
 * MIME-sniffing a served file into a more dangerous content type than the one it was
 * stored and served as. A file extension in {@see self::ACTIVE_CONTENT_EXTENSIONS}
 * (currently SVG/SVGZ, which can carry inline `<script>` content executed by a browser
 * when the file is opened directly — never when embedded via `<img>`) additionally gets
 * a sandboxing Content-Security-Policy, neutralising that script without altering the
 * served file's bytes.
 *
 * @package Application
 * @subpackage Media
 */
class MediaResponseHeaders
{
    /**
     * File extensions (lowercase) that may carry active content (e.g. inline
     * `<script>`) a browser could execute when the file is opened directly.
     *
     * @var string[]
     */
    public const ACTIVE_CONTENT_EXTENSIONS = array('svg', 'svgz');

    /**
     * The Content-Security-Policy applied to a served file whose extension is
     * in {@see self::ACTIVE_CONTENT_EXTENSIONS}: disallows every resource type
     * by default, allows only inline styles (needed by legitimate SVG styling),
     * and sandboxes the document — which disables script execution, among
     * other restrictions — without touching the file's own content.
     */
    public const ACTIVE_CONTENT_CSP = "default-src 'none'; style-src 'unsafe-inline'; sandbox";

    /**
     * Builds the response headers to send for a file with the given
     * extension, without sending them.
     *
     * @param string $extension The extension of the file actually being sent
     *        (the thumbnail/original path's extension), matched
     *        case-insensitively.
     * @return array<string,string> Header name => value.
     */
    public static function forExtension(string $extension) : array
    {
        $headers = array(
            'X-Content-Type-Options' => 'nosniff'
        );

        if(in_array(strtolower($extension), self::ACTIVE_CONTENT_EXTENSIONS, true)) {
            $headers['Content-Security-Policy'] = self::ACTIVE_CONTENT_CSP;
        }

        return $headers;
    }

    /**
     * Sends the response headers for a file with the given extension (see
     * {@see self::forExtension()}), unless headers have already been sent.
     *
     * @param string $extension The extension of the file actually being sent.
     * @param bool|null $headersSent Overrides the `headers_sent()` check used
     *        to decide whether sending is still possible. Left at `null` in
     *        production, where the real global state is used. Unit tests
     *        pass an explicit value instead of relying on `headers_sent()`
     *        itself, since PHPUnit's own output buffering keeps it `false`
     *        regardless of echoed test output under the CLI SAPI — the same
     *        kind of untestable global state
     *        {@see \Application\ConfigSettings\AppConfig::deriveAPIMaxRequestBodySize()}
     *        works around for boot constants.
     * @return bool `true` if the headers were sent, `false` if headers were
     *        already sent and nothing was emitted.
     */
    public static function send(string $extension, ?bool $headersSent=null) : bool
    {
        if($headersSent ?? headers_sent()) {
            return false;
        }

        foreach(self::forExtension($extension) as $name => $value) {
            header($name.': '.$value);
        }

        return true;
    }
}
