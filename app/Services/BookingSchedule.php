<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Collection;

class BookingSchedule
{
    public function timezone(): string
    {
        return (string) config('booking.timezone', 'Asia/Dhaka');
    }

    public function today(): string
    {
        return CarbonImmutable::now($this->timezone())->toDateString();
    }

    /**
     * @return Collection<int, array{value: string, label: string}>
     */
    public function slots(): Collection
    {
        $start = $this->parseClockTime((string) config('booking.day_start', '09:00'));
        $end = $this->parseClockTime((string) config('booking.day_end', '18:00'));
        $interval = max(1, (int) config('booking.slot_interval_minutes', 15));
        $slots = collect();

        for ($time = $start; $time <= $end; $time = $time->modify("+{$interval} minutes")) {
            $label = $time->format('h:i A');

            $slots->push([
                'value' => $label,
                'label' => $label,
            ]);
        }

        return $slots;
    }

    /**
     * @return array<int, string>
     */
    public function slotValues(): array
    {
        return $this->slots()->pluck('value')->all();
    }

    public function normalizeTime(mixed $time): ?string
    {
        if (! is_string($time) || trim($time) === '') {
            return null;
        }

        $time = strtoupper(trim($time));

        foreach (['!g:i A', '!H:i'] as $format) {
            $parsed = DateTimeImmutable::createFromFormat(
                $format,
                $time,
                new DateTimeZone($this->timezone())
            );
            $errors = DateTimeImmutable::getLastErrors();

            if ($parsed !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $parsed->format('h:i A');
            }
        }

        return null;
    }

    public function slotKey(mixed $date, mixed $time): ?string
    {
        $normalizedDate = $this->normalizeDate($date);
        $normalizedTime = $this->normalizeTime($time);

        if (
            $normalizedDate === null
            || $normalizedTime === null
            || ! in_array($normalizedTime, $this->slotValues(), true)
        ) {
            return null;
        }

        $clockTime = DateTimeImmutable::createFromFormat(
            '!h:i A',
            $normalizedTime,
            new DateTimeZone($this->timezone())
        );

        return $clockTime === false
            ? null
            : $normalizedDate.' '.$clockTime->format('H:i');
    }

    public function startsAt(mixed $date, mixed $time): ?CarbonImmutable
    {
        $slotKey = $this->slotKey($date, $time);

        if ($slotKey === null) {
            return null;
        }

        $startsAt = CarbonImmutable::createFromFormat(
            '!Y-m-d H:i',
            $slotKey,
            $this->timezone()
        );

        return $startsAt === false ? null : $startsAt;
    }

    public function isFutureSlot(mixed $date, mixed $time): bool
    {
        return $this->startsAt($date, $time)?->isFuture() ?? false;
    }

    public function isAvailable(mixed $date, mixed $time, ?int $exceptBookingId = null): bool
    {
        $slotKey = $this->slotKey($date, $time);

        if ($slotKey === null || ! $this->isFutureSlot($date, $time)) {
            return false;
        }

        return Booking::query()
            ->where('slot_key', $slotKey)
            ->when(
                $exceptBookingId !== null,
                fn ($query) => $query->whereKeyNot($exceptBookingId)
            )
            ->doesntExist();
    }

    /**
     * @return Collection<int, array{value: string, label: string, available: bool}>
     */
    public function availability(string $date): Collection
    {
        $slotKeys = $this->slots()
            ->mapWithKeys(fn (array $slot): array => [
                $slot['value'] => $this->slotKey($date, $slot['value']),
            ]);

        $reserved = Booking::query()
            ->whereIn('slot_key', $slotKeys->filter()->values())
            ->pluck('slot_key')
            ->flip();

        return $this->slots()->map(function (array $slot) use ($date, $slotKeys, $reserved): array {
            $slotKey = $slotKeys->get($slot['value']);

            return $slot + [
                'available' => $slotKey !== null
                    && ! $reserved->has($slotKey)
                    && $this->isFutureSlot($date, $slot['value']),
            ];
        });
    }

    private function normalizeDate(mixed $date): ?string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        if (! is_string($date)) {
            return null;
        }

        $date = trim($date);
        $parsed = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $date,
            new DateTimeZone($this->timezone())
        );
        $errors = DateTimeImmutable::getLastErrors();

        if (
            $parsed === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $parsed->format('Y-m-d') !== $date
        ) {
            return null;
        }

        return $date;
    }

    private function parseClockTime(string $time): DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat(
            '!H:i',
            $time,
            new DateTimeZone($this->timezone())
        );

        if ($parsed === false) {
            throw new \InvalidArgumentException("Invalid booking clock time [{$time}].");
        }

        return $parsed;
    }
}
