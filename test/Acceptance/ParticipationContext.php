<?php
declare(strict_types=1);

namespace Test\Acceptance;

use DateTimeImmutable;
use LeanpubBookClub\Application\AttendSession;
use LeanpubBookClub\Application\PlanSession;
use LeanpubBookClub\Application\RequestAccess;
use LeanpubBookClub\Domain\Model\Member\MemberId;
use LeanpubBookClub\Domain\Model\Session\SessionId;
use PHPUnit\Framework\Assert;
use RuntimeException;

final class ParticipationContext extends FeatureContext
{
    private ?SessionId $sessionId = null;

    private ?MemberId $memberId = null;

    private ?SessionId $plannedSessionId = null;

    private ?string $plannedSessionDescription = null;

    /**
     * @Given today is :date
     */
    public function todayIs(string $date): void
    {
        $currentTime = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        Assert::assertInstanceOf(DateTimeImmutable::class, $currentTime);

        $this->serviceContainer()->setCurrentTime($currentTime);
    }

    /**
     * @When the administrator schedules a session for :date with description :description
     */
    public function theAdministratorSchedulesASessionForDateWithDescription(string $date, string $description): void
    {
        $this->plannedSessionId = $this->application()->planSession(
            new PlanSession($date, $description, 10)
        );
        $this->plannedSessionDescription = $description;
    }

    /**
     * @Then this session should show up in the list of upcoming sessions for the active member
     */
    public function thisSessionShouldShowUpInTheListOfUpcomingSessions(): void
    {
        Assert::assertNotNull($this->plannedSessionId);
        Assert::assertNotNull($this->plannedSessionDescription);
        Assert::assertNotNull($this->memberId);

        foreach ($this->application()->listUpcomingSessions($this->memberId) as $upcomingSession) {
            if ($this->plannedSessionId->asString() === $upcomingSession->sessionId()
                && $this->plannedSessionDescription === $upcomingSession->description()) {
                return;
            }
        }

        throw new RuntimeException('Planned session not found in list of upcoming sessions');
    }

    /**
     * @Given an upcoming session
     */
    public function anUpcomingSession(): void
    {
        $this->sessionId = $this->application()->planSession(
            new PlanSession('2020-04-01 20:00', 'Chapter 1', 10)
        );
    }

    /**
     * @Given a member who has been granted access
     */
    public function aMemberWhoHasBeenGrantedAccess(): void
    {
        $this->memberId = $this->application()->requestAccess(
            new RequestAccess('info@matthiasnoback.nl', 'jP6LfQ3UkfOvZTLZLNfDfg')
        );
        $this->application()->grantAccess($this->memberId);
    }

    /**
     * @When the member registers themselves as a participant of the session
     */
    public function theMemberRegistersThemselvesAsAParticipantOfTheSession(): void
    {
        Assert::assertNotNull($this->sessionId);
        Assert::assertNotNull($this->memberId);

        $this->application()->attendSession(
            new AttendSession($this->sessionId->asString(), $this->memberId->asString())
        );
    }

    /**
     * @Then the list of upcoming sessions indicates that they have been registered as a participant
     */
    public function theListOfUpcomingSessionsIndicatesThatTheyHaveBeenRegisteredAsAParticipant(): void
    {
        Assert::assertNotNull($this->sessionId);
        Assert::assertNotNull($this->memberId);

        foreach ($this->application()->listUpcomingSessions($this->memberId) as $session) {
            if ($session->sessionId() === $this->sessionId->asString()) {
                Assert::assertTrue($session->memberIsRegisteredAsAttendee());
                return;
            }
        }

        throw new RuntimeException('The list of upcoming sessions did not show the active member as an attendee');
    }
}