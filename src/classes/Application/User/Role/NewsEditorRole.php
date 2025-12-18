<?php
/**
 * @package TestDriver
 * @subpackage User
 */

declare(strict_types=1);

namespace Application\User\Role;

use Application\NewsCentral\User\NewsRightsInterface;
use Application\User\Roles\BaseRole;
use Application_User;

/**
 * @package TestDriver
 * @subpackage User
 */
class NewsEditorRole extends BaseRole
{
    public const ROLE_ID = 'NewsEditor';

    public function getID(): string
    {
        return self::ROLE_ID;
    }

    public const RIGHTS = array(
        Application_User::RIGHT_LOGIN,
        NewsRightsInterface::RIGHT_CREATE_NEWS,
        NewsRightsInterface::RIGHT_EDIT_NEWS,
        NewsRightsInterface::RIGHT_VIEW_NEWS,
        NewsRightsInterface::RIGHT_DELETE_NEWS
    );
    
    public function getLabel(): string
    {
        return t('News editor');
    }
    
    public function getRights(): array
    {
        return self::RIGHTS;
    }
}
