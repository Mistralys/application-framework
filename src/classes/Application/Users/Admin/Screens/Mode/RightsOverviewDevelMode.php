<?php

declare(strict_types=1);

namespace Application\Users\Admin\Screens\Mode;

use Application;
use Application\Admin\Area\BaseMode;
use Application\Admin\Traits\DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait;
use Application\Development\Admin\DevScreenRights;
use Application_User_Rights;
use Application_User_Rights_Container;
use Application_User_Rights_Group;
use Application_User_Rights_Role;
use UI_Themes_Theme_ContentRenderer;

class RightsOverviewDevelMode extends BaseMode implements DevelModeInterface
{
    use DevelModeTrait;

    public const string URL_NAME = 'rightsoverview';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('User rights overview');
    }

    public function getNavigationTitle(): string
    {
        return t('User rights');
    }

    public function getDevCategory(): string
    {
        return t('Documentation');
    }

    public function getRequiredRight(): string
    {
        return DevScreenRights::SCREEN_RIGHTS_OVERVIEW;
    }

    public function _renderContent(): UI_Themes_Theme_ContentRenderer
    {
        $user = Application::getUser();

        $this->renderDetails($user->getRightsManager()->getRights());
        $this->renderRoles($user->getRightsManager()->getRoles());
        $this->renderGroups($user->getRightsManager());

        return $this->renderer
            ->setTitle($this->getTitle())
            ->setAbstract(sb()
                ->t('The following is a list of all user rights registered in the system, grouped by administration area.')
                ->t('Each right details any additional rights it may grant, which are the %1$s.', sb()->quote(t('Explicit grants')))
                ->t('The actual rights a user will be granted by a single right are called %1$s.', sb()->quote(t('Effective grants')))
                ->t('These are computed by combining all explicit grants along with their respective grants, if any.')
            )
            ->makeWithoutSidebar();
    }

    /**
     * @param Application_User_Rights_Role[] $roles
     */
    private function renderRoles(array $roles): void
    {
        $this->renderer->appendContent(
            '<h2>' . t('Roles overview') . '</h2>' .
            $this->renderRolesList($roles) .
            '<p><br></p>'
        );
    }

    private function renderDetails(Application_User_Rights_Container $container): void
    {
        $this->renderer->appendContent(
            '<h2>' . t('Overview of all rights') . '</h2>' .
            $this->ui->createSection()
                ->setTitle(t('Rights overview'))
                ->collapse()
                ->setContent($this->renderRightsList($container)) .
            '<p><br></p>'
        );
    }

    /**
     * @param Application_User_Rights_Role[] $roles
     * @return string
     */
    private function renderRolesList(array $roles): string
    {
        $html = '';

        foreach ($roles as $role) {
            $html .= $this->ui->createSection()
                ->setTitle($role->getLabel())
                ->setTagline(sb()->code($role->getID()))
                ->collapse()
                ->setContent(
                    '<p>' . t('Explicit rights:') . ' ' . implode(', ', $role->getRightIDs()) . '</p>' .
                    '<p>' . t('Rights that are granted by this role:') . '</p>' .
                    $this->renderRightsList($role->getRights()->resolveAllRights()) .
                    '<p>' . t('Rights that are excluded by this role:') . '</p>' .
                    $this->renderRightsList($role->getLeftoverRights())
                )
                ->render();
        }

        return $html;
    }

    private function renderRightsList(Application_User_Rights_Container $container): string
    {
        $rights = $container->getAll();

        $grid = $this->ui->createDataGrid('list_' . nextJSID());
        $grid->disableFooter();
        $grid->enableCompactMode();
        $grid->addColumn('icon', '')->setCompact();
        $grid->addColumn('id', t('Name'))->setCompact()->setSortingString();
        $grid->addColumn('description', t('Description'));
        $grid->addColumn('group', t('Group'))->setSortingString();

        $entries = array();
        foreach ($rights as $right) {
            $entries[] = array(
                'icon' => $right->getActionIcon(),
                'id' => $right->getID(),
                'description' => $right->getDescription(),
                'group' => $right->getGroup()->getLabel()
            );
        }

        return $grid->render($entries);
    }

    private function renderGroups(Application_User_Rights $manager): void
    {
        $groups = $manager->getGroups();

        $this->renderer->appendContent('<h2>' . t('Granted rights') . '</h2>');

        foreach ($groups as $group) {
            $this->renderer->appendContent(
                $this->ui->createSection()
                    ->setGroup('rights')
                    ->collapse()
                    ->setTitle($group->getLabel())
                    ->setAbstract($group->getDescription())
                    ->setContent($this->renderRightsGrants($group))
            );
        }
    }

    private function renderRightsGrants(Application_User_Rights_Group $group): string
    {
        $container = $group->getRights();
        $rights = $container->getAll();
        $list = array();

        $grid = $this->ui->createDataGrid('rights_grants_' . strtolower($group->getID()));
        $grid->addColumn('id', t('Name'))->setSortingString()->setCompact();
        $grid->addColumn('grants', t('Explicit grants'))->setTooltip(t('This right explicitly grants these rights.'));
        $grid->addColumn('resolved', t('Effective grants'))->setTooltip(t('These are all rights given by the explicit grants.'));

        $grid->disableFooter();

        foreach ($rights as $right) {
            $list[] = array(
                'id' => sb()->add($right->getActionIcon())->add($right->getID()),
                'grants' => $this->renderGrants($right->getGrants(), $group),
                'resolved' => $this->renderGrants($right->resolveGrants(), $group),
            );
        }

        return $grid->render($list);
    }

    private function renderGrants(Application_User_Rights_Container $container, Application_User_Rights_Group $originGroup): string
    {
        $rights = $container->getAll();

        if (empty($rights)) {
            return '';
        }

        $list = array();
        $rights = $container->getAll();

        foreach ($rights as $right) {
            $label = sb()
                ->add($right->getActionIcon())
                ->add($right->getID());

            if ($right->getGroup()->getID() !== $originGroup->getID()) {
                $label->muted('(' . $right->getGroup()->getLabel() . ')');
            }

            $list[] = $label;
        }

        return implode('<br>', $list);
    }
}
