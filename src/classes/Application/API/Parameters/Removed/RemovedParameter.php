<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Removed;

use Application\API\Parameters\APIParameterException;

/**
 * Represents a single API parameter that has been removed from an
 * API method: Its name is still known to the framework so that a
 * request still sending it can be detected and rejected with a
 * helpful error message, instead of the application having to
 * hand-write a guard for it.
 *
 * Instances are created via {@see \Application\API\Parameters\APIParamManager::addRemovedParam()}.
 *
 * @package API
 * @subpackage Parameters
 */
class RemovedParameter
{
    public const string KEY_NAME = 'name';
    public const string KEY_REPLACEMENT = 'replacement';
    public const string KEY_REMOVED_SINCE = 'removedSince';
    public const string KEY_NOTE = 'note';

    /**
     * Characters that PHP's request parameter parsing mangles or drops
     * (`parse_str()`-style key handling): a `.` or space is converted to
     * `_`, an unmatched `[` is converted to `_` (or opens an array key),
     * and a NUL byte truncates the key. A lone `]` is preserved verbatim
     * by PHP, so it is deliberately not part of this denylist. A
     * removed-parameter name must not contain any of the denylisted
     * characters, since {@see \Application\API\Parameters\Removed\RemovedParamCollection::detectUsed()}
     * compares the declared name against the raw request keys with
     * `array_key_exists()` - a declared name whose client-sent `.`/space/`[`/NUL
     * variant can never match would mean the declaration silently never fires.
     */
    public const string INVALID_NAME_CHARACTERS = ". [\0";

    private string $name;
    private ?string $replacement = null;
    private ?string $removedSince = null;
    private ?string $note = null;

    public function __construct(string $name)
    {
        self::requireValidName($name);

        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Declares the name of the parameter that replaces this removed one,
     * if any. Used to enrich {@see self::getErrorMessage()}.
     *
     * @param string $replacement
     * @return $this
     */
    public function setReplacement(string $replacement) : self
    {
        self::requireValidName($replacement);

        $this->replacement = $replacement;
        return $this;
    }

    public function getReplacement() : ?string
    {
        return $this->replacement;
    }

    public function hasReplacement() : bool
    {
        return $this->replacement !== null;
    }

    /**
     * Declares the version since which the parameter has been removed.
     * Free-form string, purely informational for the error message.
     *
     * @param string $version
     * @return $this
     */
    public function setRemovedSince(string $version) : self
    {
        $this->removedSince = $version;
        return $this;
    }

    public function getRemovedSince() : ?string
    {
        return $this->removedSince;
    }

    public function hasRemovedSince() : bool
    {
        return $this->removedSince !== null;
    }

    /**
     * Sets a free-form note to append to the error message, e.g. to
     * explain the context of the removal. The note is used verbatim:
     * it is never interpreted as a format string, so literal `%` or
     * `%s` sequences are safe to use.
     *
     * @param string $note
     * @return $this
     */
    public function setNote(string $note) : self
    {
        $this->note = $note;
        return $this;
    }

    public function getNote() : ?string
    {
        return $this->note;
    }

    public function hasNote() : bool
    {
        return $this->note !== null;
    }

    /**
     * Composes the human-readable error message to return to a client
     * that still sends this removed parameter.
     *
     * @return string
     */
    public function getErrorMessage() : string
    {
        $message = sprintf(
            'The parameter [%s] has been removed and may no longer be used.',
            $this->name
        );

        if($this->hasReplacement()) {
            $message .= sprintf(' Use [%s] instead.', $this->replacement);
        }

        if($this->hasRemovedSince()) {
            $message .= sprintf(' Removed since version %s.', $this->removedSince);
        }

        if($this->hasNote()) {
            $message .= sprintf(' %s', $this->note);
        }

        return $message;
    }

    /**
     * @return array<string,string|NULL>
     */
    public function toArray() : array
    {
        return array(
            self::KEY_NAME => $this->name,
            self::KEY_REPLACEMENT => $this->replacement,
            self::KEY_REMOVED_SINCE => $this->removedSince,
            self::KEY_NOTE => $this->note
        );
    }

    /**
     * @param string $name
     * @return void
     * @throws APIParameterException {@see APIParameterException::ERROR_INVALID_REMOVED_PARAM_CONFIG}
     */
    private static function requireValidName(string $name) : void
    {
        $length = strlen($name);

        for($i = 0; $i < $length; $i++) {
            if(str_contains(self::INVALID_NAME_CHARACTERS, $name[$i])) {
                throw new APIParameterException(
                    'Invalid removed parameter name.',
                    sprintf(
                        'The removed parameter name [%s] contains a character ("%s") that PHP mangles or drops '.
                        'when parsing request parameter keys (".", space, "[", or a NUL byte). '.
                        'The declared name must match the exact key a client would send, so detection and '.
                        'the generated error message stay consistent with each other.',
                        $name,
                        addcslashes($name[$i], "\0..\x1f")
                    ),
                    APIParameterException::ERROR_INVALID_REMOVED_PARAM_CONFIG
                );
            }
        }
    }
}
