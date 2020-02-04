<?php

namespace Tests;

use CRHolidays\Enums\HolidaysEnum;
use PHPUnit\Framework\TestCase;

class HolidayEnumTests extends TestCase
{

    public function testInstanceCreation()
    {

    }

    public function testEnum1()
    {
        $dateEnum = HolidaysEnum::values();

        $this->assertIsArray($dateEnum);

    }

    public function testEnum2()
    {
        foreach (HolidaysEnum::values() as $hday)
        {
            $dateText = $hday->getDateString();
            $this->assertIsString($dateText);
            echo $dateText.PHP_EOL;
        }
    }

    public function testEnum3()
    {
        foreach (HolidaysEnum::values() as $hday)
        {

            $dateName = $hday->getDateHolidayName();
            $this->assertIsString($dateName);
            echo $dateName.PHP_EOL;
        }
    }




}
