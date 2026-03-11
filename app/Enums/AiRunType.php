<?php

declare(strict_types=1);

namespace App\Enums;

enum AiRunType: string
{
    case Triage = 'triage';
    case ReplyDraft = 'reply_draft';
    case ThreadSummary = 'thread_summary';

    /**
     * @return list<self>
     */
    public static function options(): array
    {
        return self::cases();
    }

    public function label(): string
    {
        return match ($this) {
            self::Triage => 'Triage',
            self::ReplyDraft => 'Reply Draft',
            self::ThreadSummary => 'Thread Summary',
        };
    }
}
