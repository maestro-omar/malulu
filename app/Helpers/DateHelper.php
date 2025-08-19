<?php

namespace App\Helpers;

use DateTimeImmutable;

class DateHelper
{

    public static function tomorrowOrMondayIfWeekend(DateTimeImmutable $startDate): DateTimeImmutable
    {
        $next = $startDate->add(new \DateInterval('P1D'));
        if ($next->format('N') == 6 || $next->format('N') == 7)
            return self::findMonday($next);
        return $next;
    }

    /**
     * Find the next or previous Monday from a given date
     *
     * @param DateTimeImmutable $startDate
     * @param bool $next Whether to find the next Monday (true) or previous Monday (false)
     * @return DateTimeImmutable
     */
    public static function findMonday(DateTimeImmutable $startDate, bool $next = true): DateTimeImmutable
    {
        return self::findDateOfDay($startDate, 1, !$next); // 1 = Monday
    }

    /**
     * Find the next or previous Friday from a given date
     *
     * @param DateTimeImmutable $startDate
     * @param bool $next Whether to find the next Friday (true) or previous Friday (false)
     * @return DateTimeImmutable
     */
    public static function findFriday(DateTimeImmutable $startDate, bool $next = true): DateTimeImmutable
    {
        return self::findDateOfDay($startDate, 5, !$next); // 5 = Friday
    }

    /**
     * Find the next or previous occurrence of a specific day of the week
     *
     * @param DateTimeImmutable $startDate
     * @param int $dayNum Day of week (1=Monday, 2=Tuesday, ..., 7=Sunday)
     * @param bool $past Whether to look in the past (true) or future (false)
     * @return DateTimeImmutable
     */
    public static function findDateOfDay(DateTimeImmutable $startDate, int $dayNum, bool $past): DateTimeImmutable
    {
        $currentDay = (int)$startDate->format('N');
        $daysToAdd = $dayNum - $currentDay;

        if ($past) {
            // If we're looking for a past day and the target day is after current day,
            // we need to go back a full week
            if ($daysToAdd > 0) {
                $daysToAdd -= 7;
            }
        } else {
            // If we're looking for a future day and the target day is before current day,
            // we need to go forward a full week
            if ($daysToAdd < 0) {
                $daysToAdd += 7;
            }
        }

        return $startDate->modify($daysToAdd . ' days');
    }
}
