<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('slot_key', 16)->nullable()->after('preferred_time');
        });

        $claimedSlots = [];

        DB::table('bookings')
            ->where('status', '!=', 'rejected')
            ->whereNotNull('preferred_date')
            ->whereNotNull('preferred_time')
            ->orderBy('id')
            ->get(['id', 'preferred_date', 'preferred_time'])
            ->each(function (object $booking) use (&$claimedSlots): void {
                $date = substr((string) $booking->preferred_date, 0, 10);
                $time = DateTimeImmutable::createFromFormat(
                    '!g:i A',
                    strtoupper(trim((string) $booking->preferred_time))
                );

                if ($time === false || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    return;
                }

                $slotKey = $date.' '.$time->format('H:i');

                // Preserve historical records if the old system already accepted
                // duplicates; the earliest record owns the slot going forward.
                if (isset($claimedSlots[$slotKey])) {
                    return;
                }

                DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update(['slot_key' => $slotKey]);

                $claimedSlots[$slotKey] = true;
            });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unique('slot_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['slot_key']);
            $table->dropColumn('slot_key');
        });
    }
};
