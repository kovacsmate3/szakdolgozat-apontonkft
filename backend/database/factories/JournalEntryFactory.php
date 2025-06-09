<?php

namespace Database\Factories;

use App\Models\JournalEntry;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->subMonths(2);
        $end = now();

        do {
            $randomDate = fake()->dateTimeBetween($start, $end);
        } while ($this->isWeekend($randomDate));


        return [
            'work_date'          => $randomDate->format('Y-m-d'),
            'hours'              => "8:00:00",
            'work_type'          => "normál munkavégzés",
            'note'               => fake()->optional()->sentence(),
            'leaverequest_id'    => null,
            'overtimerequest_id' => null,
            'user_id'            => fake()->randomElement([3,4,5,6,7]),
            'task_id'            => null
        ];
    }

    /**
     * Szabadság kezelése: Ha a LeaveRequest státusza "accepted",
     * akkor a start_date és end_date közötti minden napra létrehozunk egy JournalEntry-t,
     * ahol a work_type "szabadság" és az hours "0:00:00".
     *
     * @param LeaveRequest $leaveRequest
     * @return \Illuminate\Support\Collection A létrejött naplóbejegyzések gyűjteménye.
     */
    public static function createLeaveEntriesForRequest(LeaveRequest $leaveRequest)
    {
        $entries = collect();

        if ($leaveRequest->status !== 'jóváhagyott') {
            return $entries;
        }

        $leaveRequest->load('user');

        $userFirstName = $leaveRequest->user->firstname ?? '';
        $reason        = $leaveRequest->reason ?? '';

        $startDate = Carbon::parse($leaveRequest->start_date);
        $endDate   = Carbon::parse($leaveRequest->end_date);

        // Minden napra (beleértve a start_date és end_date napokat is) létrehozunk egy JournalEntry-t
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $entries->push(
                JournalEntry::factory()->create([
                    'work_date'       => $date->format('Y-m-d'),
                    'hours'           => "0:00:00",
                    'work_type'       => "szabadság",
                    'leaverequest_id' => $leaveRequest->id,
                    'user_id'         => $leaveRequest->user_id,
                    'task_id'         => null,
                    'note'            => "SZABADSÁG: {$userFirstName} - {$reason}"
                ])
            );
        }
        return $entries;
    }

    /**
     * Túlóra kezelése: Ha az OvertimeRequest státusza "accepted",
     * akkor az overtime request-ben szereplő date-re létrehozunk egy JournalEntry-t,
     * ahol a work_type "túlóra" és az hours az overtime request-ben megadott érték.
     *
     * @param OvertimeRequest $overtimeRequest
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withOvertimeRequest(OvertimeRequest $overtimeRequest)
    {
        $overtimeRequest->load('user');
        $userFirstName = $overtimeRequest->user->firstname ?? '';
        $reason        = $overtimeRequest->reason ?? '';

        return $this->state(function (array $attributes) use ($overtimeRequest, $userFirstName, $reason) {
            if ($overtimeRequest->status === 'jóváhagyott') {
                return [
                    'work_date'          => $overtimeRequest->date,
                    'hours'              => $overtimeRequest->hours,
                    'work_type'          => "túlóra",
                    'overtimerequest_id' => $overtimeRequest->id,
                    'user_id'            => $overtimeRequest->user_id,
                    'task_id'            => null,
                    'note'               => "TÚLÓRA: {$userFirstName} - {$reason}"
                ];
            }
            return $attributes;
        });
    }

    /**
     * Segédfüggvény annak ellenőrzésére, hogy a megadott dátum hétvége-e.
     *
     * @param \DateTime $dateTime
     * @return bool
    */
    private function isWeekend(\DateTime $dateTime): bool {
        return in_array($dateTime->format('N'), [6, 7]);
    }
}
