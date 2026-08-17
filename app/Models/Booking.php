<?php

namespace App\Models;

use App\Services\BookingSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'service',
        'preferred_date',
        'preferred_time',
        'slot_key',
        'message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Booking $booking): void {
            if (! self::blocksSlot($booking->status)) {
                $booking->slot_key = null;

                return;
            }

            $booking->slot_key = app(BookingSchedule::class)->slotKey(
                $booking->preferred_date,
                $booking->preferred_time
            );
        });
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

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->status(self::STATUS_COMPLETED);
    }

    public static function blocksSlot(?string $status): bool
    {
        if ($status === null) {
            return true;
        }

        return in_array($status, [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
        ], true);
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
            self::STATUS_COMPLETED => 'Completed',
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
            self::STATUS_COMPLETED => 'status-completed',
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
