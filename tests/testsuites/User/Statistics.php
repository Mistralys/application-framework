<?php

use Mistralys\AppFrameworkTests\TestClasses\UserTestCase;

final class User_StatisticsTest extends UserTestCase
{
    public function test_resetStatistics(): void
    {
        $this->logHeader('Reset statistics');

        $stats = $this->user->getStatistics();

        $stats->reset();

        $this->assertNull($stats->getFirstLogin());
        $this->assertNull($stats->getLastLogin());
        $this->assertSame(0, $stats->getTotalLogins());
        $this->assertSame(0, $stats->getAmountLoginsToday());
        $this->assertEmpty($stats->getDatesLoggedIn());
    }

    public function test_firstLogin(): void
    {
        $this->logHeader('First login');

        $stats = $this->user->getStatistics();

        $stats->reset();

        $stats->handleLoggedIn(new DateTime());

        $this->assertTrue($stats->isFirstLogin());
        $this->assertSame(1, $stats->getTotalLogins());
        $this->assertSame(1, $stats->getAmountLoginsToday());
        $this->assertCount(1, $stats->getDatesLoggedIn());
        $this->assertInstanceOf(DateTime::class, $stats->getFirstLogin());
        $this->assertInstanceOf(DateTime::class, $stats->getLastLogin());
    }

    public function test_multiLogins(): void
    {
        $this->logHeader('Multi logins');

        $stats = $this->user->getStatistics();

        $stats->reset();

        $stats->handleLoggedIn(new DateTime());
        $stats->handleLoggedIn(new DateTime());
        $stats->handleLoggedIn(new DateTime());

        $this->assertFalse($stats->isFirstLogin());
        $this->assertSame(3, $stats->getTotalLogins());
        $this->assertSame(3, $stats->getAmountLoginsToday());
        $this->assertCount(1, $stats->getDatesLoggedIn());
        $this->assertInstanceOf(DateTime::class, $stats->getFirstLogin());
        $this->assertInstanceOf(DateTime::class, $stats->getLastLogin());
    }

    public function test_olderLogins(): void
    {
        $this->logHeader('Older logins');

        $stats = $this->user->getStatistics();

        $stats->reset();

        $date1 = new DateTime(); $date1->sub(new DateInterval('P10D'));
        $date2 = new DateTime(); $date2->sub(new DateInterval('P5D'));
        $date3 = new DateTime();

        $stats->handleLoggedIn($date1);
        $stats->handleLoggedIn($date2);
        $stats->handleLoggedIn($date3);

        $this->assertCount(3, $stats->getDatesLoggedIn());
        $this->assertSame(1, $stats->getAmountLoginsToday());
        $this->assertEquals($date1->format(DateTime::RFC3339_EXTENDED), $stats->getFirstLogin()->format(DateTime::RFC3339_EXTENDED));
        $this->assertEquals(5, $stats->getDaysSincePreviousLogin());
    }
}
