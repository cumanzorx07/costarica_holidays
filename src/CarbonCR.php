<?php


namespace CRHolidays;


use Carbon\Carbon;
use CRHolidays\Enums\HolidaysEnum;

class CarbonCR extends Carbon
{
    private $timezone = 'America/Costa_Rica';

    /**
     * Set the holiday
     * @param HolidaysEnum $holidayEnum
     * @param int $year The year in order to properly calculate the holiday
     * @return Carbon
     */
    private function setHolidayDate(HolidaysEnum $holidayEnum, $year)
    {
        return self::createFromFormat('Y/m/d H', $holidayEnum->getDateString($year), $this->timezone);
    }

    /**
     * Get holiday data
     *
     * @param int|null $year The year to get the holidays in
     * @return array
     */
    private function holidays($year = 2020)
    {
        $holidays = [];
        foreach (HolidaysEnum::values() as $holidaysObj)
        {
            $holidays[] = [
                'index' =>  $holidaysObj->getValue(),
                'name' =>  $holidaysObj->getDateHolidayName(),
                'date' => function () use ($holidaysObj, $year) {
                    return $this->setHolidayDate($holidaysObj, $year);
                }
            ];
        }

        return $holidays;
    }


    /**
     * Compare to dates to sort
     *
     * @param object $a A date object
     * @param object $b A date object
     * @return bool
     */
    private function compareDate($a, $b)
    {
        return $a->date > $b->date;
    }

    /**
     *  Returns holidays in the specified years
     *
     * @param HolidaysEnum|null $enumDate Search by specific holiday
     * @param int|null $year The year to get the holidays in
     * @return array
     */
    public function getHolidaysByYear($year = 2020, HolidaysEnum $enumDate = null)
    {
        $year = $year ? $year : $this->year;
        $holidays = $this->holidays($year);
        $holidayIndexes = array_column($holidays, 'index');
        $holiday_details = [];

        if (!is_null($enumDate)) {

            $index = false;
            foreach ($holidayIndexes as $key => $indexValue) {
                if ($enumDate->getValue() == $indexValue) {
                    $index = $key;
                }
            }

            if ($index !== false) {
                $holidaysObj = new HolidaysEnum($holidays[$index]['index']);
                $date = call_user_func($holidays[$index]['date']);

                if (!$this->isMidnight()) {
                    $days_until = $this->diffInDays($date, false) + 1;
                } else {
                    $days_until = $this->diffInDays($date, false);
                }



                $details = (object)[
                    'index' => $holidays[$index]['index'],
                    'name' => $holidays[$index]['name'],
                    'date' => $date,
                    'days_away' => $days_until
                ];

                array_push($holiday_details, $details);
            }

        } else {

            foreach ($holidays as $holidaysDate) {
                $holidaysObj = new HolidaysEnum($holidaysDate['index']);
                $date = call_user_func($holidaysDate['date']);

                if (!$this->isMidnight()) {
                    $days_until = $this->diffInDays($date, false) + 1;
                } else {
                    $days_until = $this->diffInDays($date, false);
                }

                $details = (object)[
                    'index' => $holidaysDate['index'],
                    'name' => $holidaysDate['name'],
                    'date' => $date,
                    'days_away' => $days_until
                ];

                array_push($holiday_details, $details);
            }
        }

        usort($holiday_details, [$this, "compareDate"]);

        return $holiday_details;
    }

    /**
     * Returns holidays in the next amount of days
     *
     * @param int $days The number of days to look ahead to find holidays in
     * @return array
     */
    public function getHolidaysInDays($days)
    {
        $searchStartDate = $this->copy();
        $searchEndDate = $this->copy()->addDays($days)->year;

        $holidaysInRange = [];
        for ($i = $searchStartDate->year; $i <= $searchEndDate; $i++) {
            $holidayYear = $this->getHolidaysByYear(intval($i));

            foreach ($holidayYear as $holiday) {
                if ($holiday->date->lessThanOrEqualTo($searchStartDate->copy()->addDays($days)) && $holiday->date->greaterThanOrEqualTo($searchStartDate)) {
                    array_push($holidaysInRange, $holiday);
                }
            }
        }

        return $holidaysInRange;
    }

    /**
     * Returns holidays in the next amount of years
     *
     * @param int $years The number of years to look ahead to find holidays in
     * @return array
     */
    public function getHolidaysInYears($years)
    {
        $days = $this->diffInDays($this->copy()->addYears($years));
        return $this->getHolidaysInDays($days);
    }

    /**
     * Check if a date is a holiday. returns boolean
     */
    public function isHoliday()
    {
        $holidays = $this->getHolidaysByYear($this->year);
        $isHoliday = false;

        foreach ($holidays as $holiday) {
            if ($this->isBirthday($holiday->date)) {
                $isHoliday = true;
                break;
            }
        }

        return $isHoliday;
    }



}