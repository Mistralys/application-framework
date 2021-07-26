<?php
/**
 * File containing the template class {@see template_default_content_welcome}.
 *
 * @package UserInterface
 * @subpackage Templates
 * @see template_default_content_welcome
 */

declare(strict_types=1);

/**
 * Renders the content of the welcome screen, with the user's
 * recent items lists.
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class template_default_content_welcome extends UI_Page_Template_Custom
{
    protected function generateOutput(): void
    {
        $greeting = $this->getGreeting();

        if(!empty($greeting))
        {
            ?>
                <p><?php echo $greeting ?></p>
            <?php
        }

        if(!$this->recent->hasEntries())
        {
            ?>
                <p><?php
                    pts('There are no items to show here yet.');
                    pts(
                        'We will keep track of the items you work on (for ex. %1$s), and show them here so you can access them easily.',
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
        <?php


        $categories = $this->recent->getCategories();

        foreach($categories as $category)
        {
            $this->renderCategory($category);
        }
    }

    private function renderCategory(Application_User_Recent_Category $category) : void
    {
        $entries = $category->getEntries();

        if(empty($entries)) {
            return;
        }

        ?>
            <h3><?php echo $category->getLabel() ?></h3>
        <?php

        $sel = $this->ui->createBigSelection();
        $sel->makeSmall();

        foreach($entries as $entry)
        {
            $sel->addLink($entry->getLabel(), $entry->getUrl());
        }

        $sel->display();
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