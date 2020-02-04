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
     * @param HolidaysEnum|null $enumDate
     * @param int|null $year The year to get the holidays in
     * @return array
     */
    public function getHolidaysByYear(HolidaysEnum $enumDate = null, $year = 2020)
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
                    $days_until = $this->diffInDays($date) + 1;
                } else {
                    $days_until = $this->diffInDays($date);
                }



                $details = (object)[
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
                    $days_until = $this->diffInDays($date) + 1;
                } else {
                    $days_until = $this->diffInDays($date);
                }

                $details = (object)[
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
     * @param string|array|null $holidays The name(s) of the holidays to get
     */
    public function getHolidaysInDays($days, $holidays = null)
    {
        if ($holidays === null || $holidays === 'all') {
            $holidays = $this->holidayArray;
        }

        $searchStartDate = $this->copy();
        $searchEndDate = $this->copy()->addDays($days)->year;

        $holidaysInRange = [];
        for ($i = $searchStartDate->year; $i <= $searchEndDate; $i++) {
            $holidayYear = $this->getHolidaysByYear($holidays, intval($i));

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
     * @param string|array|null $holidays The name(s) of the holidays to get
     */
    public function getHolidaysInYears($years, $holidays = null)
    {
        $days = $this->diffInDays($this->copy()->addYears($years));
        return $this->getHolidaysInDays($days, $holidays);
    }

    /**
     * Check if a date is a holiday. returns boolean
     */
    public function isHoliday()
    {
        $holidays = $this->getHolidaysByYear('all');
        $isHoliday = false;

        foreach ($holidays as $holiday) {

            if ($this->isBirthday($holiday->date)) {
                $isHoliday = true;
                break;
            }
        }

        return $isHoliday;
    }

    /**
     * Check if a date is a bank holiday. returns boolean
     */
    public function isBankHoliday()
    {
        $holidays = $this->getHolidaysByYear('all');
        $isBankHoliday = false;

        foreach ($holidays as $holiday) {
            if ($holiday->bank_holiday) {
                if ($this->isBirthday($holiday->date) && $this->dayOfWeek !== Carbon::SUNDAY && $this->dayOfWeek !== Carbon::SATURDAY) {
                    $isBankHoliday = true;
                    break;
                } else {
                    if ($this->dayOfWeek === Carbon::MONDAY) {
                        if ($this->copy()->subDay()->isBirthday($holiday->date)) {
                            $isBankHoliday = true;
                            break;
                        }
                    } else if ($this->dayOfWeek === Carbon::FRIDAY) {
                        if ($this->copy()->addDay()->isBirthday($holiday->date)) {
                            $isBankHoliday = true;
                            break;
                        }
                    }
                }
            }
        }

        return $isBankHoliday;
    }

    /**
     * Get the holiday names, if any for the given date
     */
    public function getHolidayName()
    {
        $holidays = $this->getHolidaysByYear('all');
        $holidayName = null;

        foreach ($holidays as $holiday) {
            if ($this->isBirthday($holiday->date)) {
                $holidayName .= $holiday->name . ', ';
            } else {
                if ($this->dayOfWeek === Carbon::MONDAY) {
                    if ($this->copy()->subDay()->isBirthday($holiday->date) && $holiday->bank_holiday) {
                        $holidayName .= $holiday->name . ' (Observed), ';
                    }
                } else if ($this->dayOfWeek === Carbon::FRIDAY) {
                    if ($this->copy()->addDay()->isBirthday($holiday->date) && $holiday->bank_holiday) {
                        $holidayName .= $holiday->name . ' (Observed), ';
                    }
                }
            }
        }

        if ($holidayName) {
            $holidayName = rtrim($holidayName, ', ');
        }

        return $holidayName;
    }

    /**
     * Return date of April Fools Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getAprilFoolsDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("April Fool's Day", $year)[0];
    }

    /**
     * Return date of Armed Forces Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getArmedForcesDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Armed Forces Day", $year)[0];
    }

    /**
     * Return date of Ash Wednesday for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getAshWednesdayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Ash Wednesday", $year)[0];
    }

    /**
     * Return date of Black Friday for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getBlackFridayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Black Friday", $year)[0];
    }

    /**
     * Return date of Christmas Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getChristmasDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Christmas Day", $year)[0];
    }

    /**
     * Return date of Christmas Eve for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getChristmasEveHoliday($year = null)
    {
        return $this->getHolidaysByYear("Christmas Eve", $year)[0];
    }

    /**
     * Return date of Cinco de Mayo for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getCincoDeMayoHoliday($year = null)
    {
        return $this->getHolidaysByYear("Cinco de Mayo", $year)[0];
    }

    /**
     * Return date of Columbus Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getColumbusDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Columbus Day", $year)[0];
    }

    /**
     * Return date of Daylight Saving (End) for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getDaylightSavingEndHoliday($year = null)
    {
        return $this->getHolidaysByYear("Daylight Saving (End)", $year)[0];
    }

    /**
     * Return date of Daylight Saving (Start) for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getDaylightSavingStartHoliday($year = null)
    {
        return $this->getHolidaysByYear("Daylight Saving (Start)", $year)[0];
    }

    /**
     * Return date of Earth Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getEarthDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Earth Day", $year)[0];
    }

    /**
     * Return date of Easter for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getEasterHoliday($year = null)
    {
        return $this->getHolidaysByYear("Easter", $year)[0];
    }

    /**
     * Return date of Father Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getFathersDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Father's Day", $year)[0];
    }

    /**
     * Return date of Flag Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getFlagDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Flag Day", $year)[0];
    }

    /**
     * Return date of Good Friday for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getGoodFridayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Good Friday", $year)[0];
    }

    /**
     * Return date of Groundhog Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getGroundhogDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Groundhog Day", $year)[0];
    }

    /**
     * Return date of Halloween for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getHalloweenHoliday($year = null)
    {
        return $this->getHolidaysByYear("Halloween", $year)[0];
    }

    /**
     * Return date of Hanukkah for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getHanukkahHoliday($year = null)
    {
        return $this->getHolidaysByYear("Hanukkah", $year)[0];
    }

    /**
     * Return date of Independence Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getIndependenceDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Independence Day", $year)[0];
    }

    /**
     * Return date of Indigenous Peoples Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getIndigenousPeoplesDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Indigenous Peoples' Day", $year)[0];
    }

    /**
     * Return date of Juneteenth for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getJuneteenthHoliday($year = null)
    {
        return $this->getHolidaysByYear("Juneteenth", $year)[0];
    }

    /**
     * Return date of Kwanzaa for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getKwanzaaHoliday($year = null)
    {
        return $this->getHolidaysByYear("Kwanzaa", $year)[0];
    }

    /**
     * Return date of Labor Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getLaborDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Labor Day", $year)[0];
    }

    /**
     * Return date of Memorial Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getMemorialDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Memorial Day", $year)[0];
    }

    /**
     * Return date of MLK Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getMLKDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Martin Luther King Jr. Day", $year)[0];
    }

    /**
     * Return date of Mothers Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getMothersDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Mother's Day", $year)[0];
    }

    /**
     * Return date of New Years Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getNewYearsDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("New Year's Day", $year)[0];
    }

    /**
     * Return date of New Years Eve for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getNewYearsEveHoliday($year = null)
    {
        return $this->getHolidaysByYear("New Year's Eve", $year)[0];
    }

    /**
     * Return date of Orthodox Easter for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getOrthodoxEasterHoliday($year = null)
    {
        return $this->getHolidaysByYear("Orthodox Easter", $year)[0];
    }

    /**
     * Return date of Palm Sunday for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getPalmSundayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Palm Sunday", $year)[0];
    }

    /**
     * Return date of Passover for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getPassoverHoliday($year = null)
    {
        return $this->getHolidaysByYear("Passover", $year)[0];
    }

    /**
     * Return date of Patriot Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getPatriotDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Patriot Day", $year)[0];
    }

    /**
     * Return date of Pearl Harbor Remembrance Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getPearlHarborRemembranceDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Pearl Harbor Remembrance Day", $year)[0];
    }

    /**
     * Return date of Presidents Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getPresidentsDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Presidents' Day", $year)[0];
    }

    /**
     * Return date of Rosh Hashanah for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getRoshHashanahHoliday($year = null)
    {
        return $this->getHolidaysByYear("Rosh Hashanah", $year)[0];
    }

    /**
     * Return date of St Patricks Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getStPatricksDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("St. Patrick's Day", $year)[0];
    }

    /**
     * Return date of Tax Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getTaxDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Tax Day", $year)[0];
    }

    /**
     * Return date of Thanksgiving for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getThanksgivingHoliday($year = null)
    {
        return $this->getHolidaysByYear("Thanksgiving", $year)[0];
    }

    /**
     * Return date of Valentines Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getValentinesDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Valentine's Day", $year)[0];
    }

    /**
     * Return date of Veterans Day for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getVeteransDayHoliday($year = null)
    {
        return $this->getHolidaysByYear("Veterans Day", $year)[0];
    }

    /**
     * Return date of Yom Kippur for given year
     *
     * @param int|null $year The year to get the holiday in
     */
    public function getYomKippurHoliday($year = null)
    {
        return $this->getHolidaysByYear("Yom Kippur", $year)[0];
    }
}