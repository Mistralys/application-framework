<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer;

/**
 * Static registry that collects notices, warnings, and errors emitted
 * during a Composer build run ({@see ComposerScripts::build()} /
 * {@see ComposerScripts::buildDEV()}).
 *
 * Any build step can add a message via {@see self::addMessage()}. At the
 * end of the build {@see self::printSummary()} is called to render all
 * collected messages in a highlighted block so they are not lost in the
 * regular console log output.
 *
 * Usage example:
 *
 * ```php
 * BuildMessages::addMessage('MyGenerator', BuildMessages::LEVEL_WARNING, 'Something looks off.');
 * ```
 *
 * @package Application
 * @subpackage Composer
 */
class BuildMessages
{
    public const LEVEL_NOTICE  = 'NOTICE';
    public const LEVEL_WARNING = 'WARNING';
    public const LEVEL_ERROR   = 'ERROR';

    /**
     * @var array<int, array{source: string, level: string, message: string}>
     */
    private static array $messages = array();

    /**
     * Adds a message to the build-time collection.
     *
     * @param string $source  Short label identifying the build step (e.g. `ModulesOverviewGenerator`).
     * @param string $level   Severity — use one of the `LEVEL_*` constants.
     * @param string $message The human-readable message text.
     * @return void
     */
    public static function addMessage(string $source, string $level, string $message) : void
    {
        // Silently ignore exact duplicates (same source + level + message) that
        // may arise when a stale temp file is carried over between build runs.
        foreach(self::$messages as $existing) {
            if(
                $existing['source']  === $source &&
                $existing['level']   === $level  &&
                $existing['message'] === $message
            ) {
                return;
            }
        }

        self::$messages[] = array(
            'source'  => $source,
            'level'   => $level,
            'message' => $message,
        );
    }

    /**
     * Convenience shorthand for {@see self::addMessage()} with level {@see self::LEVEL_WARNING}.
     *
     * @param string $source
     * @param string $message
     * @return void
     */
    public static function addWarning(string $source, string $message) : void
    {
        self::addMessage($source, self::LEVEL_WARNING, $message);
    }

    /**
     * Convenience shorthand for {@see self::addMessage()} with level {@see self::LEVEL_ERROR}.
     *
     * @param string $source
     * @param string $message
     * @return void
     */
    public static function addError(string $source, string $message) : void
    {
        self::addMessage($source, self::LEVEL_ERROR, $message);
    }

    /**
     * Returns `true` when at least one message has been collected.
     *
     * @return bool
     */
    public static function hasMessages() : bool
    {
        return !empty(self::$messages);
    }

    /**
     * Returns `true` when at least one message with level {@see self::LEVEL_ERROR}
     * has been collected.
     *
     * @return bool
     */
    public static function hasErrors() : bool
    {
        foreach(self::$messages as $entry) {
            if($entry['level'] === self::LEVEL_ERROR) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prints the collected messages to stdout as a prominent summary block,
     * grouped by source. Does nothing when no messages were collected.
     *
     * @return void
     */
    public static function printSummary() : void
    {
        if(!self::hasMessages()) {
            return;
        }

        $separator    = str_repeat('=', 70);
        $subSeparator = str_repeat('-', 70);

        // Group messages by source, preserving insertion order.
        /** @var array<string, list<array{source: string, level: string, message: string}>> $grouped */
        $grouped = array();
        foreach(self::$messages as $entry) {
            $grouped[$entry['source']][] = $entry;
        }

        echo PHP_EOL;
        echo $separator . PHP_EOL;
        echo '  BUILD MESSAGES (' . count(self::$messages) . ')' . PHP_EOL;
        echo $separator . PHP_EOL;

        $first = true;
        foreach($grouped as $source => $entries) {
            if(!$first) {
                echo $subSeparator . PHP_EOL;
            }
            $first = false;

            echo '  ' . $source . ' (' . count($entries) . ')' . PHP_EOL;

            foreach($entries as $entry) {
                echo sprintf('  [%s] %s', $entry['level'], $entry['message']) . PHP_EOL;
            }
        }

        echo $separator . PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * Clears all collected messages. Useful in tests.
     *
     * @return void
     */
    public static function reset() : void
    {
        self::$messages = array();
    }

    /**
     * Serialises all collected messages to a JSON file so they survive across
     * separate PHP process invocations (i.e. between Composer script entries).
     *
     * @param string $filePath Absolute path to the target file.
     * @return void
     */
    public static function saveToFile(string $filePath) : void
    {
        file_put_contents($filePath, json_encode(self::$messages, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    /**
     * Loads messages previously saved by {@see self::saveToFile()} into the
     * in-memory registry. Each entry is passed through {@see self::addMessage()}
     * so duplicate detection is always enforced. Silently does nothing when the
     * file does not exist.
     *
     * @param string $filePath Absolute path to the source file.
     * @return void
     */
    public static function loadFromFile(string $filePath) : void
    {
        if(!file_exists($filePath)) {
            return;
        }

        $raw = file_get_contents($filePath);
        if($raw === false) {
            return;
        }

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if(!is_array($decoded)) {
            return;
        }

        foreach($decoded as $entry) {
            if(
                isset($entry['source'], $entry['level'], $entry['message']) &&
                is_string($entry['source']) &&
                is_string($entry['level']) &&
                is_string($entry['message'])
            ) {
                // Route through addMessage so duplicate detection is always enforced.
                self::addMessage($entry['source'], $entry['level'], $entry['message']);
            }
        }
    }

    /**
     * Deletes the messages file if it exists. Call this at the start of a build
     * run to ensure no stale data from a previous (possibly failed) run leaks in.
     *
     * @param string $filePath Absolute path to the file to remove.
     * @return void
     */
    public static function clearFile(string $filePath) : void
    {
        if(file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
