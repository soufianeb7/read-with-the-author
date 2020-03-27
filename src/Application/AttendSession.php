<?php
declare(strict_types=1);

namespace LeanpubBookClub\Application;

use LeanpubBookClub\Domain\Model\Member\MemberId;
use LeanpubBookClub\Domain\Model\Session\SessionId;

final class AttendSession
{
    private string $sessionId;

    private string $memberId;

    public function __construct(string $sessionId, string $memberId)
    {
        $this->sessionId = $sessionId;
        $this->memberId = $memberId;
    }

    public function sessionId(): SessionId
    {
        return SessionId::fromString($this->sessionId);
    }

    public function memberId(): MemberId
    {
        return MemberId::fromString($this->memberId);
    }
}