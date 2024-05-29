<?php

namespace App\Services;

use DateTime;
use Exception;

class DueDateCalculator
{
    private const WORK_HOURS = 8;
    private const WORK_START_HOUR_INT = 9;
    private const WORK_START_HOUR = '09:00';
    private const WORK_END_HOUR = '17:00';

    /**
     * @param string $start_time
     * @param int $turnaround_time
     * @return DateTime
     * @throws Exception
     */
    public function calculateDueDate(string $start_time, int $turnaround_time): DateTime
    {
        $this->validateSubmittedData($start_time, $turnaround_time);

        $submit_date = $this->getDateTimeObject($start_time);

        $this->validateWorkingHours($submit_date);

        $minutes =      (int) $submit_date->format('i');
        $day_of_week =  (int) $submit_date->format('N');

        $days_to_add =  (int) ($turnaround_time / self::WORK_HOURS);
        $remaining_hours = $turnaround_time % self::WORK_HOURS;

        // Handle problems submitted on weekends
        if ($day_of_week >= 6) {
            $weekend_days = 7 - $day_of_week + 1;

            $submit_date->modify("+{$weekend_days} day");
            $submit_date->setTime(self::WORK_START_HOUR_INT, $minutes);
        }

        $submit_date->modify("+{$days_to_add} weekday");

        while ($remaining_hours > 0) {
            $submit_date->modify('+1 hour');

            if ($submit_date->format('H:i') > self::WORK_START_HOUR && $submit_date->format('H:i') <= self::WORK_END_HOUR) {
                --$remaining_hours;
            }
        }

        return $submit_date;
    }

    /**
     * Before processing the input string, let's validate it. In case of a misformatted string, throw an error.
     * Also validate the turnaround time, which should should not be negative.
     *
     * @throws Exception
     */
    private function validateSubmittedData(string $start_time, int $turnaround_time): void
    {
        [$date, $time] = explode(' ', $start_time);

        /**
         * Test if the date part of the string is valid, ie. the formatted date should equal the original date.
         */

        try {
            $datetime = new DateTime($date);
            $formatted_date = $datetime->format('Y-m-d');

            if ($formatted_date !== $date) {
                throw new Exception('Invalid submit date provided!');
            }
        } catch (\Exception) {
            throw new Exception('Invalid submit date provided!');
        }

        /**
         * Validate the time part of the string for both international long hour:minute format, and short time format
         * with AM/PM. The string should match one regex, and it's digits should not be out of bounds.
         */

        if (str_contains($time, 'AM') || str_contains($time, 'PM')) {
            // Validate 12-hour format time
            if (!preg_match('/^\d{1,2}:\d{2}(AM|PM)$/', $time)) {
                throw new Exception('Invalid submit date provided!');
            }

            [$hour, $minute] = sscanf($time, '%d:%d%s');

            if ($hour < 1 || $hour > 12 || $minute < 0 || $minute > 59) {
                throw new Exception('Invalid submit date provided!');
            }
        } else {
            // Validate 24-hour format time
            if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
                throw new Exception('Invalid submit date provided!');
            }

            [$hour, $minute] = sscanf($time, '%d:%d');

            if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
                throw new Exception('Invalid submit date provided!');
            }
        }

        /**
         * Finally, validate the turnaround time, which should not be negative.
         */

        if ($turnaround_time < 0) {
            throw new Exception('Invalid turnaround time provided!');
        }
    }

    /**
     * We got a valid datetime object! Now, before actually processing it, let's test if the we are withing working hours.
     *
     * @param DateTime $start_time
     * @return void
     * @throws Exception
     */
    private function validateWorkingHours(DateTime $start_time): void
    {
        $start_hour = $start_time->format('H:i');

        if ($start_hour < self::WORK_START_HOUR || $start_hour > self::WORK_END_HOUR) {
            throw new Exception('Invalid submit date provided!');
        }
    }

    /**
     * Function to get the DateTime object from the user submitted datetime string.
     *
     * @throws Exception
     */
    private function getDateTimeObject(string $start_time): DateTime
    {
        /**
         * First, try to parse the standard ISO datetime format.
         */
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $start_time)) {
            return DateTime::createFromFormat('Y-m-d H:i', $start_time);
        }

        /**
         * If we didn't get a match, let's try the short time format, with AM/PM suffix.
         */
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{1,2}:\d{2}(AM|PM)$/', $start_time)) {
            return DateTime::createFromFormat('Y-m-d g:iA', $start_time);
        }

        throw new Exception('Invalid submit date provided!');
    }
}