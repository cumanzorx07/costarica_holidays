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
        $holiday = CarbonCR::now()->getHolidaysByYear(null, 2020);

        $this->assertIsArray($holiday);
        print_r($holiday);

    }





}
