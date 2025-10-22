<?php
/**
 * @package Admin
 * @subpackage Request Types
 */

declare(strict_types=1);

namespace Application\Admin\RequestTypes;

use Application\Admin\ScreenException;
use Application\Interfaces\Admin\AdminScreenInterface;

/**
 * Base implementation of {@see RequestTypeInterface}.
 *
 * @package Admin
 * @subpackage Request Types
 */
abstract class BaseRequestType implements RequestTypeInterface
{
    private AdminScreenInterface $screen;

    public function __construct(AdminScreenInterface $screen)
    {
        $this->screen = $screen;
    }

    public function getRecordOrRedirect()
    {
        $record = $this->getRecord();
        if($record !== null) {
            return $record;
        }

        $this->screen->redirectWithInfoMessage(
            'No record has been specified, or no such record exists.',
            $this->getRecordMissingURL()
        );
    }

    public function requireRecord()
    {
        $record = $this->getRecord();
        if($record !== null) {
            return $record;
        }

        throw new ScreenException(
            $this->screen,
            'No record has been specified in the request.',
            '',
            ScreenException::NO_RECORD_SPECIFIED_IN_REQUEST
        );
    }
}
