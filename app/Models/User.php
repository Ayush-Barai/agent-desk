<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read string $email
 * @property-read UserRole $role
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasUuids;
    use Notifiable;

    /**
     * @var list<string>
     */
    #[Override]
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'email' => 'string',
            'role' => UserRole::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'remember_token' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isAgent(): bool
    {
        return $this->role === UserRole::Agent;
    }

    public function isRequester(): bool
    {
        return $this->role === UserRole::Requester;
    }

    public function isStaff(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isAgent();
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function requesterTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to_user_id');
    }

    /**
     * @return HasMany<TicketMessage, $this>
     */
    public function ticketMessages(): HasMany
    {
        return $this->hasMany(TicketMessage::class, 'user_id');
    }

    /**
     * @return HasMany<AiRun, $this>
     */
    public function aiRuns(): HasMany
    {
        return $this->hasMany(AiRun::class, 'initiated_by_user_id');
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_user_id');
    }
}
