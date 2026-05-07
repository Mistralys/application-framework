<?php
/**
 * PHPStan stubs for the CLI utility functions defined in
 * {@see tools/include/cli-utilities.php}.
 *
 * These stubs declare only function signatures (no bodies) so that PHPStan
 * can resolve the functions when `tools/` is included in the analysis scope.
 * Without this file, adding `./tools` to `phpstan.neon` `paths:` would
 * produce ~91 `function.notFound` false-positive errors.
 *
 * MAINTENANCE: Keep signatures in sync with `tools/include/cli-utilities.php`.
 * The source file is small and rarely changes — a manual review on each
 * structural change to `tools/include/cli-utilities.php` is sufficient.
 *
 * @package Application
 * @subpackage Tests
 * @see tools/include/cli-utilities.php The source file these stubs mirror.
 */

declare(strict_types=1);

/**
 * Writes a line of text to STDOUT followed by a newline.
 *
 * @param string $text The text to output. Pass an empty string for a blank line.
 * @return void
 */
function writeln(string $text = '') : void {}

/**
 * Wraps the given text in ANSI escape codes for the specified colour.
 *
 * Supported colours: green, red, yellow, cyan, bold.
 * Returns plain text (no ANSI codes) for unrecognised colour names or on Windows.
 *
 * @param string $text The text to colorize.
 * @param string $name One of: green, red, yellow, cyan, bold.
 * @return string The text wrapped in the appropriate ANSI escape sequence,
 *                or the plain text when the colour is unrecognised or the
 *                OS family is Windows.
 */
function color(string $text, string $name) : string {}

/**
 * Displays a prompt and reads a line of input from STDIN.
 *
 * When a default value is provided it is shown in square brackets and
 * returned unchanged if the user presses Enter without typing anything.
 *
 * @param string $label   The label to display before the input cursor.
 * @param string $default The value to use when the user provides no input.
 * @return string The trimmed user input, or $default when input is empty.
 */
function prompt(string $label, string $default = '') : string {}

/**
 * Displays a prompt and reads a password from STDIN without echoing input.
 *
 * On Unix-like systems `stty -echo` is used to suppress character echo.
 * On Windows (or when stty is unavailable) input is read with echo visible.
 *
 * @param string $label   The label to display before the input cursor.
 * @param string $default The value to use when the user provides no input.
 * @return string The trimmed user input, or $default when input is empty.
 * @see tools/setup-local.php SIGINT handler (pcntl_signal) for the echo-restore pattern.
 */
function promptPassword(string $label, string $default = '') : string {}
