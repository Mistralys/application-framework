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
     * @param string $text The text to colorize.
     * @param string $name One of: green, red, yellow, cyan, bold.
     * @return string The text wrapped in the appropriate ANSI escape sequence,
     *                or the plain text when the colour is unrecognised or the
     *                OS family is Windows.
     */
    function color(string $text, string $name) : string
    {
        $codes = array(
            'green'  => "\033[32m",
            'red'    => "\033[31m",
            'yellow' => "\033[33m",
            'cyan'   => "\033[36m",
            'bold'   => "\033[1m",
        );

        if (!isset($codes[$name]))
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

        return $codes[$name] . $text . "\033[0m";
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
     * **Known limitation — stty availability probe in non-interactive contexts:**
     * The availability of `stty` is detected by calling
     * `shell_exec('stty 2>/dev/null; echo $?')`. Because `shell_exec` returns
     * `null` only when the function is disabled in `php.ini`, this probe returns
     * a non-null string even when no TTY is attached (e.g. when the script is
     * run in a piped or non-interactive context). As a result, `$sttyAvailable`
     * will evaluate to `true` and the subsequent `stty -echo` call may silently
     * fail, leaving echo enabled. This function is designed for interactive
     * terminal use only. Do not call it in non-interactive or piped contexts
     * where a TTY is not guaranteed to be present.
     *
     * **Echo-restore safety net on interrupt:**
     * If the user presses Ctrl-C while this function is active, the `stty -echo`
     * state would normally be left in place, suppressing all subsequent terminal
     * output. `tools/setup-local.php` registers a `pcntl_signal(SIGINT, …)`
     * handler that calls `stty echo` to restore echo before exiting. If you use
     * `promptPassword()` in a new script, register a similar handler near the top
     * of that script so echo is always restored on interrupt:
     *
     * ```php
     * if (function_exists('pcntl_signal')) {
     *     pcntl_signal(SIGINT, static function () : void {
     *         shell_exec('stty echo 2>/dev/null');
     *         writeln();
     *         writeln('Interrupted.');
     *         exit(130);
     *     });
     *     pcntl_async_signals(true);
     * }
     * ```
     *
     * @param string $label   The label to display before the input cursor.
     * @param string $default The value to use when the user provides no input.
     * @return string The trimmed user input, or $default when input is empty.
     * @see tools/setup-local.php SIGINT handler (pcntl_signal) for the echo-restore pattern.
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
