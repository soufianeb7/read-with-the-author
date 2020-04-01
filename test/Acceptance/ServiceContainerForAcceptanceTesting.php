<?php
declare(strict_types=1);

namespace Test\Acceptance;

use Assert\Assert;
use LeanpubBookClub\Application\EventDispatcherWithSubscribers;
use LeanpubBookClub\Domain\Model\Common\TimeZone;
use LeanpubBookClub\Domain\Model\Session\AttendeeRegisteredForSession;
use LeanpubBookClub\Domain\Model\Session\SessionWasPlanned;
use LeanpubBookClub\Infrastructure\ServiceContainer;

final class ServiceContainerForAcceptanceTesting extends ServiceContainer
{
    private ?EventDispatcherSpy $eventDispatcherSpy = null;

    public function setCurrentTime(string $time): void
    {
        $clock = $this->clock();
        Assert::that($clock)->isInstanceOf(FakeClock::class);

        $clock->setCurrentTime($time);
    }

    public function authorTimeZone(): TimeZone
    {
        return parent::authorTimeZone();
    }

    public function setCurrentDate(string $date): void
    {
        $clock = $this->clock();
        Assert::that($clock)->isInstanceOf(FakeClock::class);

        $clock->setCurrentDate($date);
    }

    protected function registerEventSubscribers(EventDispatcherWithSubscribers $eventDispatcher): void
    {
        parent::registerEventSubscribers($eventDispatcher);

        $eventDispatcher->subscribeToAllEvents([$this->eventDispatcherSpy(), 'notify']);

        // Test-specific listeners:
        $eventDispatcher->subscribeToSpecificEvent(
            SessionWasPlanned::class,
            [$this->upcomingSessions(), 'whenSessionWasPlanned']
        );
        $eventDispatcher->subscribeToSpecificEvent(
            AttendeeRegisteredForSession::class,
            [$this->upcomingSessions(), 'whenAttendeeRegisteredForSession']
        );
    }

    public function eventDispatcherSpy(): EventDispatcherSpy
    {
        if ($this->eventDispatcherSpy === null) {
            $this->eventDispatcherSpy = new EventDispatcherSpy();
        }

        return $this->eventDispatcherSpy;
    }

    public function individualPurchases(): IndividualPurchasesInMemory
    {
        $individualPurchases = parent::individualPurchases();
        Assert::that($individualPurchases)->isInstanceOf(IndividualPurchasesInMemory::class);
        /** @var $individualPurchases IndividualPurchasesInMemory */

        return $individualPurchases;
    }

    public function mailer(): MailerSpy
    {
        if ($this->mailer === null) {
            $this->mailer = new MailerSpy();
        }

        Assert::that($this->mailer)->isInstanceOf(MailerSpy::class);
        return $this->mailer;
    }
}
