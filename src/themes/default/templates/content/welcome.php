<?php
/**
 * File containing the template class {@see template_default_content_welcome}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_content_welcome
 */

declare(strict_types=1);

use Application\Admin\Area\Welcome\OverviewMode;
use AppUtils\ConvertHelper;

/**
 * Renders the content of the welcome screen, with the user's
 * recent items lists.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see OverviewMode
 */
class template_default_content_welcome extends UI_Page_Template_Custom
{
    protected function generateOutput(): void
    {
        $this->ui->addStylesheet('ui/welcome.css');

        $greeting = $this->getGreeting();

        if(!empty($greeting))
        {
            ?>
                <p><?php echo $greeting ?></p>
            <?php
        }

        if(!$this->recent->hasEntries())
        {
            $this->ui->createMessage(t('There are no items to show here yet.'))
                ->makeInfo()
                ->enableIcon()
                ->makeNotDismissable()
                ->display();

            ?>

                <p><?php
                    pts(
                        'We will keep track of the items you work on (for ex. %1$s), and show them here so you can easily access them again.',
                        $this->getExampleItems()
                    );
                ?></p>
            <?php

            return;
        }

        ?>
            <p>
                <?php pt('These are the items you last worked on:') ?>
            </p>
            <div class="welcome-categories" data-masonry='{"itemSelector": ".span6"}'>
                <?php

                $categories = $this->recent->getCategoriesWithNotes(false);

                foreach ($categories as $category)
                {
                    if($category instanceof Application_User_Recent_NoteCategory)
                    {
                        $this->renderNote($category);
                    }
                    else if($category instanceof Application_User_Recent_Category)
                    {
                        $this->renderCategory($category);
                    }
                }
                ?>
            </div>
        <?php
    }

    private function renderCategory(Application_User_Recent_Category $category) : void
    {
        $entries = $category->getEntries();

        if(empty($entries)) {
            return;
        }

        $jsID = nextJSID();

        JSHelper::tooltipify($jsID);

        ?>
            <div class="span6">
                <div class="welcome-category">
                    <div class="welcome-toolbar">
                        <div class="welcome-clear-link">
                            <a href="<?php echo $category->getAdminURLClear() ?>" title="<?php pt('Clears the %1$s history.', $category->getLabel()); ?>" id="<?php echo $jsID ?>">
                            <?php
                                echo sb()
                                    ->add(UI::icon()->deleteSign())
                                    ->t('Clear history');
                            ?>
                            </a>
                        </div>
                    </div>
                    <h3>
                        <?php
                            $icon = $category->getIcon();
                            if($icon) { $icon->display(); }
                        ?>
                        <?php echo $category->getLabel() ?>
                    </h3>
                    <?php

                    $lookup = $category->getLookupItem();
                    if($lookup)
                    {
                        $searchID = nextJSID();
                        $js = $lookup->getJSSearch(sprintf("$('#%s').val()", $searchID));

                        ?>
                        <div class="welcome-lookup">
                            <form class="form-search" onsubmit="<?php echo $js ?>;return false;">
                                <input
                                    id="<?php echo $searchID ?>"
                                    type="search"
                                    class="search-query input-xlarge"
                                    placeholder="<?php pt('%1$s lookup...', $category->getLabel()) ?>"
                                    onfocusin="application.disallowAutoRefresh('welcome-lookup')"
                                    onfocusout="application.allowAutoRefresh('welcome-lookup')"
                                />
                                <input type="submit" name="dummy-submit" style="display: none"/>
                            </form>
                        </div>
                        <?php
                    }

                    $sel = $this->ui->createBigSelection();
                    $sel->makeSmall();

                    $max = $category->getMaxItems();
                    $count = 0;

                    foreach($entries as $entry)
                    {
                        $count++;

                        $sel->addLink($entry->getLabel(), $entry->getUrl())
                        ->setDescription(ConvertHelper::date2listLabel($entry->getDate(), true, true));

                        if($count === $max) {
                            break;
                        }
                    }

                    $sel->display();

                    ?>
                </div>
            </div>
        <?php
    }

    private function renderNote(Application_User_Recent_NoteCategory $category) : void
    {
        $jsID = nextJSID();

        JSHelper::tooltipify($jsID);

        ?>
            <div class="span6">
            <div class="welcome-category">
                <div class="welcome-toolbar">
                    <div class="welcome-clear-link">
                        <a href="<?php echo $category->getAdminURLUnpin() ?>" title="<?php pt('Unpins the notepad note from the quickstart.', $category->getLabel()); ?>" id="<?php echo $jsID ?>">
                            <?php
                            echo sb()
                                ->add(UI::icon()->pin())
                                ->t('Unpin note');
                            ?>
                        </a>
                    </div>
                </div>
                <h3>
                    <?php
                    $icon = $category->getIcon();
                    if($icon) { $icon->display(); }
                    ?>
                    <?php echo $category->getLabel() ?>
                </h3>
                <?php

                echo $category->renderContent();

                ?>
            </div>
            </div>
        <?php
    }

    /**
     * @var Application_User_Recent
     */
    private $recent;

    /**
     * @var Application_User
     */
    private $targetUser;

    /**
     * @var Application_User_Statistics
     */
    private $stats;

    protected function preRender(): void
    {
        $this->recent = $this->getObjectVar('recent', Application_User_Recent::class);
        $this->targetUser = $this->getObjectVar('user', Application_User::class);
        $this->stats = $this->targetUser->getStatistics();
    }

    protected function getGreeting() : string
    {
        $firstname = $this->targetUser->getFirstname();

        if($this->stats->isFirstLogin())
        {
            return t(
                'Hello %1$s, welcome to %2$s!',
                $firstname,
                $this->driver->getAppNameShort()
            );
        }

        // First login of the day
        if($this->stats->getAmountLoginsToday() === 1)
        {
            $tod = new TimeOfDay($this->stats->getLastLogin());

            if($tod->isMorning())
            {
                return t('Good morning, %1$s.', $firstname);
            }
            else if($tod->isAfternoon())
            {
                return t('Good afternoon, %1$s.', $firstname);
            }
            else if($tod->isEvening())
            {
                return t('Good evening, %1$s.', $firstname);
            }

            return t('Hello, %1$s.', $firstname);
        }

        return '';
    }

    private function getExampleItems() : string
    {
        $names = array();
        $categories = $this->recent->getCategories();

        foreach($categories as $category)
        {
            $names[] = $category->getLabel();
        }

        return implode(', ', array_slice($names, 0, 2));
    }
}