<?php

declare(strict_types=1);

namespace SchemaTransformer\Transforms\Event\VastsverigeEvents\Mappers;

use DateTimeImmutable;
use Municipio\Schema\Event;
use Municipio\Schema\Schema;
use Municipio\Schema\Schedule;
use SchemaTransformer\Util\DateUtils;

/**
 * Maps Västsverige Dates entries to schema.org Schedule.
 */
class MapEventSchedule extends AbstractVastsverigeEventMapper
{
    /**
     * Map source dates onto the event schedule.
     *
     * @param Event                $event Event being built.
     * @param array<string, mixed> $data  Source event.
     *
     * @return Event
     */
    public function map(Event $event, array $data): Event
    {
        $schedules = [];
        foreach ($data['Dates'] ?? [] as $date) {
            if (!is_array($date)) {
                continue;
            }

            $schedules = [...$schedules, ...$this->schedulesForDate($date)];
        }

        usort(
            $schedules,
            fn (Schedule $left, Schedule $right) => ($left->getProperty('startDate') ?? '') <=> ($right->getProperty('startDate') ?? '')
        );

        return $event->eventSchedule($schedules);
    }

    /**
     * Expand one source date into one schedule per time slot.
     *
     * @param array<string, mixed> $date Source date entry.
     *
     * @return Schedule[]
     */
    private function schedulesForDate(array $date): array
    {
        $sourceDate = $date['Date'] ?? null;
        if (!is_string($sourceDate) || $sourceDate === '') {
            return [];
        }

        $times = $this->times($date);
        if ($times === []) {
            return [$this->scheduleWithoutClock($sourceDate, $date['EndDate'] ?? null)];
        }

        return array_map(
            fn (array $time) => $this->scheduleWithClock($sourceDate, $time),
            $times
        );
    }

    /**
     * Read time slots from a source date.
     *
     * @param array<string, mixed> $date Source date entry.
     *
     * @return array<int, array<string, mixed>>
     */
    private function times(array $date): array
    {
        $times = $date['Times'] ?? [];
        if (!is_array($times)) {
            return [];
        }

        return array_values(array_filter($times, 'is_array'));
    }

    /**
     * Build a schedule from a date that has no clock times.
     *
     * @param string $sourceDate Source date timestamp.
     * @param mixed  $endDate    Optional source end date.
     *
     * @return Schedule
     */
    private function scheduleWithoutClock(string $sourceDate, mixed $endDate): Schedule
    {
        $end = is_string($endDate) && $endDate !== '' ? DateUtils::toLocalDate($endDate) : null;

        return Schema::schedule()
            ->startDate(DateUtils::toLocalDate($sourceDate))
            ->endDate($end);
    }

    /**
     * Build a schedule from a date and one clock interval.
     *
     * Clock times belong to the calendar day of Date. An end clock that is not
     * later than the start clock rolls over to the next day.
     *
     * @param string               $sourceDate Source date timestamp.
     * @param array<string, mixed> $time       Source time slot.
     *
     * @return Schedule
     */
    private function scheduleWithClock(string $sourceDate, array $time): Schedule
    {
        $startClock = $this->clock($time['Start'] ?? null);
        $endClock   = $this->clock($time['End'] ?? null);
        if ($startClock === null && $endClock === null) {
            return $this->scheduleWithoutClock($sourceDate, null);
        }

        $start = $this->onDay($sourceDate, $startClock);
        $end   = $this->onDay($sourceDate, $endClock);
        if ($start !== null && $end !== null && $end <= $start) {
            $end = $this->addOneDay($end);
        }

        return Schema::schedule()->startDate($start)->endDate($end);
    }

    /**
     * Combine a source calendar day with an optional clock time.
     *
     * @param string      $sourceDate Source date timestamp.
     * @param string|null $clock      Clock time as HH:MM:SS.
     *
     * @return string|null Local date-time without a timezone offset.
     */
    private function onDay(string $sourceDate, ?string $clock): ?string
    {
        $day = substr($sourceDate, 0, 10);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $day) !== 1) {
            return null;
        }

        if ($clock === null) {
            return DateUtils::toLocalDate($sourceDate);
        }

        return $day . 'T' . $clock;
    }

    /**
     * Normalize a clock time to HH:MM:SS.
     *
     * @param mixed $time Source clock time.
     *
     * @return string|null
     */
    private function clock(mixed $time): ?string
    {
        $time = trim((string) $time);
        if (preg_match('/^\d{2}:\d{2}$/', $time) === 1) {
            return $time . ':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time) === 1) {
            return $time;
        }

        return null;
    }

    /**
     * Add one day to a local date-time.
     *
     * @param string $localDateTime Local date-time without a timezone offset.
     *
     * @return string
     */
    private function addOneDay(string $localDateTime): string
    {
        return (new DateTimeImmutable($localDateTime))
            ->modify('+1 day')
            ->format('Y-m-d\TH:i:s');
    }
}
