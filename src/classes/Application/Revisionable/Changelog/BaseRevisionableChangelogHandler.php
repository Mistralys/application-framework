<?php

declare(strict_types=1);

namespace Application\Revisionable\Changelog;

use Application\Changelog\BaseChangelogHandler;
use Application_StateHandler_State;
use Closure;

abstract class BaseRevisionableChangelogHandler
    extends BaseChangelogHandler
    implements RevisionableChangelogHandlerInterface
{
    public static function _getTypeLabels(): array
    {
        $labels = call_user_func(array(static::class, '_collectTypeLabels'));

        $labels[self::CHANGELOG_SET_LABEL] = t('Changed label');
        $labels[self::CHANGELOG_SET_STATE] = t('Changed state');

        return $labels;
    }

    abstract protected static function _collectTypeLabels() : array;

    public static function resolveSetStateData(Application_StateHandler_State $previous, Application_StateHandler_State $new) : array
    {
        return array(
            'old' => $previous->getLabel(),
            'new' => $new->getLabel()
        );
    }

    protected function resolveSetStateText(array $data) : string
    {
        return t(
            'Changed state from %1$s to %2$s.',
            sb()->bold($data['old']),
            sb()->bold($data['new'])
        );
    }

    protected function resolveSetLabelText(array $data) : string
    {
        return t(
            'Changed the label from %1$s to %2$s.',
            sb()->bold($data['old']),
            sb()->bold($data['new'])
        );
    }

    protected function registerTextCallbacks(): void
    {
        $this->registerTextCallback(
            self::CHANGELOG_SET_STATE,
            Closure::fromCallable(array($this, 'resolveSetStateText'))
        );

        $this->registerTextCallback(
            self::CHANGELOG_SET_LABEL,
            Closure::fromCallable(array($this, 'resolveSetLabelText'))
        );

        $this->_registerTextCallbacks();
    }

    abstract protected function _registerTextCallbacks() : void;
}
