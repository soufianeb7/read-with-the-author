<?php
declare(strict_types=1);

namespace LeanpubBookClub\Domain\Model\Member;

use DateTimeImmutable;
use LeanpubBookClub\Domain\Model\Common\EmailAddress;
use LeanpubBookClub\Domain\Model\Common\EntityTestCase;
use LeanpubBookClub\Domain\Model\Common\TimeZone;
use LeanpubBookClub\Domain\Service\AccessTokenGenerator;

final class MemberTest extends EntityTestCase
{
    /**
     * @test
     */
    public function they_can_request_access(): void
    {
        $emailAddress = $this->anEmailAddress();
        $leanpubInvoiceId = $this->aLeanpubInvoiceId();
        $timeZone = $this->aTimeZone();
        $requestedAt = new DateTimeImmutable();

        $member = Member::requestAccess($leanpubInvoiceId, $emailAddress, $timeZone, $requestedAt);

        self::assertEquals(
            [
                new MemberRequestedAccess($leanpubInvoiceId, $emailAddress, $timeZone, $requestedAt)
            ],
            $member->releaseEvents()
        );

        self::assertEquals($leanpubInvoiceId, $member->memberId());
    }

    /**
     * @test
     */
    public function they_can_be_granted_access(): void
    {
        $member = $this->aMember();

        $member->grantAccess();

        self::assertArrayContainsObjectOfType(AccessWasGrantedToMember::class, $member->releaseEvents());
    }

    /**
     * @test
     */
    public function granting_access_twice_has_no_effect(): void
    {
        $member = $this->aMember();
        $member->grantAccess();
        $member->releaseEvents();

        $member->grantAccess();

        self::assertEquals([], $member->releaseEvents());
    }

    /**
     * @test
     */
    public function it_can_generate_a_new_access_token(): void
    {
        $member = $this->aMember();

        $accessToken = AccessToken::fromString('e47755b7-5828-4d34-8471-f41967881312');

        $member->generateAccessToken($this->accessTokenGenerator($accessToken));

        self::assertContainsEquals(
            new AnAccessTokenWasGenerated($member->memberId(), $this->anEmailAddress(), $accessToken),
            $member->releaseEvents()
        );
    }

    /**
     * @test
     */
    public function they_can_change_their_time_zone(): void
    {
        $member = $this->aMember();

        $newTimeZone = TimeZone::fromString('America/New_York');

        $member->changeTimeZone($newTimeZone);

        self::assertContainsEquals(
            new MemberTimeZoneChanged($member->memberId(), $newTimeZone),
            $member->releaseEvents()
        );
    }

    /**
     * @test
     */
    public function it_releases_events_only_once(): void
    {
        $member = $this->aMember();

        self::assertNotEmpty($member->releaseEvents());

        self::assertEmpty($member->releaseEvents());
    }

    private function anEmailAddress(): EmailAddress
    {
        return EmailAddress::fromString('info@matthiasnoback.nl');
    }

    private function aLeanpubInvoiceId(): LeanpubInvoiceId
    {
        return LeanpubInvoiceId::fromString('jP6LfQ3UkfOvZTLZLNfDfg');
    }

    private function aMember(): Member
    {
        return Member::requestAccess($this->aLeanpubInvoiceId(), $this->anEmailAddress(), $this->aTimeZone(), $this->now());
    }

    private function accessTokenGenerator(AccessToken $accessToken): AccessTokenGenerator
    {
        $accessTokenGenerator = $this->createStub(AccessTokenGenerator::class);
        $accessTokenGenerator->method('generate')->willReturn($accessToken);

        return $accessTokenGenerator;
    }

    private function aTimeZone(): TimeZone
    {
        return TimeZone::fromString('Europe/Amsterdam');
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}
