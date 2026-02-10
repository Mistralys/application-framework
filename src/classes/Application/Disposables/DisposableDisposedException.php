<?php
/**
 * @package Application
 * @subpackage Disposable
 */

declare(strict_types=1);

namespace Application\Disposables;

use Application\Exception\ApplicationException;

/**
 * @package Application
 * @subpackage Disposable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DisposableDisposedException extends ApplicationException
{
    public const int ERROR_DISPOSABLE_DISPOSED = 92101;

    public function __construct(DisposableInterface $disposable, ?string $actionLabel)
    {
        if (empty($actionLabel)) {
            $actionLabel = '(not specified)';
        }

        parent::__construct(
            'Object has been disposed',
            sprintf(
                'Tried to do action [%s] on disposable [%s].',
                $actionLabel,
                $disposable->getIdentification()
            ),
            self::ERROR_DISPOSABLE_DISPOSED
        );
    }
}
