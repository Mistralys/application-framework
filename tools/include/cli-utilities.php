<?php
/**
 * Shared CLI utility functions for the developer tools scripts.
 *
 * Provides console I/O helpers for the developer CLI scripts in the `tools/`
 * directory. Intended to be included by scripts such as menu.php and
 * setup-local.php.
 * All functions are guarded with function_exists() to allow safe re-inclusion.
 *
 * @package Application
 * @subpackage Tools
 */

declare(strict_types=1);

// ----------------------------------------------------------------------------
// writeln
// ----------------------------------------------------------------------------

if (!function_exists('writeln'))
{
    /**
     * Writes a line of text to STDOUT followed by a newline.
     *
     * @param string $text The text to output. Pass an empty string for a blank line.
     * @return void
     */
    function writeln(string $text = '') : void
    {
        echo $text . PHP_EOL;
    }
}

// ----------------------------------------------------------------------------
// color
// ----------------------------------------------------------------------------

if (!function_exists('color'))
{
    /**
     * Wraps the given text in ANSI escape codes for the specified colour.
     *
     * Supported colours: green, red, yellow, cyan, bold.
     * Returns plain text (no ANSI codes) in two cases:
     *   - The colour name is not in the supported list (unrecognised colour).
     *   - The runtime OS family is Windows (PHP_OS_FAMILY === 'Windows').
     *     Note: modern Windows environments such as Windows Terminal and
     *     PowerShell 7+ do support ANSI escape sequences. However, this
     *     implementation uses a conservative fallback — plain text is
     *     returned for all Windows environments — to avoid introducing a
     *     dependency on runtime terminal-capability detection (e.g. checking
     *     ANSICON, WT_SESSION, or VT processing mode). If your environment
     *     reliably supports ANSI on Windows, the PHP_OS_FAMILY guard can be
     *     replaced with a capability check.
     *
     * @param string $text  The text to colorize.
     * @param string $color One of: green, red, yellow, cyan, bold.
     * @return string The text wrapped in the appropriate ANSI escape sequence,
     *                or the plain text when the colour is unrecognised or the
     *                OS family is Windows.
     */
    function color(string $text, string $color) : string
    {
        $codes = array(
            'green'  => "\033[32m",
            'red'    => "\033[31m",
            'yellow' => "\033[33m",
            'cyan'   => "\033[36m",
            'bold'   => "\033[1m",
        );

        if (!isset($codes[$color]))
        {
            return $text;
        }

        // Conservative Windows fallback: return plain text for all Windows
        // environments to avoid a dependency on runtime terminal-capability
        // detection. Modern terminals (Windows Terminal, PowerShell 7+) do
        // support ANSI, but detecting them reliably requires additional checks
        // (e.g. WT_SESSION env var, ANSICON, or enabling VT processing via
        // the Win32 API). Plain text is always safe; colour is a nicety.
        if (PHP_OS_FAMILY === 'Windows')
        {
            return $text;
        }

        return $codes[$color] . $text . "\033[0m";
    }
}

// ----------------------------------------------------------------------------
// prompt
// ----------------------------------------------------------------------------

if (!function_exists('prompt'))
{
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
    function prompt(string $label, string $default = '') : string
    {
        if ($default !== '')
        {
            echo $label . ' [' . $default . ']: ';
        }
        else
        {
            echo $label . ': ';
        }

        $input = fgets(STDIN);

        if ($input === false)
        {
            return $default;
        }

        $trimmed = trim($input);

        if ($trimmed === '')
        {
            return $default;
        }

        return $trimmed;
    }
}

// ----------------------------------------------------------------------------
// promptPassword
// ----------------------------------------------------------------------------

if (!function_exists('promptPassword'))
{
    /**
     * Displays a prompt and reads a password from STDIN without echoing input.
     *
     * On Unix-like systems `stty -echo` is used to suppress character echo.
     * On Windows (or when stty is unavailable) input is read with echo visible
     * and a warning is displayed to the user.
     *
     * @param string $label   The label to display before the input cursor.
     * @param string $default The value to use when the user provides no input.
     * @return string The trimmed user input, or $default when input is empty.
     */
    function promptPassword(string $label, string $default = '') : string
    {
        // shell_exec returns null only when the function is disabled (e.g. php.ini
        // disable_functions). The DIRECTORY_SEPARATOR guard already excludes Windows;
        // the null-check provides a secondary guard for environments where shell_exec
        // itself is disabled. The stty exit status (via `; echo $?`) is discarded here
        // because the meaningful check is whether stty is actually available, which is
        // tested by calling it below — if it fails, the shell absorbs the error silently.
        $sttyAvailable = (DIRECTORY_SEPARATOR !== '\\')
            && (shell_exec('stty 2>/dev/null; echo $?') !== null);

        if ($default !== '')
        {
            echo $label . ' [' . str_repeat('*', min(8, strlen($default))) . ']: ';
        }
        else
        {
            echo $label . ': ';
        }

        if ($sttyAvailable)
        {
            shell_exec('stty -echo 2>/dev/null');
            $input = fgets(STDIN);
            shell_exec('stty echo 2>/dev/null');
            echo PHP_EOL;
        }
        else
        {
            writeln('(warning: password will be visible — stty not available)');
            $input = fgets(STDIN);
        }

        if ($input === false)
        {
            return $default;
        }

        $trimmed = trim($input);

        if ($trimmed === '')
        {
            return $default;
        }

        return $trimmed;
    }
}
