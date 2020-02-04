<?php

namespace Tests;


use CRHolidays\CarbonCR;
use PHPUnit\Framework\TestCase;

class HolidayTests extends TestCase
{

    public function testInstanceCreation()
    {

    }

    public function testGetHolidays()
    {
        $holiday = CarbonCR::now()->getHolidaysByYear(2020);

        $this->assertIsArray($holiday);
        print_r($holiday);

    }

    public function testNextHoliday()
    {
        $holiday = CarbonCR::now()->getHolidaysInDays(15);

        $this->assertIsArray($holiday);
        print_r($holiday);

    }

    public function testNextYearHoliday()
    {
        $holiday = CarbonCR::now()->getHolidaysInYears(2);

        $this->assertIsArray($holiday);
        print_r($holiday);

    }





}
