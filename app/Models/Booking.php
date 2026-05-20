<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'service',
        'preferred_date',
        'preferred_time',
        'message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->status(self::STATUS_PENDING);
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->status(self::STATUS_ACCEPTED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->status(self::STATUS_REJECTED);
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function statusClass(): string
    {
        return match ($this->status) {
            self::STATUS_ACCEPTED => 'status-active',
            self::STATUS_REJECTED => 'status-rejected',
            default => 'status-pending',
        };
    }

    public function scheduleLabel(): string
    {
        $date = $this->preferred_date?->format('M d, Y') ?? 'Flexible date';
        $time = $this->preferred_time ?: 'Flexible time';

        return "{$date} at {$time}";
    }
}
