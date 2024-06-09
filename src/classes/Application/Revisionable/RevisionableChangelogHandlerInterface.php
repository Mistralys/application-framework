<?php

declare(strict_types=1);

namespace Application\Revisionable;

use Application\Interfaces\ChangelogHandlerInterface;

interface RevisionableChangelogHandlerInterface extends ChangelogHandlerInterface
{
    public const CHANGELOG_SET_LABEL = 'set_label';
    public const CHANGELOG_SET_STATE = 'set_state';
}
