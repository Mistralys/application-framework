<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API\Params;

use Application\API\Parameters\Handlers\BaseParamHandler;
use Application\Media\Collection\MediaRecord;
use AppUtils\ClassHelper;

/**
 * Handler for the {@see MediaIDParam} parameter, resolving it to a
 * {@see MediaRecord}.
 *
 * @package Media
 * @subpackage API
 *
 * @method MediaIDParam|NULL getParam()
 * @method MediaIDParam register()
 */
class MediaIDHandler extends BaseParamHandler
{
    protected function resolveValueFromSubject() : ?MediaRecord
    {
        return $this->getParam()?->getMediaRecord();
    }

    public function register() : MediaIDParam
    {
        return ClassHelper::requireObjectInstanceOf(
            MediaIDParam::class,
            parent::register()
        );
    }

    public function getParam() : ?MediaIDParam
    {
        $param = parent::getParam();

        if($param instanceof MediaIDParam) {
            return $param;
        }

        return null;
    }

    protected function createParam() : MediaIDParam
    {
        return new MediaIDParam();
    }
}
